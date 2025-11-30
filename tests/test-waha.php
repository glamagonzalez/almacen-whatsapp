<?php
/**
 * TEST: Probar envío de WhatsApp con WAHA
 * 100% GRATIS - Sin límites - Funciona en Windows
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test WAHA WhatsApp - GRATIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 40px; background: #f8f9fa; }
        .test-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .result { margin-top: 20px; padding: 20px; border-radius: 10px; }
        .result.success { background: #d1e7dd; border: 1px solid #0f5132; }
        .result.error { background: #f8d7da; border: 1px solid #842029; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="test-card">
        <h1 class="mb-4">🆓 Test WhatsApp con WAHA</h1>
        
        <div class="alert alert-success">
            <h5>✅ WAHA - 100% Gratis</h5>
            <ul class="mb-0">
                <li><strong>Costo:</strong> GRATIS siempre</li>
                <li><strong>Límites:</strong> Ninguno</li>
                <li><strong>Funciona en:</strong> Windows, Linux, Mac</li>
                <li><strong>Multimedia:</strong> Texto, imágenes, archivos</li>
            </ul>
        </div>

        <!-- Estado de conexión -->
        <div id="statusCheck" class="mb-4">
            <button class="btn btn-info" onclick="verificarConexion()">
                🔍 Verificar Conexión WhatsApp
            </button>
            <div id="connectionStatus" class="mt-2"></div>
        </div>

        <!-- Formulario de prueba -->
        <form id="sendForm" class="mb-4">
            <div class="mb-3">
                <label class="form-label">Número de WhatsApp</label>
                <input type="text" id="telefono" class="form-control" 
                       placeholder="5491157816498" 
                       value="5491157816498" required>
                <small class="text-muted">Con código país, sin + ni espacios</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Mensaje</label>
                <textarea id="mensaje" class="form-control" rows="4" required>¡Hola! 👋

Este es un mensaje de prueba desde WAHA.

🆓 Sistema GRATIS funcionando!
📱 WhatsApp conectado correctamente</textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg">
                📤 Enviar WhatsApp
            </button>
            
            <button type="button" onclick="testPedido()" class="btn btn-success ms-2">
                🛒 Test Notificación Pedido
            </button>
        </form>

        <div id="result"></div>

        <!-- Código PHP -->
        <div class="mt-5">
            <h5>💻 Código PHP para usar en tu sistema:</h5>
            <pre><code>&lt;?php
// Enviar WhatsApp con WAHA
function enviarWhatsAppWAHA($telefono, $mensaje) {
    $url = 'http://localhost:3000/api/sendText';
    
    $data = [
        'session' => 'almacen-whatsapp',
        'chatId' => $telefono . '@c.us',
        'text' => $mensaje
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Usar en tu sistema
$resultado = enviarWhatsAppWAHA('5491157816498', '¡Pedido confirmado! 🎉');
?&gt;</code></pre>
        </div>
    </div>

    <script>
        const API_URL = 'http://localhost:3000';
        const SESSION = 'almacen-whatsapp';

        async function verificarConexion() {
            const status = document.getElementById('connectionStatus');
            status.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Verificando...';
            
            try {
                const response = await fetch(`${API_URL}/api/sessions/${SESSION}/me`);
                const data = await response.json();
                
                if (data.id) {
                    status.innerHTML = `
                        <div class="alert alert-success mt-2">
                            <strong>✅ WhatsApp Conectado</strong><br>
                            📱 Número: ${data.id}<br>
                            👤 Nombre: ${data.pushName || 'Sin nombre'}
                        </div>
                    `;
                } else {
                    status.innerHTML = `
                        <div class="alert alert-warning mt-2">
                            <strong>⚠️ WhatsApp No Conectado</strong><br>
                            <a href="waha-qr.html" class="btn btn-primary btn-sm mt-2">Conectar WhatsApp</a>
                        </div>
                    `;
                }
            } catch (error) {
                status.innerHTML = `
                    <div class="alert alert-danger mt-2">
                        <strong>❌ Error:</strong> ${error.message}
                    </div>
                `;
            }
        }

        document.getElementById('sendForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const telefono = document.getElementById('telefono').value;
            const mensaje = document.getElementById('mensaje').value;
            const result = document.getElementById('result');
            
            result.innerHTML = '<div class="alert alert-info">⏳ Enviando mensaje...</div>';
            
            try {
                const response = await fetch(`${API_URL}/api/sendText`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        session: SESSION,
                        chatId: telefono + '@c.us',
                        text: mensaje
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    result.innerHTML = `
                        <div class="result success">
                            <h5>✅ ¡Mensaje enviado correctamente!</h5>
                            <p><strong>A:</strong> +${telefono}</p>
                            <p><strong>ID:</strong> ${data.id || 'N/A'}</p>
                            <p class="mt-3">📱 Revisa tu WhatsApp!</p>
                        </div>
                    `;
                } else {
                    result.innerHTML = `
                        <div class="result error">
                            <h5>❌ Error al enviar</h5>
                            <p>${JSON.stringify(data)}</p>
                            ${!data.id ? '<a href="waha-qr.html" class="btn btn-primary mt-2">Conectar WhatsApp</a>' : ''}
                        </div>
                    `;
                }
            } catch (error) {
                result.innerHTML = `
                    <div class="result error">
                        <h5>❌ Error</h5>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        });

        async function testPedido() {
            const telefono = document.getElementById('telefono').value;
            const mensaje = `🛒 *NUEVO PEDIDO #12345*

Hola Cliente!

Tu pedido fue recibido:
💰 Total: $5,850.00

⏳ Estado: Esperando pago
📧 Te enviamos los detalles por email

¡Gracias por tu compra! 😊`;
            
            document.getElementById('mensaje').value = mensaje;
            document.getElementById('sendForm').dispatchEvent(new Event('submit'));
        }

        // Verificar conexión al cargar
        window.addEventListener('load', verificarConexion);
    </script>
</body>
</html>
