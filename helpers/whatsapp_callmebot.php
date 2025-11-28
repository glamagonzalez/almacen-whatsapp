<?php
/**
 * Helper para enviar WhatsApp GRATIS con CallMeBot
 * 
 * SETUP:
 * 1. Guarda en tu celular: +34 644 31 81 81
 * 2. Envía WhatsApp: "I allow callmebot to send me messages"
 * 3. Te responde con tu API KEY
 * 4. Pega tu API KEY abajo en $CALLMEBOT_API_KEY
 */

// ⚠️ REEMPLAZA CON TU API KEY ⚠️
define('CALLMEBOT_API_KEY', 'TU_API_KEY_AQUI');

/**
 * Enviar mensaje de WhatsApp gratis
 * 
 * @param string $telefono Número con código país (ej: 5491157816498)
 * @param string $mensaje Texto del mensaje
 * @return array ['success' => bool, 'response' => string]
 */
function enviarWhatsAppGratis($telefono, $mensaje) {
    $apikey = CALLMEBOT_API_KEY;
    
    // Validar API key
    if ($apikey === 'TU_API_KEY_AQUI') {
        return [
            'success' => false,
            'error' => 'Necesitas configurar tu API KEY de CallMeBot en helpers/whatsapp_callmebot.php'
        ];
    }
    
    // Construir URL
    $url = "https://api.callmebot.com/whatsapp.php?" . http_build_query([
        'phone' => $telefono,
        'text' => $mensaje,
        'apikey' => $apikey
    ]);
    
    // Enviar request
    try {
        $response = @file_get_contents($url);
        
        if ($response === false) {
            return [
                'success' => false,
                'error' => 'Error al conectar con CallMeBot'
            ];
        }
        
        return [
            'success' => true,
            'response' => $response
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Notificar nuevo pedido al cliente (usando CallMeBot)
 */
function notificarNuevoPedidoGratis($pedido_id) {
    global $pdo;
    
    // Obtener datos del pedido
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
    
    // Formatear mensaje
    $mensaje = "🛒 *NUEVO PEDIDO #{$pedido_id}*\n\n";
    $mensaje .= "Hola {$pedido['cliente_nombre']}!\n\n";
    $mensaje .= "Tu pedido fue recibido:\n";
    $mensaje .= "💰 Total: $" . number_format($pedido['total'], 2) . "\n\n";
    $mensaje .= "⏳ Estado: Esperando pago\n";
    $mensaje .= "📧 Te enviamos los detalles por email\n\n";
    $mensaje .= "¡Gracias por tu compra! 😊";
    
    // Enviar WhatsApp
    return enviarWhatsAppGratis($pedido['cliente_telefono'], $mensaje);
}

/**
 * Notificar pago confirmado
 */
function notificarPagoConfirmadoGratis($pedido_id) {
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
    
    return enviarWhatsAppGratis($pedido['cliente_telefono'], $mensaje);
}

/**
 * Notificar pedido enviado
 */
function notificarPedidoEnviadoGratis($pedido_id, $tracking = null) {
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
    
    return enviarWhatsAppGratis($pedido['cliente_telefono'], $mensaje);
}

/**
 * Alerta de stock bajo al admin
 */
function notificarStockBajoGratis($producto_id) {
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
    return enviarWhatsAppGratis('5491157816498', $mensaje);
}
?>
