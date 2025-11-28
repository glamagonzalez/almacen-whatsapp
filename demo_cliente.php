<?php
/**
 * DEMO_CLIENTE.PHP - Vista de cómo ve el cliente final
 */
require_once 'config/database.php';

// Obtener productos con imágenes
$stmt = $pdo->query("
    SELECT p.*, a.ruta as imagen
    FROM productos p
    LEFT JOIN archivos a ON p.archivo_id = a.id
    WHERE p.activo = 1
    ORDER BY p.categoria, p.nombre
    LIMIT 20
");
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Tu Almacén</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .producto-card {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
        }
        .producto-imagen {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            flex-shrink: 0;
        }
        .producto-sin-imagen {
            width: 100px;
            height: 100px;
            background: #e9ecef;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #adb5bd;
            flex-shrink: 0;
        }
        .producto-info {
            flex: 1;
        }
        .producto-nombre {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .producto-precio {
            font-size: 24px;
            color: var(--primary-color);
            font-weight: bold;
        }
        .producto-precio-anterior {
            text-decoration: line-through;
            color: #999;
            font-size: 14px;
            margin-left: 10px;
        }
        .btn-whatsapp {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: bold;
            width: 100%;
            margin-top: 10px;
        }
        .btn-whatsapp:hover {
            background: var(--secondary-color);
            color: white;
        }
        .badge-stock {
            background: #ffc107;
            color: #000;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 11px;
        }
        .badge-categoria {
            background: #e9ecef;
            color: #495057;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 11px;
            margin-right: 5px;
        }
        .carrito-flotante {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            cursor: pointer;
            z-index: 1000;
        }
        .carrito-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        .search-box {
            padding: 15px;
            background: white;
            margin: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .ganancia-badge {
            background: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 5px;
        }

    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h4><i class="fas fa-store"></i> Tu Almacén</h4>
        <p class="mb-0"><small>🔒 Pago con Mercado Pago • 📦 Envíos a domicilio</small></p>
    </div>
    
    <!-- Alerta importante -->
    <div class="alert alert-warning text-center m-3" style="border-radius: 15px;">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Únicamente Mercado Pago</strong> • Se paga primero, se envía después • Sin efectivo
    </div>

    <!-- Búsqueda y Filtros -->
    <div class="search-box">
        <div class="row g-2">
            <div class="col-8">
                <input type="text" class="form-control" placeholder="🔍 Buscar productos..." id="buscar">
            </div>
            <div class="col-4">
                <select class="form-select" id="categoria-select" onchange="filtrarCategoria(this.value)">
                    <option value="">📂 Todas</option>
                    <?php
                    $categorias = $pdo->query("
                        SELECT DISTINCT categoria 
                        FROM productos 
                        WHERE activo = 1 
                        ORDER BY categoria
                    ")->fetchAll();
                    
                    foreach ($categorias as $cat) {
                        echo '<option value="'.htmlspecialchars($cat['categoria']).'">'.
                             htmlspecialchars($cat['categoria']).
                             '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Productos -->
    <div class="container-fluid px-3">
        <?php if (empty($productos)): ?>
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                No hay productos disponibles aún.
            </div>
        <?php else: ?>
            <?php foreach ($productos as $producto): ?>
                <div class="producto-card" data-categoria="<?= htmlspecialchars($producto['categoria']) ?>">
                    <?php if ($producto['imagen']): ?>
                        <img src="<?= htmlspecialchars($producto['imagen']) ?>" 
                             class="producto-imagen" 
                             alt="<?= htmlspecialchars($producto['nombre']) ?>">
                    <?php else: ?>
                        <div class="producto-sin-imagen">
                            <i class="fas fa-box"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="producto-info">
                        <div class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></div>
                        
                        <div class="mb-2">
                            <span class="badge-categoria"><?= htmlspecialchars($producto['categoria']) ?></span>
                            <?php if ($producto['stock_actual'] > 0): ?>
                                <span class="badge-stock">
                                    <i class="fas fa-check"></i> Stock: <?= $producto['stock_actual'] ?> uds
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">Sin stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <span class="producto-precio">$<?= number_format($producto['precio_venta'], 2) ?></span>
                            <?php if ($producto['precio_compra'] > 0): ?>
                                <span class="producto-precio-anterior">$<?= number_format($producto['precio_compra'], 2) ?></span>
                                <span class="ganancia-badge">
                                    <?= $producto['margen_porcentaje'] ?>% OFF
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($producto['descripcion']): ?>
                            <small class="text-muted d-block mt-1">
                                <?= htmlspecialchars(substr($producto['descripcion'], 0, 80)) ?>...
                            </small>
                        <?php endif; ?>
                        
                        <button class="btn btn-whatsapp" onclick="agregarAlCarrito(<?= $producto['id'] ?>)">
                            <i class="fas fa-cart-plus"></i> Agregar al pedido
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Carrito flotante -->
    <div class="carrito-flotante" onclick="verCarrito()">
        <i class="fas fa-shopping-cart"></i>
        <div class="carrito-badge" id="carrito-count">0</div>
    </div>

    <!-- WhatsApp flotante -->
    <a href="https://wa.me/5491112345678?text=Hola, quiero hacer un pedido" 
       style="position: fixed; bottom: 90px; right: 20px; background: #25D366; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-decoration: none; z-index: 1000;">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let carrito = [];

        function agregarAlCarrito(productoId) {
            carrito.push(productoId);
            actualizarContadorCarrito();
            
            // Mostrar feedback
            alert('✅ Producto agregado al pedido');
        }

        function actualizarContadorCarrito() {
            document.getElementById('carrito-count').textContent = carrito.length;
        }

        function verCarrito() {
            if (carrito.length === 0) {
                alert('Tu carrito está vacío');
            } else {
                alert(`Tienes ${carrito.length} productos en tu pedido`);
                // Aquí redirigirías a checkout.php
            }
        }

        let categoriaActual = '';

        // Búsqueda
        document.getElementById('buscar').addEventListener('input', function(e) {
            const texto = e.target.value.toLowerCase();
            const productos = document.querySelectorAll('.producto-card');
            
            productos.forEach(producto => {
                const nombre = producto.querySelector('.producto-nombre').textContent.toLowerCase();
                const categoria = producto.dataset.categoria;
                
                const coincideBusqueda = nombre.includes(texto);
                const coincideCategoria = categoriaActual === '' || categoria === categoriaActual;
                
                if (coincideBusqueda && coincideCategoria) {
                    producto.style.display = 'flex';
                } else {
                    producto.style.display = 'none';
                }
            });
        });

        // Filtrar por categoría
        function filtrarCategoria(categoria) {
            categoriaActual = categoria;
            const productos = document.querySelectorAll('.producto-card');
            const textoBusqueda = document.getElementById('buscar').value.toLowerCase();
            
            // Filtrar productos
            productos.forEach(producto => {
                const nombre = producto.querySelector('.producto-nombre').textContent.toLowerCase();
                const cat = producto.dataset.categoria;
                
                const coincideCategoria = categoria === '' || cat === categoria;
                const coincideBusqueda = textoBusqueda === '' || nombre.includes(textoBusqueda);
                
                if (coincideCategoria && coincideBusqueda) {
                    producto.style.display = 'flex';
                } else {
                    producto.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
