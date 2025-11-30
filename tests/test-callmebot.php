<?php
/**
 * TEST: Probar envío de WhatsApp GRATIS con CallMeBot
 * 
 * 📱 ANTES DE USAR:
 * 1. Guarda en tu celular: +34 644 31 81 81
 * 2. Envía WhatsApp: "I allow callmebot to send me messages"
 * 3. Te responde con tu API KEY
 * 4. Abre helpers/whatsapp_callmebot.php
 * 5. Reemplaza 'TU_API_KEY_AQUI' con tu API KEY
 * 6. Accede a: http://localhost/almacen-whatsapp-1/test-callmebot.php
 */

require_once 'helpers/whatsapp_callmebot.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test CallMeBot WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 40px; background: #f8f9fa; }
        .test-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .setup-steps { background: #fff3cd; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .result { margin-top: 20px; padding: 20px; border-radius: 10px; }
        .result.success { background: #d1e7dd; border: 1px solid #0f5132; }
        .result.error { background: #f8d7da; border: 1px solid #842029; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-card">
            <h1 class="mb-4">🆓 Test WhatsApp Gratis - CallMeBot</h1>
            
            <!-- Instrucciones de Setup -->
            <div class="setup-steps">
                <h5>📱 Setup (solo 1 vez):</h5>
                <ol>
                    <li>Guarda en tu celular: <strong>+34 644 31 81 81</strong></li>
                    <li>Envía WhatsApp a ese número: <code>I allow callmebot to send me messages</code></li>
                    <li>Te responde con tu API KEY</li>
                    <li>Abre <code>helpers/whatsapp_callmebot.php</code></li>
                    <li>Reemplaza <code>TU_API_KEY_AQUI</code> con tu API KEY</li>
                    <li>Refresca esta página y prueba!</li>
                </ol>
                <small class="text-muted">⏱️ Limitación: 1 mensaje cada 5 segundos</small>
            </div>

            <!-- Formulario de prueba -->
            <form method="POST" class="mb-4">
                <div class="mb-3">
                    <label class="form-label">Número de WhatsApp (con código país)</label>
                    <input type="text" name="telefono" class="form-control" 
                           placeholder="5491157816498" 
                           value="5491157816498" required>
                    <small class="text-muted">Sin espacios ni símbolos</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mensaje</label>
                    <textarea name="mensaje" class="form-control" rows="4" required>¡Hola! 👋

Este es un mensaje de prueba desde tu sistema de almacén.

🚀 CallMeBot funcionando correctamente!</textarea>
                </div>
                
                <button type="submit" name="enviar" class="btn btn-primary btn-lg">
                    📤 Enviar WhatsApp de Prueba
                </button>
                
                <button type="submit" name="test_pedido" class="btn btn-success ms-2">
                    🛒 Test Notificación Pedido
                </button>
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // Test mensaje simple
                if (isset($_POST['enviar'])) {
                    $telefono = $_POST['telefono'];
                    $mensaje = $_POST['mensaje'];
                    
                    echo "<div class='result'>";
                    echo "<h5>⏳ Enviando mensaje...</h5>";
                    
                    $resultado = enviarWhatsAppGratis($telefono, $mensaje);
                    
                    if ($resultado['success']) {
                        echo "<div class='result success'>";
                        echo "<h5>✅ ¡Mensaje enviado correctamente!</h5>";
                        echo "<p><strong>A:</strong> +{$telefono}</p>";
                        echo "<p><strong>Respuesta:</strong> {$resultado['response']}</p>";
                        echo "<p class='mt-3'>📱 Revisa tu WhatsApp!</p>";
                        echo "</div>";
                    } else {
                        echo "<div class='result error'>";
                        echo "<h5>❌ Error al enviar</h5>";
                        echo "<p><strong>Error:</strong> {$resultado['error']}</p>";
                        
                        if (strpos($resultado['error'], 'API KEY') !== false) {
                            echo "<div class='alert alert-warning mt-3'>";
                            echo "<strong>⚠️ Necesitas configurar tu API KEY:</strong><br>";
                            echo "1. Envía WhatsApp a <strong>+34 644 31 81 81</strong><br>";
                            echo "2. Mensaje: <code>I allow callmebot to send me messages</code><br>";
                            echo "3. Copia tu API KEY<br>";
                            echo "4. Pégala en <code>helpers/whatsapp_callmebot.php</code>";
                            echo "</div>";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                }
                
                // Test notificación de pedido
                if (isset($_POST['test_pedido'])) {
                    echo "<div class='result'>";
                    echo "<h5>🛒 Test: Notificación de Nuevo Pedido</h5>";
                    
                    $telefono = $_POST['telefono'];
                    
                    $mensaje = "🛒 *NUEVO PEDIDO #12345*\n\n";
                    $mensaje .= "Hola Cliente!\n\n";
                    $mensaje .= "Tu pedido fue recibido:\n";
                    $mensaje .= "💰 Total: $5,850.00\n\n";
                    $mensaje .= "⏳ Estado: Esperando pago\n";
                    $mensaje .= "📧 Te enviamos los detalles por email\n\n";
                    $mensaje .= "¡Gracias por tu compra! 😊";
                    
                    $resultado = enviarWhatsAppGratis($telefono, $mensaje);
                    
                    if ($resultado['success']) {
                        echo "<div class='result success'>";
                        echo "<h5>✅ Notificación de pedido enviada!</h5>";
                        echo "<p>📱 Revisa tu WhatsApp</p>";
                        echo "</div>";
                    } else {
                        echo "<div class='result error'>";
                        echo "<h5>❌ Error: {$resultado['error']}</h5>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
            }
            ?>

            <!-- Información adicional -->
            <div class="mt-5">
                <h5>ℹ️ Información</h5>
                <ul>
                    <li><strong>Costo:</strong> 100% GRATIS</li>
                    <li><strong>Límite:</strong> 1 mensaje cada 5 segundos</li>
                    <li><strong>Tipos:</strong> Solo texto (no imágenes)</li>
                    <li><strong>Ideal para:</strong> Desarrollo y pruebas</li>
                </ul>
                
                <h5 class="mt-4">🚀 Producción (después):</h5>
                <p>Cuando tu sistema crezca, migra a:</p>
                <ul>
                    <li><strong>Twilio:</strong> $0.005 por mensaje (~$1.50 por 300 mensajes)</li>
                    <li><strong>Wati.io:</strong> $49/mes ilimitado + CRM</li>
                    <li><strong>Meta Cloud API:</strong> Gratis hasta 1000 conversaciones/mes</li>
                </ul>
            </div>

            <!-- Código de ejemplo -->
            <div class="mt-5">
                <h5>💻 Código PHP para usar en tu sistema:</h5>
                <pre><code>&lt;?php
require_once 'helpers/whatsapp_callmebot.php';

// Enviar mensaje simple
$resultado = enviarWhatsAppGratis('5491157816498', '¡Hola desde PHP! 🚀');

if ($resultado['success']) {
    echo "✅ Mensaje enviado!";
} else {
    echo "❌ Error: " . $resultado['error'];
}

// Notificar nuevo pedido (automático)
notificarNuevoPedidoGratis($pedido_id);

// Notificar pago confirmado
notificarPagoConfirmadoGratis($pedido_id);

// Notificar envío
notificarPedidoEnviadoGratis($pedido_id, 'TRACK123');

// Alerta stock bajo
notificarStockBajoGratis($producto_id);
?&gt;</code></pre>
            </div>
        </div>
    </div>
</body>
</html>
