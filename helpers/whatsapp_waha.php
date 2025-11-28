<?php
/**
 * Helper para enviar WhatsApp con WAHA (GRATIS - Funciona en Windows)
 * WAHA está corriendo en Docker puerto 3000
 */

define('WAHA_API_URL', 'http://localhost:3000');
define('WAHA_SESSION', 'almacen-whatsapp');

/**
 * Enviar mensaje de WhatsApp con WAHA
 * 
 * @param string $telefono Número con código país (ej: 5491157816498)
 * @param string $mensaje Texto del mensaje
 * @return array ['success' => bool, 'message_id' => string]
 */
function enviarWhatsAppWAHA($telefono, $mensaje) {
    $url = WAHA_API_URL . '/api/sendText';
    
    $data = [
        'session' => WAHA_SESSION,
        'chatId' => $telefono . '@c.us',
        'text' => $mensaje
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($http_code >= 200 && $http_code < 300) {
        return [
            'success' => true,
            'message_id' => $result['id'] ?? null,
            'response' => $result
        ];
    } else {
        return [
            'success' => false,
            'error' => $result['message'] ?? 'Error desconocido',
            'http_code' => $http_code
        ];
    }
}

/**
 * Verificar si WhatsApp está conectado
 */
function verificarConexionWAHA() {
    $url = WAHA_API_URL . '/api/sessions/' . WAHA_SESSION . '/me';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        return [
            'conectado' => isset($data['id']),
            'numero' => $data['id'] ?? null,
            'nombre' => $data['pushName'] ?? null
        ];
    }
    
    return ['conectado' => false];
}

/**
 * Notificar nuevo pedido al cliente
 */
function notificarNuevoPedido($pedido_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT p.*, 
               c.nombre as cliente_nombre,
               c.telefono as cliente_telefono,
               c.email as cliente_email
        FROM pedidos p
        JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        return ['success' => false, 'error' => 'Pedido no encontrado'];
    }
    
    $mensaje = "🛒 *NUEVO PEDIDO #{$pedido_id}*\n\n";
    $mensaje .= "Hola {$pedido['cliente_nombre']}!\n\n";
    $mensaje .= "Tu pedido fue recibido:\n";
    $mensaje .= "💰 Total: $" . number_format($pedido['total'], 2) . "\n\n";
    $mensaje .= "⏳ Estado: Esperando pago\n";
    $mensaje .= "📧 Te enviamos los detalles por email\n\n";
    $mensaje .= "¡Gracias por tu compra! 😊";
    
    return enviarWhatsAppWAHA($pedido['cliente_telefono'], $mensaje);
}

/**
 * Notificar pago confirmado
 */
function notificarPagoConfirmado($pedido_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono
        FROM pedidos p
        JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        return ['success' => false, 'error' => 'Pedido no encontrado'];
    }
    
    $mensaje = "✅ *PAGO CONFIRMADO*\n\n";
    $mensaje .= "Hola {$pedido['cliente_nombre']}! 🎉\n\n";
    $mensaje .= "Tu pago fue aprobado\n";
    $mensaje .= "📝 Pedido #{$pedido_id}\n";
    $mensaje .= "💰 Total: $" . number_format($pedido['total'], 2) . "\n\n";
    $mensaje .= "🚚 Preparando tu pedido...\n";
    $mensaje .= "Te avisaremos cuando salga!\n\n";
    $mensaje .= "¡Gracias! 😊";
    
    return enviarWhatsAppWAHA($pedido['cliente_telefono'], $mensaje);
}

/**
 * Notificar pedido enviado
 */
function notificarPedidoEnviado($pedido_id, $tracking = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.nombre as cliente_nombre, c.telefono as cliente_telefono
        FROM pedidos p
        JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        return ['success' => false, 'error' => 'Pedido no encontrado'];
    }
    
    $mensaje = "🚚 *PEDIDO EN CAMINO*\n\n";
    $mensaje .= "Hola {$pedido['cliente_nombre']}! 📦\n\n";
    $mensaje .= "Tu pedido #{$pedido_id} fue despachado\n";
    
    if ($tracking) {
        $mensaje .= "🔢 Tracking: {$tracking}\n";
    }
    
    $mensaje .= "\n⏰ Estimado: 24-48 hs\n\n";
    $mensaje .= "¡Gracias por confiar en nosotros! 🙏";
    
    return enviarWhatsAppWAHA($pedido['cliente_telefono'], $mensaje);
}

/**
 * Alerta de stock bajo al admin
 */
function notificarStockBajo($producto_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT nombre, stock, categoria_id
        FROM productos
        WHERE id = ?
    ");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        return ['success' => false, 'error' => 'Producto no encontrado'];
    }
    
    $mensaje = "⚠️ *ALERTA DE STOCK BAJO*\n\n";
    $mensaje .= "📦 Producto: {$producto['nombre']}\n";
    $mensaje .= "📊 Stock actual: {$producto['stock']} unidades\n\n";
    $mensaje .= "⚡ Acción: Reabastecer producto";
    
    // Enviar al admin (tu número)
    return enviarWhatsAppWAHA('5491157816498', $mensaje);
}

/**
 * Enviar imagen por WhatsApp
 */
function enviarImagenWAHA($telefono, $imagen_url, $caption = '') {
    $url = WAHA_API_URL . '/api/sendImage';
    
    $data = [
        'session' => WAHA_SESSION,
        'chatId' => $telefono . '@c.us',
        'file' => [
            'url' => $imagen_url
        ],
        'caption' => $caption
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'success' => ($http_code >= 200 && $http_code < 300),
        'response' => json_decode($response, true)
    ];
}
?>
