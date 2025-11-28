<?php
/**
 * GESTIONAR_IMAGENES.PHP - Vincular imágenes con productos
 */
require_once 'config/database.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Imágenes de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .product-row {
            border-bottom: 1px solid #eee;
            padding: 15px;
            margin-bottom: 10px;
        }
        .product-row:hover {
            background: #f8f9fa;
        }
        .current-image {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        .no-image {
            width: 150px;
            height: 150px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 2px dashed #ccc;
        }
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        .image-preview:hover {
            border-color: #28a745;
            transform: scale(1.1);
        }
        .image-preview.selected {
            border-color: #28a745;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1400px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-white">
                <i class="fas fa-images"></i> Gestionar Imágenes de Productos
            </h1>
            <div>
                <a href="productos.php" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="importar.php" class="btn btn-info">
                    <i class="fas fa-upload"></i> Subir Imágenes
                </a>
            </div>
        </div>

        <!-- Instrucciones -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Cómo usar:</strong>
            <ol class="mb-0">
                <li>Para cada producto, click en la imagen que le corresponde</li>
                <li>O sube una nueva imagen con el botón "Subir nueva"</li>
                <li>Los cambios se guardan automáticamente</li>
            </ol>
        </div>

        <div class="card">
            <div class="card-body">
                <?php
                // Obtener productos sin imagen o con imagen
                $productos = $pdo->query("
                    SELECT p.*, a.ruta as imagen_actual
                    FROM productos p
                    LEFT JOIN archivos a ON p.archivo_id = a.id
                    WHERE p.activo = 1
                    ORDER BY p.nombre
                ")->fetchAll();

                // Obtener todas las imágenes disponibles
                $imagenes = $pdo->query("
                    SELECT id, nombre_original, ruta
                    FROM archivos
                    WHERE tipo LIKE 'image/%'
                    ORDER BY fecha_subida DESC
                ")->fetchAll();

                foreach ($productos as $producto) {
                    ?>
                    <div class="product-row">
                        <div class="row align-items-center">
                            <!-- Imagen actual -->
                            <div class="col-md-2">
                                <?php if ($producto['imagen_actual']): ?>
                                    <img src="<?php echo $producto['imagen_actual']; ?>" 
                                         class="current-image" 
                                         alt="<?php echo $producto['nombre']; ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Info del producto -->
                            <div class="col-md-3">
                                <h5><?php echo $producto['nombre']; ?></h5>
                                <p class="mb-0">
                                    <span class="badge bg-primary"><?php echo $producto['categoria']; ?></span>
                                    <br>
                                    <small class="text-muted">
                                        Compra: $<?php echo number_format($producto['precio_compra'], 2); ?> | 
                                        Venta: $<?php echo number_format($producto['precio_venta'], 2); ?>
                                    </small>
                                </p>
                            </div>

                            <!-- Selector de imágenes -->
                            <div class="col-md-5">
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-mouse-pointer"></i> Click en una imagen para asignarla:
                                </small>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php 
                                    $count = 0;
                                    foreach ($imagenes as $img): 
                                        if ($count >= 5) break; // Mostrar solo 5
                                        $count++;
                                    ?>
                                        <img src="<?php echo $img['ruta']; ?>" 
                                             class="image-preview <?php echo ($producto['archivo_id'] == $img['id']) ? 'selected' : ''; ?>"
                                             onclick="asignarImagen(<?php echo $producto['id']; ?>, <?php echo $img['id']; ?>, this)"
                                             title="<?php echo $img['nombre_original']; ?>">
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($imagenes) > 5): ?>
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                onclick="verTodasImagenes(<?php echo $producto['id']; ?>)">
                                            +<?php echo count($imagenes) - 5; ?> más
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-success mb-2" 
                                        onclick="subirNuevaImagen(<?php echo $producto['id']; ?>)">
                                    <i class="fas fa-upload"></i> Subir nueva
                                </button>
                                <?php if ($producto['archivo_id']): ?>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="quitarImagen(<?php echo $producto['id']; ?>)">
                                        <i class="fas fa-times"></i> Quitar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Modal para subir nueva imagen -->
    <div class="modal fade" id="modalSubirImagen" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subir Nueva Imagen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formSubirImagen" enctype="multipart/form-data">
                        <input type="hidden" id="productoId" name="producto_id">
                        <div class="mb-3">
                            <label class="form-label">Selecciona una imagen</label>
                            <input type="file" name="imagen" class="form-control" 
                                   accept="image/*" required>
                        </div>
                        <div id="previewContainer"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="guardarNuevaImagen()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /**
         * ASIGNAR IMAGEN A PRODUCTO
         */
        function asignarImagen(productoId, archivoId, elemento) {
            // Quitar selección de otras imágenes en la misma fila
            const row = elemento.closest('.product-row');
            row.querySelectorAll('.image-preview').forEach(img => {
                img.classList.remove('selected');
            });
            elemento.classList.add('selected');
            
            // Guardar en base de datos
            fetch('api/asignar_imagen.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    producto_id: productoId,
                    archivo_id: archivoId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('success', '✅ Imagen asignada correctamente');
                    // Recargar página para ver cambios
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrarNotificacion('error', '❌ Error al asignar imagen');
                }
            });
        }

        /**
         * SUBIR NUEVA IMAGEN
         */
        function subirNuevaImagen(productoId) {
            document.getElementById('productoId').value = productoId;
            const modal = new bootstrap.Modal(document.getElementById('modalSubirImagen'));
            modal.show();
        }

        function guardarNuevaImagen() {
            const form = document.getElementById('formSubirImagen');
            const formData = new FormData(form);
            
            fetch('api/subir_imagen_producto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('success', '✅ Imagen subida y asignada');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrarNotificacion('error', '❌ ' + data.message);
                }
            });
        }

        /**
         * QUITAR IMAGEN
         */
        function quitarImagen(productoId) {
            if (!confirm('¿Quitar la imagen de este producto?')) return;
            
            fetch('api/asignar_imagen.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    producto_id: productoId,
                    archivo_id: null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('success', '✅ Imagen quitada');
                    setTimeout(() => location.reload(), 1000);
                }
            });
        }

        /**
         * MOSTRAR NOTIFICACIÓN
         */
        function mostrarNotificacion(tipo, mensaje) {
            const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
            const notif = document.createElement('div');
            notif.className = `alert ${alertClass} position-fixed`;
            notif.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notif.textContent = mensaje;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        // Preview de imagen al seleccionar archivo
        document.querySelector('input[type="file"]')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewContainer').innerHTML = 
                        `<img src="${e.target.result}" class="img-fluid rounded" style="max-height: 300px;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
