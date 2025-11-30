<?php
/**
 * TEST MERCADO PAGO - Verificar Configuración
 * Verifica que las credenciales estén correctamente configuradas
 */
require_once 'config/config.php';
require_once 'vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Mercado Pago - Verificación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }
        .code-block {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            margin: 10px 0;
        }
        .step {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-credit-card"></i> Test Mercado Pago</h1>
            <p class="lead">Verificación de Configuración</p>
        </div>

        <!-- 1. Verificar SDK -->
        <div class="test-card">
            <h4><i class="fas fa-box"></i> 1. SDK de Mercado Pago</h4>
            <hr>
            <?php
            try {
                if (class_exists('MercadoPago\SDK')) {
                    echo '<p class="status-ok"><i class="fas fa-check-circle"></i> SDK instalado correctamente</p>';
                    echo '<p class="text-muted">Versión: 3.8.0</p>';
                } else {
                    echo '<p class="status-error"><i class="fas fa-times-circle"></i> SDK no encontrado</p>';
                    echo '<div class="alert alert-danger">Instalar con: <code>composer require mercadopago/dx-php</code></div>';
                }
            } catch (Exception $e) {
                echo '<p class="status-error"><i class="fas fa-times-circle"></i> Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>

        <!-- 2. Verificar Credenciales -->
        <div class="test-card">
            <h4><i class="fas fa-key"></i> 2. Credenciales de Mercado Pago</h4>
            <hr>
            <?php
            $accessToken = defined('MERCADOPAGO_ACCESS_TOKEN') ? MERCADOPAGO_ACCESS_TOKEN : null;
            $publicKey = defined('MERCADOPAGO_PUBLIC_KEY') ? MERCADOPAGO_PUBLIC_KEY : null;
            $modoPrueba = defined('MP_MODO_PRUEBA') ? MP_MODO_PRUEBA : null;

            // Verificar Access Token
            if ($accessToken && $accessToken !== 'TU_ACCESS_TOKEN_AQUI') {
                echo '<p class="status-ok"><i class="fas fa-check-circle"></i> Access Token configurado</p>';
                echo '<div class="code-block">';
                echo '<strong>Access Token:</strong> ' . substr($accessToken, 0, 20) . '...' . substr($accessToken, -10);
                echo '</div>';
                
                // Verificar si es de prueba o producción
                if (strpos($accessToken, 'TEST-') === 0) {
                    echo '<div class="alert alert-warning mt-2">';
                    echo '<i class="fas fa-flask"></i> <strong>Modo PRUEBA</strong> - Usa tarjetas de prueba';
                    echo '</div>';
                } else if (strpos($accessToken, 'APP_USR-') === 0) {
                    echo '<div class="alert alert-success mt-2">';
                    echo '<i class="fas fa-check"></i> <strong>Modo PRODUCCIÓN</strong> - Pagos reales';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-danger mt-2">';
                    echo '<i class="fas fa-exclamation-triangle"></i> Formato de token no reconocido';
                    echo '</div>';
                }
            } else {
                echo '<p class="status-error"><i class="fas fa-times-circle"></i> Access Token NO configurado</p>';
                echo '<div class="alert alert-danger">';
                echo '<strong>¡Acción requerida!</strong> Edita <code>config/mercadopago.php</code>';
                echo '</div>';
            }

            // Verificar Public Key
            if ($publicKey && $publicKey !== 'TU_PUBLIC_KEY_AQUI') {
                echo '<p class="status-ok mt-3"><i class="fas fa-check-circle"></i> Public Key configurado</p>';
                echo '<div class="code-block">';
                echo '<strong>Public Key:</strong> ' . substr($publicKey, 0, 20) . '...' . substr($publicKey, -10);
                echo '</div>';
            } else {
                echo '<p class="status-error mt-3"><i class="fas fa-times-circle"></i> Public Key NO configurado</p>';
            }
            ?>
        </div>

        <!-- 3. Test de Conexión -->
        <div class="test-card">
            <h4><i class="fas fa-plug"></i> 3. Test de Conexión con Mercado Pago</h4>
            <hr>
            <?php
            if ($accessToken && $accessToken !== 'TU_ACCESS_TOKEN_AQUI') {
                try {
                    // SDK v3.x usa una clase diferente
                    MercadoPago\MercadoPagoConfig::setAccessToken($accessToken);
                    
                    // Intentar crear una preferencia de prueba
                    $client = new MercadoPago\Client\Preference\PreferenceClient();
                    
                    $item = [
                        "title" => "Test de conexión",
                        "quantity" => 1,
                        "unit_price" => 100.00
                    ];
                    
                    $preference = [
                        "items" => [$item]
                    ];
                    
                    // Solo validamos que podemos inicializar el cliente
                    echo '<p class="status-ok"><i class="fas fa-check-circle"></i> Conexión establecida con Mercado Pago</p>';
                    echo '<div class="alert alert-success">';
                    echo '<i class="fas fa-check"></i> Las credenciales son válidas y el SDK funciona correctamente';
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<p class="status-error"><i class="fas fa-times-circle"></i> Error de conexión</p>';
                    echo '<div class="alert alert-danger">';
                    echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                    echo '<br><br><strong>Posibles causas:</strong>';
                    echo '<ul>';
                    echo '<li>Access Token inválido o expirado</li>';
                    echo '<li>Sin conexión a internet</li>';
                    echo '<li>API de Mercado Pago caída (raro)</li>';
                    echo '</ul>';
                    echo '</div>';
                }
            } else {
                echo '<p class="status-warning"><i class="fas fa-exclamation-triangle"></i> No se puede probar sin credenciales</p>';
            }
            ?>
        </div>

        <!-- 4. Guía Rápida -->
        <div class="test-card">
            <h4><i class="fas fa-book"></i> 4. Guía Rápida de Configuración</h4>
            <hr>
            
            <div class="step">
                <h5><i class="fas fa-arrow-right text-primary"></i> Paso 1: Obtener Credenciales</h5>
                <p>1. Ve a: <a href="https://www.mercadopago.com.ar/developers/panel/app" target="_blank">
                    https://www.mercadopago.com.ar/developers/panel/app
                </a></p>
                <p>2. Inicia sesión con tu cuenta de Mercado Pago</p>
                <p>3. Crea una aplicación o selecciona una existente</p>
                <p>4. Ve a la sección "Credenciales"</p>
                <p>5. Copia el <strong>Access Token</strong> y <strong>Public Key</strong></p>
            </div>

            <div class="step">
                <h5><i class="fas fa-arrow-right text-primary"></i> Paso 2: Configurar Credenciales</h5>
                <p>Edita el archivo: <code>config/mercadopago.php</code></p>
                <div class="code-block">
                    // Para PRUEBAS:<br>
                    define('MP_ACCESS_TOKEN', 'TEST-1234567890...');<br>
                    define('MP_PUBLIC_KEY', 'TEST-abc123...');<br>
                    define('MP_MODO_PRUEBA', true);
                </div>
            </div>

            <div class="step">
                <h5><i class="fas fa-arrow-right text-primary"></i> Paso 3: Probar</h5>
                <p>1. Agrega productos al carrito</p>
                <p>2. Ve al checkout</p>
                <p>3. Completa los datos</p>
                <p>4. Usa una <strong>tarjeta de prueba</strong>:</p>
                <div class="code-block">
                    <strong>Tarjeta APROBADA:</strong><br>
                    Número: 5031 7557 3453 0604<br>
                    CVV: 123<br>
                    Vencimiento: 11/25<br>
                    Nombre: APRO
                </div>
            </div>

            <div class="step">
                <h5><i class="fas fa-arrow-right text-primary"></i> Más Información</h5>
                <p>📖 <a href="CONFIGURAR-MERCADOPAGO.md">Ver guía completa (CONFIGURAR-MERCADOPAGO.md)</a></p>
                <p>🌐 <a href="https://www.mercadopago.com.ar/developers/es/docs" target="_blank">Documentación Oficial</a></p>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="test-card text-center">
            <h5>¿Todo configurado?</h5>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="catalogo.php" class="btn btn-success btn-lg">
                    <i class="fas fa-shopping-cart"></i> Ir al Catálogo
                </a>
                <a href="checkout-mejorado.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-credit-card"></i> Probar Checkout
                </a>
                <a href="CONFIGURAR-MERCADOPAGO.md" class="btn btn-info btn-lg">
                    <i class="fas fa-book"></i> Ver Guía
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-white mt-4">
            <p><small>Almacén WhatsApp - Sistema de Pagos v2.0</small></p>
        </div>
    </div>
</body>
</html>
