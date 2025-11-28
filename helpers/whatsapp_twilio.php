<?php
/**
 * Helper para enviar WhatsApp con Twilio
 * $15 USD GRATIS al registrarte = ~3000 mensajes
 * 
 * SETUP:
 * 1. Registrate en: https://www.twilio.com/try-twilio
 * 2. Obtenés $15 de crédito gratis
 * 3. Ve a la consola y copia:
 *    - Account SID
 *    - Auth Token
 * 4. Pegalos abajo
 */

// ⚠️ REEMPLAZA CON TUS DATOS DE TWILIO ⚠️
define('TWILIO_ACCOUNT_SID', 'TU_ACCOUNT_SID_AQUI');
define('TWILIO_AUTH_TOKEN', 'TU_AUTH_TOKEN_AQUI');
define('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'); // Número de Twilio (no cambiar)

/**
 * Enviar mensaje de WhatsApp con Twilio
 * 
 * @param string $telefono Número con código país (ej: 5491157816498)
 * @param string $mensaje Texto del mensaje
 * @return array ['success' => bool, 'message_sid' => string]
 */
function enviarWhatsAppTwilio($telefono, $mensaje) {
    $sid = TWILIO_ACCOUNT_SID;
    $token = TWILIO_AUTH_TOKEN;
    
    // Validar credenciales
    if ($sid === 'TU_ACCOUNT_SID_AQUI') {
        return [
            'success' => false,
            'error' => 'Necesitas configurar tus credenciales de Twilio en helpers/whatsapp_twilio.php'
        ];
    }
    
    // URL de la API
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    
    // Preparar datos
    $data = [
        'From' => TWILIO_WHATSAPP_FROM,
        'To' => 'whatsapp:+' . $telefono,
        'Body' => $mensaje
    ];
    
    // Enviar request con cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Procesar respuesta
    $result = json_decode($response, true);
    
    if ($http_code == 201 && isset($result['sid'])) {
        return [
            'success' => true,
            'message_sid' => $result['sid'],
            'status' => $result['status']
        ];
    } else {
        return [
            'success' => false,
            'error' => isset($result['message']) ? $result['message'] : 'Error desconocido',
            'code' => isset($result['code']) ? $result['code'] : null
        ];
    }
}

/**
 * Notificar nuevo pedido al cliente
 */
function notificarNuevoPedidoTwilio($pedido_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT p.*, 
               c.nombre as cliente_nombre,
               c.telefono as cliente_telefono
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
    
    return enviarWhatsAppTwilio($pedido['cliente_telefono'], $mensaje);
}

/**
 * Notificar pago confirmado
 */
function notificarPagoConfirmadoTwilio($pedido_id) {
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
    
    return enviarWhatsAppTwilio($pedido['cliente_telefono'], $mensaje);
}

/**
 * Notificar pedido enviado
 */
function notificarPedidoEnviadoTwilio($pedido_id, $tracking = null) {
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
    
    return enviarWhatsAppTwilio($pedido['cliente_telefono'], $mensaje);
}

/**
 * Alerta de stock bajo al admin
 */
function notificarStockBajoTwilio($producto_id) {
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
    
    return enviarWhatsAppTwilio('5491157816498', $mensaje);
}
?>
