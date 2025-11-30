<?php
require_once 'config/database.php';

// Obtener productos activos
$stmt = $pdo->prepare("
    SELECT 
        id, 
        nombre, 
        descripcion, 
        precio_venta as precio, 
        stock_actual as stock, 
        categoria, 
        COALESCE(imagen_url, imagen) as imagen
    FROM productos 
    WHERE activo = 1
    ORDER BY nombre ASC
");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén Digital - Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding-top: 20px;
            padding-bottom: 80px;
        }

        .header {
            background: white;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: var(--primary-color);
            text-align: center;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #f0f0f0;
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-weight: bold;
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }

        .product-price {
            font-size: 24px;
            color: var(--success-color);
            font-weight: bold;
            margin: 10px 0;
        }

        .btn-add-cart {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-add-cart:hover {
            background: var(--secondary-color);
            transform: scale(1.05);
        }

        .cart-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: var(--success-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s;
        }

        .cart-float:hover {
            transform: scale(1.1);
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 15px;
            margin: 20px 0;
        }

        .empty-state i {
            font-size: 60px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .stock-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .stock-badge.in-stock {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-badge.low-stock {
            background: #fef3c7;
            color: #92400e;
        }

        .stock-badge.out-stock {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-store"></i> Almacén Digital</h1>
        </div>

        <!-- Productos Grid -->
        <div class="row" id="productos-container">
            <?php if (count($productos) > 0): ?>
                <?php foreach ($productos as $producto): ?>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="product-card">
                            <img src="<?php echo $producto['imagen'] ?: 'https://via.placeholder.com/300x200?text=Sin+Imagen'; ?>" 
                                 class="product-image" 
                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                                <div class="product-price">$<?php echo number_format($producto['precio'], 2); ?></div>
                                <?php
                                $stock = $producto['stock'];
                                if ($stock <= 0) {
                                    echo '<span class="stock-badge out-stock">Sin stock</span>';
                                } elseif ($stock <= 5) {
                                    echo '<span class="stock-badge low-stock">Quedan ' . $stock . '</span>';
                                } else {
                                    echo '<span class="stock-badge in-stock">Disponible</span>';
                                }
                                ?>
                                <button class="btn-add-cart" 
                                        onclick="agregarAlCarrito(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>', <?php echo $producto['precio']; ?>, '<?php echo htmlspecialchars($producto['imagen']); ?>')"
                                        <?php echo $producto['stock'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-cart-plus"></i> 
                                    <?php echo $producto['stock'] <= 0 ? 'Sin Stock' : 'Agregar al Carrito'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No hay productos disponibles</h3>
                        <p>Vuelve pronto para ver nuestros productos</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Carrito flotante -->
    <div class="cart-float" onclick="irAlCarrito()">
        <i class="fas fa-shopping-cart"></i>
        <div class="cart-badge" id="cart-count">0</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function agregarAlCarrito(productoId, nombre, precio, imagen) {
            // Obtener carrito actual (formato de carrito-mejorado.js)
            let carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
            
            // Buscar si el producto ya está en el carrito
            const index = carrito.findIndex(item => item.id === productoId);
            
            if (index !== -1) {
                carrito[index].cantidad++;
            } else {
                carrito.push({ 
                    id: productoId, 
                    nombre: nombre,
                    precio: precio,
                    imagen: imagen,
                    cantidad: 1 
                });
            }
            
            // Guardar en localStorage
            localStorage.setItem('carrito', JSON.stringify(carrito));
            
            // Actualizar contador
            actualizarContadorCarrito();
            
            // Mostrar feedback
            mostrarToast('✅ Producto agregado al carrito');
        }

        function actualizarContadorCarrito() {
            const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
            const total = carrito.reduce((sum, item) => sum + item.cantidad, 0);
            document.getElementById('cart-count').textContent = total;
        }

        function irAlCarrito() {
            window.location.href = 'checkout-mejorado.php';
        }

        function mostrarToast(mensaje) {
            // Crear toast simple
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                bottom: 100px;
                right: 20px;
                background: #10b981;
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
            `;
            toast.textContent = mensaje;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        }

        // Actualizar contador al cargar
        document.addEventListener('DOMContentLoaded', () => {
            actualizarContadorCarrito();
        });
    </script>
    
    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
</body>
</html>
