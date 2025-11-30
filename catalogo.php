<?php
/**
 * CARRITO DE COMPRAS
 * Página para que los clientes armen su pedido
 */
require_once 'config/database.php';
require_once 'config/mercadopago.php';
session_start();

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - Almacén Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .product-card {
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .price-tag {
            font-size: 1.8em;
            font-weight: bold;
            color: #2e7d32;
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
        }
        .cart-floating {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1400px;">
        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-shopping-cart"></i> Catálogo de Productos</h1>
            <p class="lead">🔒 Pago seguro con Mercado Pago • 📦 Envío a domicilio</p>
        </div>

        <!-- Alerta de método de pago -->
        <div class="alert alert-info text-center mb-4">
            <i class="fas fa-credit-card"></i>
            <strong>Únicamente aceptamos Mercado Pago</strong> • 
            Se paga primero, se envía después • Sin pago en efectivo
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-8 col-8">
                        <input type="text" id="searchProduct" class="form-control" 
                               placeholder="🔍 Buscar producto...">
                    </div>
                    <div class="col-md-4 col-4">
                        <select id="filterCategory" class="form-select">
                            <option value="">📂 Todas</option>
                            <?php
                            $categorias = $pdo->query("SELECT DISTINCT categoria FROM productos WHERE activo = 1 ORDER BY categoria")->fetchAll();
                            foreach ($categorias as $cat) {
                                echo '<option value="'.$cat['categoria'].'">'.$cat['categoria'].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid de productos -->
        <div class="row g-4" id="productsGrid">
            <?php
            $productos = $pdo->query("
                SELECT p.*, a.ruta as imagen_ruta
                FROM productos p
                LEFT JOIN archivos a ON p.archivo_id = a.id
                WHERE p.activo = 1 AND p.stock_actual > 0
                ORDER BY p.nombre
            ")->fetchAll();

            foreach ($productos as $producto) {
                $imagenUrl = $producto['imagen_ruta'] ?? 'https://via.placeholder.com/400x300?text=Sin+Imagen';
                $stockClass = $producto['stock_actual'] <= $producto['stock_minimo'] ? 'text-warning' : 'text-success';
                
                echo '
                <div class="col-md-4 col-lg-3 product-item" data-categoria="'.$producto['categoria'].'">
                    <div class="card product-card">
                        <div class="position-relative">
                            <img src="'.$imagenUrl.'" class="product-image" alt="'.$producto['nombre'].'">
                            <span class="stock-badge '.$stockClass.'">
                                <i class="fas fa-box"></i> '.$producto['stock_actual'].' disponibles
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">'.$producto['nombre'].'</h5>
                            <p class="text-muted mb-2">
                                <small><i class="fas fa-tag"></i> '.$producto['categoria'].'</small>
                            </p>
                            <div class="price-tag mb-3">$'.number_format($producto['precio_venta'], 2).'</div>
                            <button class="btn btn-success w-100" onclick="agregarAlCarrito('.$producto['id'].', \''.$producto['nombre'].'\', '.$producto['precio_venta'].', '.$producto['stock_actual'].')">
                                <i class="fas fa-cart-plus"></i> Agregar al carrito
                            </button>
                        </div>
                    </div>
                </div>';
            }
            ?>
        </div>
    </div>

    <!-- Botón flotante del carrito -->
    <div class="cart-floating">
        <button class="btn btn-primary btn-lg rounded-circle" style="width: 70px; height: 70px;" 
                data-bs-toggle="modal" data-bs-target="#modalCarrito">
            <i class="fas fa-shopping-cart fa-2x"></i>
            <span class="cart-count" id="cartCount">0</span>
        </button>
    </div>

    <!-- Modal del Carrito -->
    <div class="modal fade" id="modalCarrito" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-shopping-cart"></i> Mi Carrito
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="cartItems"></div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Total:</h4>
                        <h3 class="text-success" id="cartTotal">$0.00</h3>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left"></i> Seguir comprando
                    </button>
                    <button type="button" class="btn btn-success" onclick="irACheckout()" id="btnCheckout">
                        <i class="fas fa-credit-card"></i> Pagar con Mercado Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/carrito-mejorado.js"></script>
</body>
</html>
