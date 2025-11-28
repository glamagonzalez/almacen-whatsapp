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
    // CREAR PREFERENCIA DE MERCADO PAGO
    // ==========================================
    
    // Preparar items para Mercado Pago
    $items = [];
    foreach ($productos as $prod) {
        $items[] = [
            'title' => $prod['nombre'],
            'quantity' => $prod['cantidad'],
            'unit_price' => floatval($prod['precio']),
            'currency_id' => 'ARS' // Pesos argentinos
        ];
    }
    
    // Datos de la preferencia
    $preferenceData = [
        'items' => $items,
        'payer' => [
            'name' => $nombre,
            'phone' => [
                'number' => $telefono
            ],
            'email' => $email ?: 'cliente@ejemplo.com'
        ],
        'back_urls' => [
            'success' => 'http://localhost/almacen-whatsapp-1/pago_exitoso.php?pedido_id=' . $pedidoId,
            'failure' => 'http://localhost/almacen-whatsapp-1/pago_fallido.php?pedido_id=' . $pedidoId,
            'pending' => 'http://localhost/almacen-whatsapp-1/pago_pendiente.php?pedido_id=' . $pedidoId
        ],
        'auto_return' => 'approved',
        'notification_url' => MP_NOTIFICATION_URL . '?pedido_id=' . $pedidoId,
        'external_reference' => strval($pedidoId),
        'statement_descriptor' => 'Almacén WhatsApp'
    ];
    
    // Llamar a la API de Mercado Pago
    $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . MP_ACCESS_TOKEN,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($preferenceData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $mpResponse = json_decode($response, true);
    
    if ($httpCode === 201 && isset($mpResponse['id'])) {
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
