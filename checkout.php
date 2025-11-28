<?php
/**
 * CHECKOUT.PHP - Página de pago con Mercado Pago
 */
require_once 'config/database.php';
require_once 'config/mercadopago.php';
session_start();

// Verificar que hay productos en el carrito (desde localStorage via JavaScript)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Mercado Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .checkout-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
            margin: 0 5px;
        }
        .step.active {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-credit-card"></i> Finalizar Compra</h1>
            <p>🔒 Pago seguro con Mercado Pago</p>
        </div>

        <!-- Pasos -->
        <div class="step-indicator">
            <div class="step active">
                <i class="fas fa-shopping-cart"></i><br>
                <small>Carrito</small>
            </div>
            <div class="step active">
                <i class="fas fa-user"></i><br>
                <small>Datos</small>
            </div>
            <div class="step">
                <i class="fas fa-credit-card"></i><br>
                <small>Pago</small>
            </div>
        </div>

        <!-- Resumen del pedido -->
        <div class="card">
            <div class="card-body">
                <h4><i class="fas fa-box"></i> Resumen de tu pedido</h4>
                <div id="orderSummary"></div>
                <hr>
                <div class="d-flex justify-content-between">
                    <h5>Total:</h5>
                    <h4 class="text-success" id="orderTotal">$0.00</h4>
                </div>
            </div>
        </div>

        <!-- Formulario de datos del cliente -->
        <div class="card">
            <div class="card-body">
                <h4><i class="fas fa-user"></i> Tus datos</h4>
                <form id="formCheckout">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">WhatsApp *</label>
                            <input type="tel" name="telefono" class="form-control" 
                                   placeholder="Ej: 1234567890" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               placeholder="tu@email.com">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Dirección de envío *</label>
                        <textarea name="direccion" class="form-control" rows="2" 
                                  placeholder="Calle, número, barrio, ciudad" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notas adicionales (opcional)</label>
                        <textarea name="notas" class="form-control" rows="2" 
                                  placeholder="Ej: Entre que calles, horario preferido, etc."></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Importante:</strong> 
                        <ul class="mb-0 mt-2">
                            <li>✅ Solo aceptamos pago por <strong>Mercado Pago</strong></li>
                            <li>💳 Debes pagar primero para confirmar tu pedido</li>
                            <li>📦 El envío se realiza después de confirmar el pago</li>
                            <li>❌ No aceptamos efectivo</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fab fa-cc-mercadopago"></i> 
                            Ir a Mercado Pago
                        </button>
                        <a href="catalogo.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al catálogo
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Garantías -->
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <i class="fas fa-shield-alt fa-3x text-success mb-2"></i>
                        <h6>Pago Seguro</h6>
                        <small class="text-muted">Protección de Mercado Pago</small>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-truck fa-3x text-primary mb-2"></i>
                        <h6>Envío Rápido</h6>
                        <small class="text-muted">Después de confirmar el pago</small>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-headset fa-3x text-info mb-2"></i>
                        <h6>Soporte</h6>
                        <small class="text-muted">Contacto por WhatsApp</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script src="js/checkout.js"></script>
</body>
</html>
