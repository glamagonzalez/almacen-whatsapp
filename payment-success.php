<?php
require_once 'config/config.php';

// Obtener parámetros de MP
$payment_id = $_GET['payment_id'] ?? null;
$status = $_GET['status'] ?? null;
$external_reference = $_GET['external_reference'] ?? null;
$preference_id = $_GET['preference_id'] ?? null;

// Actualizar estado del pedido
if ($payment_id && $preference_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            UPDATE pedidos 
            SET estado = 'pagado', 
                mp_payment_id = ?,
                fecha_pago = NOW()
            WHERE mp_preference_id = ?
        ");
        $stmt->execute([$payment_id, $preference_id]);
        
        // Obtener ID del pedido
        $stmt = $conn->prepare("SELECT id FROM pedidos WHERE mp_preference_id = ?");
        $stmt->execute([$preference_id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        $pedido_id = $pedido['id'] ?? null;
        
    } catch (Exception $e) {
        error_log("Error al actualizar pedido: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pago Exitoso! - Almacén</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            text-align: center;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card">
            <i class="fas fa-check-circle success-icon"></i>
            <h1 class="mt-4 mb-3">¡Pago Exitoso!</h1>
            <p class="lead">Tu compra ha sido procesada correctamente</p>
            
            <div class="order-info">
                <?php if (isset($pedido_id)): ?>
                    <h4 class="mb-3">Detalles del Pedido</h4>
                    <p class="mb-2">
                        <strong>Número de Pedido:</strong> #<?= str_pad($pedido_id, 6, '0', STR_PAD_LEFT) ?>
                    </p>
                <?php endif; ?>
                
                <?php if ($external_reference): ?>
                    <p class="mb-2">
                        <strong>Referencia:</strong> <?= htmlspecialchars($external_reference) ?>
                    </p>
                <?php endif; ?>
                
                <?php if ($payment_id): ?>
                    <p class="mb-2">
                        <strong>ID de Pago:</strong> <?= htmlspecialchars($payment_id) ?>
                    </p>
                <?php endif; ?>
                
                <hr class="my-3">
                
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i>
                    <strong>¿Qué sigue?</strong><br>
                    Recibirás un mensaje de WhatsApp con la confirmación de tu pedido 
                    y los detalles de envío en los próximos minutos.
                </div>
            </div>
            
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="catalogo.php" class="btn btn-custom">
                    <i class="fas fa-shopping-bag me-2"></i>Seguir Comprando
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>Volver al Inicio
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-envelope"></i> 
                    También recibirás un email de confirmación
                </small>
            </div>
        </div>
    </div>
    
    <script>
        // Limpiar localStorage del carrito
        localStorage.removeItem('checkoutData');
        localStorage.removeItem('carrito');
        
        // Confetti animation (opcional)
        setTimeout(() => {
            for (let i = 0; i < 50; i++) {
                createConfetti();
            }
        }, 300);
        
        function createConfetti() {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff'][Math.floor(Math.random() * 5)];
            confetti.style.left = Math.random() * window.innerWidth + 'px';
            confetti.style.top = '-10px';
            confetti.style.opacity = '1';
            confetti.style.borderRadius = '50%';
            document.body.appendChild(confetti);
            
            let pos = 0;
            let opacity = 1;
            const interval = setInterval(() => {
                if (pos > window.innerHeight || opacity <= 0) {
                    clearInterval(interval);
                    confetti.remove();
                } else {
                    pos += 5;
                    opacity -= 0.01;
                    confetti.style.top = pos + 'px';
                    confetti.style.opacity = opacity;
                    confetti.style.left = (parseFloat(confetti.style.left) + (Math.random() - 0.5) * 2) + 'px';
                }
            }, 20);
        }
    </script>
</body>
</html>
