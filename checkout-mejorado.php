<?php
/**
 * CHECKOUT MEJORADO v2.0
 * Sistema completo de pago con Mercado Pago, cupones y envío
 */
require_once 'config/database.php';
require_once 'config/mercadopago.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Almacén Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 20px;
            border: none;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 10px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 15px 10px;
            background: white;
            border-radius: 10px;
            position: relative;
        }
        .step.active {
            background: #28a745;
            color: white;
        }
        .step.completed {
            background: #17a2b8;
            color: white;
        }
        .product-mini {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .product-mini img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        .shipping-option {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
        }
        .shipping-option:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }
        .shipping-option.selected {
            border-color: #28a745;
            background: #d4edda;
        }
        .shipping-option input[type="radio"] {
            margin-right: 10px;
        }
        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .summary-line.total {
            border-top: 2px solid #28a745;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 1.3em;
            font-weight: bold;
        }
        .alert-envio-gratis {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-credit-card"></i> Finalizar Compra</h1>
            <p class="lead">🔒 Pago seguro con Mercado Pago</p>
        </div>

        <!-- Indicador de pasos -->
        <div class="step-indicator">
            <div class="step completed" id="step1">
                <i class="fas fa-shopping-cart fa-2x"></i>
                <p class="mb-0 mt-2"><small>1. Carrito</small></p>
            </div>
            <div class="step active" id="step2">
                <i class="fas fa-user fa-2x"></i>
                <p class="mb-0 mt-2"><small>2. Datos</small></p>
            </div>
            <div class="step" id="step3">
                <i class="fas fa-truck fa-2x"></i>
                <p class="mb-0 mt-2"><small>3. Envío</small></p>
            </div>
            <div class="step" id="step4">
                <i class="fas fa-credit-card fa-2x"></i>
                <p class="mb-0 mt-2"><small>4. Pago</small></p>
            </div>
        </div>

        <div class="row">
            <!-- Columna izquierda: Formulario -->
            <div class="col-lg-7">
                <!-- Paso 1: Datos del cliente -->
                <div class="card" id="seccionDatos">
                    <div class="card-body">
                        <h4 class="mb-4"><i class="fas fa-user-circle"></i> Tus datos</h4>
                        <form id="formCheckout">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre completo *</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">WhatsApp * (sin espacios)</label>
                                    <input type="tel" name="telefono" id="telefono" class="form-control" 
                                           placeholder="5491157816498" required pattern="[0-9]+">
                                    <small class="text-muted">Recibirás actualizaciones por WhatsApp</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dirección completa *</label>
                                <input type="text" name="direccion" id="direccion" class="form-control" 
                                       placeholder="Calle, Número, Piso, Depto" required>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Código Postal *</label>
                                    <input type="text" name="codigo_postal" id="codigo_postal" 
                                           class="form-control" required maxlength="8"
                                           onchange="calcularEnvioDesdeCP()">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ciudad *</label>
                                    <input type="text" name="ciudad" id="ciudad" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Provincia *</label>
                                    <select name="provincia" id="provincia" class="form-select" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="Buenos Aires">Buenos Aires</option>
                                        <option value="CABA">CABA</option>
                                        <option value="Catamarca">Catamarca</option>
                                        <option value="Chaco">Chaco</option>
                                        <option value="Chubut">Chubut</option>
                                        <option value="Córdoba">Córdoba</option>
                                        <option value="Corrientes">Corrientes</option>
                                        <option value="Entre Ríos">Entre Ríos</option>
                                        <option value="Formosa">Formosa</option>
                                        <option value="Jujuy">Jujuy</option>
                                        <option value="La Pampa">La Pampa</option>
                                        <option value="La Rioja">La Rioja</option>
                                        <option value="Mendoza">Mendoza</option>
                                        <option value="Misiones">Misiones</option>
                                        <option value="Neuquén">Neuquén</option>
                                        <option value="Río Negro">Río Negro</option>
                                        <option value="Salta">Salta</option>
                                        <option value="San Juan">San Juan</option>
                                        <option value="San Luis">San Luis</option>
                                        <option value="Santa Cruz">Santa Cruz</option>
                                        <option value="Santa Fe">Santa Fe</option>
                                        <option value="Santiago del Estero">Santiago del Estero</option>
                                        <option value="Tierra del Fuego">Tierra del Fuego</option>
                                        <option value="Tucumán">Tucumán</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Aclaraciones (opcional)</label>
                                <textarea name="aclaraciones" id="aclaraciones" class="form-control" rows="2"
                                          placeholder="Entre calles, referencias, timbre, etc."></textarea>
                            </div>

                            <button type="button" class="btn btn-primary btn-lg w-100" onclick="continuarAEnvio()">
                                <i class="fas fa-arrow-right"></i> Continuar al envío
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Paso 2: Opciones de envío -->
                <div class="card d-none" id="seccionEnvio">
                    <div class="card-body">
                        <h4 class="mb-4"><i class="fas fa-truck"></i> Método de envío</h4>
                        
                        <div id="opcionesEnvio">
                            <!-- Envío Express - Entrega desde las 22hs -->
                            <div class="shipping-option" onclick="seleccionarEnvio('express', 1000)">
                                <input type="radio" name="envio" value="express" id="envio_express" checked>
                                <label for="envio_express" style="cursor: pointer; width: 100%;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><i class="fas fa-shipping-fast"></i> Envío Express</strong>
                                            <p class="mb-0 text-muted small">Despacho en menos de 1 hora - Entrega desde las 22:00 hs</p>
                                        </div>
                                        <strong class="text-success" id="costoExpress">$1000</strong>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-envio-gratis mt-3">
                            <i class="fas fa-gift"></i>
                            <strong>¡Envío gratis en compras desde $5000!</strong>
                            <p class="mb-0 small mt-1">Despacho en menos de 1 hora • Entrega desde las 22:00 hs</p>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" onclick="volverADatos()">
                                <i class="fas fa-arrow-left"></i> Volver
                            </button>
                            <button type="button" class="btn btn-primary flex-grow-1" onclick="continuarAPago()">
                                <i class="fas fa-arrow-right"></i> Continuar al pago
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Paso 3: Confirmar y pagar -->
                <div class="card d-none" id="seccionPago">
                    <div class="card-body">
                        <h4 class="mb-4"><i class="fas fa-credit-card"></i> Confirmar y pagar</h4>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Serás redirigido a <strong>Mercado Pago</strong> para completar el pago de forma segura.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary" onclick="volverAEnvio()">
                                <i class="fas fa-arrow-left"></i> Volver
                            </button>
                            <button type="button" class="btn btn-success flex-grow-1 btn-lg" onclick="procesarPago()">
                                <i class="fas fa-lock"></i> Pagar con Mercado Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Resumen -->
            <div class="col-lg-5">
                <div class="card position-sticky" style="top: 20px;">
                    <div class="card-body">
                        <h4 class="mb-3"><i class="fas fa-receipt"></i> Resumen del pedido</h4>
                        
                        <!-- Productos -->
                        <div id="resumenProductos" class="mb-3"></div>

                        <hr>

                        <!-- Totales -->
                        <div id="resumenTotales"></div>

                        <!-- Botón móvil de pagar -->
                        <button type="button" class="btn btn-success btn-lg w-100 d-lg-none mt-3" 
                                onclick="procesarPago()" id="btnPagarMobile" disabled>
                            <i class="fas fa-lock"></i> Pagar con Mercado Pago
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script src="js/checkout-mejorado.js"></script>
</body>
</html>
