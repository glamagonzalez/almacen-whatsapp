<?php
/**
 * HELPER N8N - Funciones para enviar notificaciones
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/n8n.php';

/**
 * Notificar nuevo pedido
 */
function notificarNuevoPedido($pedidoId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM pedidos p
        WHERE p.id = ?
    ");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) return false;
    
    $productos = json_decode($pedido['productos_json'], true);
    
    $data = [
        'evento' => 'nuevo_pedido',
        'pedido_id' => $pedido['id'],
        'cliente_nombre' => $pedido['cliente_nombre'],
        'cliente_telefono' => $pedido['cliente_telefono'],
        'cliente_email' => $pedido['cliente_email'],
        'direccion' => $pedido['direccion_entrega'],
        'total' => $pedido['total'],
        'productos' => $productos,
        'fecha' => $pedido['fecha_creacion'],
        'estado' => $pedido['estado'],
        'mp_preference_id' => $pedido['mp_preference_id']
    ];
    
    // Enviar a n8n
    return enviarEventoN8n(N8N_WEBHOOK_NUEVO_PEDIDO, $data);
}

/**
 * Notificar pago confirmado
 */
function notificarPagoConfirmado($pedidoId, $pagoInfo) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) return false;
    
    $data = [
        'evento' => 'pago_confirmado',
        'pedido_id' => $pedido['id'],
        'cliente_nombre' => $pedido['cliente_nombre'],
        'cliente_telefono' => $pedido['cliente_telefono'],
        'total' => $pedido['total'],
        'mp_payment_id' => $pagoInfo['id'] ?? null,
        'metodo_pago' => $pagoInfo['payment_method_id'] ?? 'mercadopago',
        'fecha_pago' => date('Y-m-d H:i:s')
    ];
    
    return enviarEventoN8n(N8N_WEBHOOK_PAGO_CONFIRMADO, $data);
}

/**
 * Notificar pedido enviado
 */
function notificarPedidoEnviado($pedidoId, $trackingInfo = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) return false;
    
    $data = [
        'evento' => 'pedido_enviado',
        'pedido_id' => $pedido['id'],
        'cliente_nombre' => $pedido['cliente_nombre'],
        'cliente_telefono' => $pedido['cliente_telefono'],
        'direccion' => $pedido['direccion_entrega'],
        'tracking' => $trackingInfo,
        'fecha_envio' => date('Y-m-d H:i:s')
    ];
    
    return enviarEventoN8n(N8N_WEBHOOK_PEDIDO_ENVIADO, $data);
}

/**
 * Notificar stock bajo
 */
function notificarStockBajo($productoId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$productoId]);
    $producto = $stmt->fetch();
    
    if (!$producto) return false;
    
    $data = [
        'evento' => 'stock_bajo',
        'producto_id' => $producto['id'],
        'producto_nombre' => $producto['nombre'],
        'stock_actual' => $producto['stock_actual'],
        'stock_minimo' => $producto['stock_minimo'],
        'categoria' => $producto['categoria'],
        'fecha' => date('Y-m-d H:i:s')
    ];
    
    return enviarEventoN8n(N8N_WEBHOOK_STOCK_BAJO, $data);
}

/**
 * Enviar WhatsApp de confirmación al cliente
 */
function enviarConfirmacionWhatsApp($pedidoId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) return false;
    
    $productos = json_decode($pedido['productos_json'], true);
    
    $mensaje = "🛒 *PEDIDO CONFIRMADO #" . $pedido['id'] . "*\n\n";
    $mensaje .= "Hola " . $pedido['cliente_nombre'] . "! ✅\n\n";
    $mensaje .= "📦 *Tu pedido:*\n";
    
    foreach ($productos as $prod) {
        $mensaje .= "• " . $prod['nombre'] . " x" . $prod['cantidad'] . " = $" . number_format($prod['subtotal'], 2) . "\n";
    }
    
    $mensaje .= "\n💰 *TOTAL: $" . number_format($pedido['total'], 2) . "*\n\n";
    $mensaje .= "📍 Dirección: " . $pedido['direccion_entrega'] . "\n";
    $mensaje .= "🚚 Te avisaremos cuando despachemos tu pedido.\n\n";
    $mensaje .= "¡Gracias por tu compra! 😊";
    
    return enviarWhatsApp($pedido['cliente_telefono'], $mensaje);
}
?>
