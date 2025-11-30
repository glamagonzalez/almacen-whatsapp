<?php
/**
 * CREAR_PEDIDO_MP.PHP - Crear pedido y preferencia de Mercado Pago
 */
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/mercadopago.php';

try {
    // Obtener datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $notas = $_POST['notas'] ?? '';
    $productosJson = $_POST['productos'] ?? '[]';
    
    // Decodificar productos
    $productos = json_decode($productosJson, true);
    
    if (empty($productos)) {
        throw new Exception('No hay productos en el pedido');
    }
    
    // Calcular totales
    $subtotal = 0;
    foreach ($productos as $prod) {
        $subtotal += $prod['precio'] * $prod['cantidad'];
    }
    
    $envio = 0; // Puedes calcular envío aquí
    $total = $subtotal + $envio;
    
    // Crear pedido en la base de datos
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (
            cliente_nombre, cliente_telefono, cliente_email, cliente_direccion,
            productos_json, subtotal, envio, total, notas, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
    ");
    
    $stmt->execute([
        $nombre, $telefono, $email, $direccion,
        $productosJson, $subtotal, $envio, $total, $notas
    ]);
    
    $pedidoId = $pdo->lastInsertId();
    
    // ==========================================
    // CREAR PREFERENCIA DE MERCADO PAGO (SDK v3.x)
    // ==========================================
    require_once '../vendor/autoload.php';
    require_once '../config/config.php';
    
    MercadoPago\MercadoPagoConfig::setAccessToken(MERCADOPAGO_ACCESS_TOKEN);
    $client = new MercadoPago\Client\Preference\PreferenceClient();
    
    // Preparar items para Mercado Pago
    $items = [];
    foreach ($productos as $prod) {
        $items[] = [
            'title' => $prod['nombre'],
            'quantity' => (int)$prod['cantidad'],
            'unit_price' => (float)$prod['precio']
        ];
    }
    
    // Datos de la preferencia SIN auto_return
    $preferenceData = [
        'items' => $items,
        'payer' => [
            'name' => $nombre,
            'phone' => [
                'number' => $telefono
            ],
            'email' => $email ?: 'cliente@ejemplo.com'
        ],
        'notification_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/almacen-whatsapp-1/webhook_mp.php?pedido_id=' . $pedidoId,
        'external_reference' => 'ORDEN-' . $pedidoId,
        'statement_descriptor' => 'ALMACEN DIGITAL'
    ];
    
    // Crear preferencia
    $preference = $client->create($preferenceData);
    $mpResponse = [
        'id' => $preference->id,
        'init_point' => $preference->init_point
    ];
    
    if (isset($mpResponse['id'])) {
        // Actualizar pedido con datos de MP
        $stmtUpdate = $pdo->prepare("
            UPDATE pedidos 
            SET mp_preference_id = ?, mp_link_pago = ?
            WHERE id = ?
        ");
        $stmtUpdate->execute([
            $mpResponse['id'],
            $mpResponse['init_point'],
            $pedidoId
        ]);
        
        echo json_encode([
            'success' => true,
            'pedido_id' => $pedidoId,
            'preference_id' => $mpResponse['id'],
            'init_point' => $mpResponse['init_point'],
            'message' => 'Pedido creado correctamente'
        ]);
        
    } else {
        // Error al crear preferencia
        throw new Exception('Error al crear preferencia de Mercado Pago: ' . ($mpResponse['message'] ?? 'Error desconocido'));
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
