<?php
require_once 'config/config.php';

$payment_id = $_GET['payment_id'] ?? null;
$external_reference = $_GET['external_reference'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Pendiente - Almacén</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pending-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 600px;
            text-align: center;
        }
        .pending-icon {
            font-size: 5rem;
            color: #ffc107;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="pending-card">
            <i class="fas fa-clock pending-icon"></i>
            <h1 class="mt-4 mb-3">Pago Pendiente</h1>
            <p class="lead">Tu pago está siendo procesado</p>
            
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i>
                <strong>Estamos verificando tu pago</strong><br>
                Esto puede tomar unos minutos. Te notificaremos por WhatsApp 
                cuando se confirme.
            </div>
            
            <?php if ($external_reference): ?>
                <p class="text-muted mb-3">
                    Referencia: <?= htmlspecialchars($external_reference) ?>
                </p>
            <?php endif; ?>
            
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Volver al Inicio
                </a>
                <a href="catalogo.php" class="btn btn-outline-secondary">
                    <i class="fas fa-shopping-bag me-2"></i>Ver Catálogo
                </a>
            </div>
        </div>
    </div>
</body>
</html>
