<?php
require_once 'config/config.php';

$payment_id = $_GET['payment_id'] ?? null;
$status = $_GET['status'] ?? null;
$external_reference = $_GET['external_reference'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en el Pago - Almacén</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 600px;
            text-align: center;
        }
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            animation: shake 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin: 2rem 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-card">
            <i class="fas fa-times-circle error-icon"></i>
            <h1 class="mt-4 mb-3">Error en el Pago</h1>
            <p class="lead">No pudimos procesar tu pago</p>
            
            <div class="info-box">
                <h5><i class="fas fa-info-circle text-warning"></i> ¿Qué pasó?</h5>
                <ul class="mb-0 text-start">
                    <li>El pago fue rechazado por el medio de pago</li>
                    <li>Puede haber fondos insuficientes</li>
                    <li>Los datos ingresados pueden ser incorrectos</li>
                    <li>El límite de la tarjeta fue alcanzado</li>
                </ul>
            </div>
            
            <?php if ($external_reference): ?>
                <p class="text-muted mb-3">
                    <small>Referencia: <?= htmlspecialchars($external_reference) ?></small>
                </p>
            <?php endif; ?>
            
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="checkout-mejorado.php" class="btn btn-primary">
                    <i class="fas fa-redo me-2"></i>Intentar Nuevamente
                </a>
                <a href="catalogo.php" class="btn btn-outline-secondary">
                    <i class="fas fa-shopping-bag me-2"></i>Volver al Catálogo
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-headset"></i> 
                    Si necesitas ayuda, contáctanos por WhatsApp
                </small>
            </div>
        </div>
    </div>
</body>
</html>
