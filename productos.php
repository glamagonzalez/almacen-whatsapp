<?php
/**
 * PRODUCTOS.PHP - Gestión de productos con precios
 */
require_once 'config/database.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Almacén WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1400px; }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .price-profit {
            background: #e8f5e9;
            padding: 5px 10px;
            border-radius: 5px;
            color: #2e7d32;
            font-weight: bold;
        }
        .price-cost {
            background: #fff3e0;
            padding: 5px 10px;
            border-radius: 5px;
            color: #e65100;
        }
        .stock-low { color: #d32f2f; font-weight: bold; }
        .stock-ok { color: #388e3c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-white">
                <i class="fas fa-box-open"></i> Gestión de Productos
            </h1>
            <div>
                <a href="index.php" class="btn btn-light me-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="gestionar_imagenes.php" class="btn btn-warning me-2">
                    <i class="fas fa-images"></i> Gestionar Imágenes
                </a>
                <a href="importar_csv.php" class="btn btn-info me-2">
                    <i class="fas fa-file-csv"></i> Importar CSV
                </a>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalProducto">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </button>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <?php
            $statsTotal = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1")->fetch();
            $statsBajoStock = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE stock_actual <= stock_minimo AND activo = 1")->fetch();
            $statsValorInventario = $pdo->query("SELECT SUM(precio_venta * stock_actual) as total FROM productos WHERE activo = 1")->fetch();
            ?>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                        <h3><?php echo $statsTotal['total']; ?></h3>
                        <small class="text-muted">Productos Activos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                        <h3><?php echo $statsBajoStock['total']; ?></h3>
                        <small class="text-muted">Bajo Stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                        <h3>$<?php echo number_format($statsValorInventario['total'], 2); ?></h3>
                        <small class="text-muted">Valor Inventario</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de productos -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio Compra</th>
                                <th>Margen</th>
                                <th>Precio Venta</th>
                                <th>Ganancia</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productosTable">
                            <?php
                            $productos = $pdo->query("
                                SELECT p.*, a.ruta as imagen_ruta
                                FROM productos p
                                LEFT JOIN archivos a ON p.archivo_id = a.id
                                WHERE p.activo = 1
                                ORDER BY p.nombre
                            ")->fetchAll();

                            foreach ($productos as $producto) {
                                $ganancia = $producto['precio_venta'] - $producto['precio_compra'];
                                $stockClass = $producto['stock_actual'] <= $producto['stock_minimo'] ? 'stock-low' : 'stock-ok';
                                
                                $imagenUrl = $producto['imagen_ruta'] ?? 'https://via.placeholder.com/80?text=Sin+Imagen';
                                
                                echo '<tr>
                                    <td><img src="'.$imagenUrl.'" class="product-image" alt="'.$producto['nombre'].'"></td>
                                    <td>
                                        <strong>'.$producto['nombre'].'</strong><br>
                                        <small class="text-muted">'.$producto['proveedor'].'</small>
                                    </td>
                                    <td><span class="badge bg-info">'.$producto['categoria'].'</span></td>
                                    <td><span class="price-cost">$'.number_format($producto['precio_compra'], 2).'</span></td>
                                    <td>'.$producto['margen_porcentaje'].'%</td>
                                    <td><span class="price-profit">$'.number_format($producto['precio_venta'], 2).'</span></td>
                                    <td class="text-success">+$'.number_format($ganancia, 2).'</td>
                                    <td class="'.$stockClass.'">'.$producto['stock_actual'].' uds</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editarProducto('.$producto['id'].')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info" onclick="enviarWhatsApp('.$producto['id'].')">
                                            <i class="fab fa-whatsapp"></i>
                                        </button>
                                    </td>
                                </tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Producto -->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-box"></i> Nuevo Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formProducto">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Nombre del Producto *</label>
                                    <input type="text" name="nombre" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Código de Barras</label>
                                    <input type="text" name="codigo_barras" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Categoría *</label>
                                    <select name="categoria" class="form-select" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="Alimentos">Alimentos</option>
                                        <option value="Bebidas">Bebidas</option>
                                        <option value="Limpieza">Limpieza</option>
                                        <option value="Higiene">Higiene Personal</option>
                                        <option value="Snacks">Snacks</option>
                                        <option value="Lácteos">Lácteos</option>
                                        <option value="Otros">Otros</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Proveedor</label>
                                    <input type="text" name="proveedor" class="form-control" value="Maxi Consumo">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">💵 Precio Compra *</label>
                                    <input type="number" name="precio_compra" id="precio_compra" 
                                           class="form-control" step="0.01" required
                                           onchange="calcularPrecioVenta()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">📊 Margen (%) *</label>
                                    <input type="number" name="margen_porcentaje" id="margen_porcentaje" 
                                           class="form-control" value="30" step="0.01" required
                                           onchange="calcularPrecioVenta()">
                                    <small class="text-muted">Ejemplo: 30% de ganancia</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">💰 Precio Venta</label>
                                    <input type="number" name="precio_venta" id="precio_venta" 
                                           class="form-control" step="0.01" required readonly>
                                    <small class="text-success" id="gananciaTexto"></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Stock Actual</label>
                                    <input type="number" name="stock_actual" class="form-control" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Stock Mínimo (Alerta)</label>
                                    <input type="number" name="stock_minimo" class="form-control" value="5">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Imagen del Producto</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="guardarProducto()">
                        <i class="fas fa-save"></i> Guardar Producto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/productos.js"></script>
</body>
</html>
