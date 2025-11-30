<?php
/**
 * TEST: Probar envío de WhatsApp con Twilio
 * $15 USD GRATIS = ~3000 mensajes
 * 
 * SETUP:
 * 1. Registrate: https://www.twilio.com/try-twilio
 * 2. Obtenés $15 de crédito gratis
 * 3. En la consola de Twilio:
 *    - Copia tu Account SID
 *    - Copia tu Auth Token
 * 4. Pegalos en helpers/whatsapp_twilio.php
 * 5. Activa WhatsApp Sandbox:
 *    - Ve a Messaging > Try it out > Send a WhatsApp message
 *    - Envía el código que te dan al número de Twilio
 * 6. Accede a: http://localhost/almacen-whatsapp-1/test-twilio.php
 */

require_once 'helpers/whatsapp_twilio.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Twilio WhatsApp - $15 GRATIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 40px; background: #f8f9fa; }
        .test-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .setup-steps { background: #cfe2ff; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .sandbox-steps { background: #fff3cd; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .result { margin-top: 20px; padding: 20px; border-radius: 10px; }
        .result.success { background: #d1e7dd; border: 1px solid #0f5132; }
        .result.error { background: #f8d7da; border: 1px solid #842029; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .highlight { background: #ffeb3b; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-card">
            <h1 class="mb-4">💰 Test Twilio WhatsApp - $15 USD GRATIS</h1>
            
            <!-- Instrucciones de Setup -->
            <div class="setup-steps">
                <h5>🎁 Setup (5 minutos - UNA SOLA VEZ):</h5>
                <ol>
                    <li>Registrate en: <a href="https://www.twilio.com/try-twilio" target="_blank"><strong>https://www.twilio.com/try-twilio</strong></a></li>
                    <li>Verificá tu email y celular</li>
                    <li>Obtenés <span class="highlight">$15 USD de crédito gratis</span> (~3000 mensajes)</li>
                    <li>En la consola de Twilio, copia:
                        <ul>
                            <li><strong>Account SID</strong> (empieza con AC...)</li>
                            <li><strong>Auth Token</strong> (haz click en "show" para verlo)</li>
                        </ul>
                    </li>
                    <li>Abre <code>helpers/whatsapp_twilio.php</code></li>
                    <li>Pega tus credenciales en las líneas 14-15</li>
                </ol>
            </div>

            <!-- Instrucciones Sandbox -->
            <div class="sandbox-steps">
                <h5>📱 Activar WhatsApp Sandbox:</h5>
                <ol>
                    <li>En Twilio Console: <strong>Messaging</strong> → <strong>Try it out</strong> → <strong>Send a WhatsApp message</strong></li>
                    <li>Te dan un código como: <code>join abc-123</code></li>
                    <li>Desde tu WhatsApp (1157816498), envía ese código a: <strong>+1 415 523 8886</strong></li>
                    <li>Te responde: "You are all set!"</li>
                    <li>¡Listo para enviar mensajes!</li>
                </ol>
                <small class="text-muted">⚠️ El Sandbox solo envía a números que se unieron con el código</small>
            </div>

            <!-- Formulario de prueba -->
            <form method="POST" class="mb-4">
                <div class="mb-3">
                    <label class="form-label">Número de WhatsApp (con código país)</label>
                    <input type="text" name="telefono" class="form-control" 
                           placeholder="5491157816498" 
                           value="5491157816498" required>
                    <small class="text-muted">El número debe estar registrado en el Sandbox de Twilio</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Mensaje</label>
                    <textarea name="mensaje" class="form-control" rows="4" required>¡Hola! 👋

Este es un mensaje de prueba desde Twilio.

💰 Estás usando $15 USD GRATIS
🚀 Sistema funcionando correctamente!</textarea>
                </div>
                
                <button type="submit" name="enviar" class="btn btn-primary btn-lg">
                    📤 Enviar WhatsApp con Twilio
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
                    echo "<h5>⏳ Enviando mensaje con Twilio...</h5>";
                    
                    $resultado = enviarWhatsAppTwilio($telefono, $mensaje);
                    
                    if ($resultado['success']) {
                        echo "<div class='result success'>";
                        echo "<h5>✅ ¡Mensaje enviado correctamente con Twilio!</h5>";
                        echo "<p><strong>A:</strong> +{$telefono}</p>";
                        echo "<p><strong>Message SID:</strong> {$resultado['message_sid']}</p>";
                        echo "<p><strong>Estado:</strong> {$resultado['status']}</p>";
                        echo "<p class='mt-3'>📱 Revisa tu WhatsApp!</p>";
                        echo "<p class='text-muted'>💰 Crédito usado: ~$0.005 USD</p>";
                        echo "</div>";
                    } else {
                        echo "<div class='result error'>";
                        echo "<h5>❌ Error al enviar</h5>";
                        echo "<p><strong>Error:</strong> {$resultado['error']}</p>";
                        
                        if (isset($resultado['code'])) {
                            echo "<p><strong>Código:</strong> {$resultado['code']}</p>";
                        }
                        
                        if (strpos($resultado['error'], 'credenciales') !== false) {
                            echo "<div class='alert alert-warning mt-3'>";
                            echo "<strong>⚠️ Necesitas configurar tus credenciales:</strong><br>";
                            echo "1. Ve a <a href='https://console.twilio.com' target='_blank'>Twilio Console</a><br>";
                            echo "2. Copia tu <strong>Account SID</strong> y <strong>Auth Token</strong><br>";
                            echo "3. Pégalos en <code>helpers/whatsapp_twilio.php</code> líneas 14-15";
                            echo "</div>";
                        } else if (strpos($resultado['error'], 'not a valid') !== false) {
                            echo "<div class='alert alert-warning mt-3'>";
                            echo "<strong>⚠️ Este número no está en el Sandbox:</strong><br>";
                            echo "1. Ve a <strong>Messaging > Try it out > Send a WhatsApp message</strong><br>";
                            echo "2. Envía el código (join xxx) desde el número {$telefono} a +1 415 523 8886<br>";
                            echo "3. Espera confirmación \"You are all set!\"<br>";
                            echo "4. Vuelve a probar aquí";
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
                    
                    $resultado = enviarWhatsAppTwilio($telefono, $mensaje);
                    
                    if ($resultado['success']) {
                        echo "<div class='result success'>";
                        echo "<h5>✅ Notificación de pedido enviada con Twilio!</h5>";
                        echo "<p>📱 Revisa tu WhatsApp</p>";
                        echo "<p><strong>Message SID:</strong> {$resultado['message_sid']}</p>";
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
                <h5>💰 Información de Costos:</h5>
                <ul>
                    <li><strong>Crédito inicial:</strong> $15 USD GRATIS al registrarte</li>
                    <li><strong>Costo por mensaje:</strong> $0.005 USD (~$1.50 por 300 mensajes)</li>
                    <li><strong>Con $15 gratis:</strong> ~3000 mensajes WhatsApp</li>
                    <li><strong>Tipos:</strong> Texto, imágenes, documentos, ubicación</li>
                    <li><strong>Ideal para:</strong> Desarrollo, pruebas y producción pequeña</li>
                </ul>
                
                <h5 class="mt-4">✅ Ventajas de Twilio:</h5>
                <ul>
                    <li>✅ Funciona en Windows sin problemas</li>
                    <li>✅ Profesional y confiable (99.9% uptime)</li>
                    <li>✅ Documentación excelente</li>
                    <li>✅ Dashboard con estadísticas</li>
                    <li>✅ Soporte técnico</li>
                    <li>✅ Envío de multimedia (imágenes, PDFs)</li>
                </ul>
            </div>

            <!-- Código de ejemplo -->
            <div class="mt-5">
                <h5>💻 Código PHP para usar en tu sistema:</h5>
                <pre><code>&lt;?php
require_once 'helpers/whatsapp_twilio.php';

// Enviar mensaje simple
$resultado = enviarWhatsAppTwilio('5491157816498', '¡Hola desde Twilio! 🚀');

if ($resultado['success']) {
    echo "✅ Mensaje enviado! SID: " . $resultado['message_sid'];
} else {
    echo "❌ Error: " . $resultado['error'];
}

// Notificar nuevo pedido (automático)
notificarNuevoPedidoTwilio($pedido_id);

// Notificar pago confirmado
notificarPagoConfirmadoTwilio($pedido_id);

// Notificar envío
notificarPedidoEnviadoTwilio($pedido_id, 'TRACK123');

// Alerta stock bajo
notificarStockBajoTwilio($producto_id);
?&gt;</code></pre>
            </div>

            <!-- Enlaces útiles -->
            <div class="mt-4 p-3 bg-light rounded">
                <h6>🔗 Enlaces útiles:</h6>
                <ul class="mb-0">
                    <li><a href="https://www.twilio.com/try-twilio" target="_blank">Registrarse en Twilio</a></li>
                    <li><a href="https://console.twilio.com" target="_blank">Consola de Twilio</a></li>
                    <li><a href="https://www.twilio.com/console/sms/whatsapp/sandbox" target="_blank">WhatsApp Sandbox</a></li>
                    <li><a href="https://www.twilio.com/docs/whatsapp" target="_blank">Documentación WhatsApp</a></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
