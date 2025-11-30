<?php
/**
 * PROCESAR PAGO - Crear preferencia de Mercado Pago
 * Endpoint para recibir datos del checkout y crear preferencia de pago
 */

require_once '../config/config.php';
require_once '../vendor/autoload.php';

header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Obtener datos del POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Validar datos requeridos
if (empty($data['cliente']) || empty($data['productos']) || empty($data['totales'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Configurar Mercado Pago (SDK v3.x)
    MercadoPago\MercadoPagoConfig::setAccessToken(MERCADOPAGO_ACCESS_TOKEN);
    
    // Crear cliente de preferencias
    $client = new MercadoPago\Client\Preference\PreferenceClient();

    // Configurar items
    $items = [];
    foreach ($data['productos'] as $producto) {
        $item = [
            "id" => (string)$producto['id'],
            "title" => $producto['nombre'],
            "quantity" => (int)$producto['cantidad'],
            "unit_price" => (float)$producto['precio']
        ];
        
        if (!empty($producto['imagen'])) {
            $item["picture_url"] = $producto['imagen'];
        }
        
        $items[] = $item;
    }

    // Si hay envío con costo, agregar como ítem
    if (isset($data['envio']) && $data['envio']['costo'] > 0) {
        $items[] = [
            "title" => 'Envío - ' . ucfirst($data['envio']['tipo']),
            "quantity" => 1,
            "unit_price" => (float)$data['envio']['costo']
        ];
    }

    // Si hay descuento, agregar como ítem negativo
    if (isset($data['totales']['descuento']) && $data['totales']['descuento'] > 0) {
        $items[] = [
            "title" => 'Descuento - ' . ($data['cupon']['codigo'] ?? 'Promoción'),
            "quantity" => 1,
            "unit_price" => -(float)$data['totales']['descuento']
        ];
    }

    // Configurar datos del comprador
    $payer = [
        "name" => $data['cliente']['nombre'],
        "email" => $data['cliente']['email']
    ];
    
    if (!empty($data['cliente']['telefono'])) {
        $payer["phone"] = [
            "number" => $data['cliente']['telefono']
        ];
    }
    
    if (!empty($data['cliente']['direccion'])) {
        $payer["address"] = [
            "street_name" => $data['cliente']['direccion'],
            "zip_code" => $data['cliente']['codigo_postal'] ?? '',
            "city" => $data['cliente']['ciudad'] ?? ''
        ];
    }

    // Referencia externa
    $external_reference = 'ORDEN-' . time();
    
    // Crear objeto de preferencia SIN auto_return para evitar error
    $preferenceData = [
        "items" => $items,
        "payer" => $payer,
        "external_reference" => $external_reference,
        "statement_descriptor" => "ALMACEN DIGITAL",
        "notification_url" => "http://" . $_SERVER['HTTP_HOST'] . "/almacen-whatsapp-1/webhook_mp.php",
        "metadata" => [
            "cliente_nombre" => $data['cliente']['nombre'],
            "cliente_telefono" => $data['cliente']['telefono']
        ]
    ];

    // Crear preferencia usando el cliente
    $preference = $client->create($preferenceData);

    // Guardar orden en base de datos (antes del pago)
    try {
        $conn = getDBConnection();
        
        // Preparar JSON con productos
        $productos_json = json_encode($data['productos'], JSON_UNESCAPED_UNICODE);
        
        // Calcular totales
        $costo_envio = $data['envio']['costo'] ?? 0;
        $metodo_envio = $data['envio']['tipo'] ?? null;
        $total_final = $data['totales']['total'];
        $cupon_codigo = $data['cupon']['codigo'] ?? null;
        $cupon_descuento = $data['totales']['descuento'] ?? 0;
        
        // Insertar pedido con TODAS las columnas de la tabla real
        $stmt = $conn->prepare("
            INSERT INTO pedidos 
            (cliente_nombre, cliente_telefono, cliente_email, cliente_direccion, 
             cliente_cp, cliente_ciudad, cliente_provincia, 
             metodo_envio, costo_envio, cupon_codigo, cupon_descuento,
             productos_json, subtotal, envio, total, 
             mp_preference_id, mp_link_pago, mp_external_reference, estado)
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
        ");
        
        $stmt->execute([
            $data['cliente']['nombre'],
            $data['cliente']['telefono'],
            $data['cliente']['email'],
            $data['cliente']['direccion'],
            $data['cliente']['codigo_postal'] ?? null,
            $data['cliente']['ciudad'] ?? null,
            $data['cliente']['provincia'] ?? null,
            $metodo_envio,
            $costo_envio,
            $cupon_codigo,
            $cupon_descuento,
            $productos_json,
            $data['totales']['subtotal'],
            $costo_envio,
            $total_final,
            $preference->id,
            $preference->init_point,
            $external_reference
        ]);
        
        $pedido_id = $conn->lastInsertId();
        
        // Registrar en log de actividades
        $log_stmt = $conn->prepare("
            INSERT INTO log_actividades (pedido_id, accion, descripcion) 
            VALUES (?, 'pedido_creado', ?)
        ");
        $log_stmt->execute([
            $pedido_id,
            'Pedido creado para ' . $data['cliente']['nombre'] . ' - Total: $' . $total_final
        ]);
        
        // 🔔 NOTIFICAR A N8N - NUEVO PEDIDO
        try {
            $n8n_data = [
                'pedido_id' => $pedido_id,
                'cliente_nombre' => $data['cliente']['nombre'],
                'cliente_telefono' => $data['cliente']['telefono'],
                'cliente_email' => $data['cliente']['email'],
                'direccion' => $direccion_completa,
                'productos' => $data['productos'],
                'subtotal' => $data['totales']['subtotal'],
                'envio' => $costo_envio,
                'total' => $total_final,
                'fecha' => date('Y-m-d H:i:s')
            ];
            
            $ch = curl_init('http://localhost:5678/webhook/nuevo-pedido');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($n8n_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout de 5 segundos
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            error_log("Error al notificar n8n: " . $e->getMessage());
        }
        
    } catch (Exception $e) {
        error_log("Error al guardar orden: " . $e->getMessage());
        // No bloqueamos el pago si falla el guardado
    }

    // Retornar URL de pago
    echo json_encode([
        'success' => true,
        'preference_id' => $preference->id,
        'init_point' => $preference->init_point,
        'external_reference' => $external_reference
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error al crear preferencia MP: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar el pago: ' . $e->getMessage()
    ]);
}
