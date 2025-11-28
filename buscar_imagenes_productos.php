<?php
/**
 * BUSCAR_IMAGENES_PRODUCTOS.PHP - Buscar y descargar imágenes para tus productos
 */
require_once 'config/database.php';
session_start();

// Obtener productos sin imagen
$stmt = $pdo->query("
    SELECT id, nombre, categoria, descripcion
    FROM productos
    WHERE activo = 1 AND (archivo_id IS NULL OR archivo_id = 0)
    ORDER BY nombre
");
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Imágenes Automáticamente</title>
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
        .producto-card {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
        }
        .imagen-sugerida {
            width: 100%;
            max-width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .imagen-sugerida:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .imagen-sugerida.selected {
            border: 4px solid #28a745;
        }
        .progress-container {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1400px;">
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-search"></i> Buscar Imágenes para Productos</h1>
            <p class="lead">Te sugiero imágenes para cada producto</p>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Cómo funciona:</strong>
                    <ol class="mb-0">
                        <li>El sistema busca imágenes para cada producto en bases de datos públicas</li>
                        <li>Te muestra varias opciones por producto</li>
                        <li>Elegís la que más te gusta (click en la imagen)</li>
                        <li>Se descarga y asigna automáticamente</li>
                    </ol>
                </div>

                <?php if (empty($productos)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>¡Perfecto!</strong> Todos tus productos ya tienen imagen asignada.
                    </div>
                    <a href="gestionar_imagenes.php" class="btn btn-primary">
                        <i class="fas fa-images"></i> Ver Imágenes
                    </a>
                <?php else: ?>
                    <h4>Productos sin imagen: <?= count($productos) ?></h4>
                    
                    <button class="btn btn-success btn-lg mb-4" onclick="buscarTodasLasImagenes()">
                        <i class="fas fa-magic"></i> Buscar Imágenes Automáticamente
                    </button>

                    <div id="progress-container" class="progress-container" style="display: none;">
                        <div class="progress" style="height: 30px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <p class="text-center mt-2" id="progress-text">Iniciando búsqueda...</p>
                    </div>

                    <div id="resultados">
                        <?php foreach ($productos as $producto): ?>
                            <div class="producto-card" id="producto-<?= $producto['id'] ?>">
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5><?= htmlspecialchars($producto['nombre']) ?></h5>
                                        <p class="text-muted"><?= htmlspecialchars($producto['categoria']) ?></p>
                                        <div id="status-<?= $producto['id'] ?>" class="badge bg-secondary">
                                            Esperando...
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div id="imagenes-<?= $producto['id'] ?>" class="row g-2">
                                            <!-- Imágenes sugeridas aparecerán aquí -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="gestionar_imagenes.php" class="btn btn-light">
                <i class="fas fa-images"></i> Ir a Gestionar Imágenes
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const productos = <?= json_encode($productos) ?>;
        let procesamientoActual = 0;

        async function buscarTodasLasImagenes() {
            document.getElementById('progress-container').style.display = 'block';
            
            for (let i = 0; i < productos.length; i++) {
                const producto = productos[i];
                procesamientoActual = i + 1;
                
                actualizarProgreso(procesamientoActual, productos.length);
                
                await buscarImagenesProducto(producto);
                
                // Esperar un poco entre búsquedas
                await sleep(500);
            }
            
            document.getElementById('progress-text').innerHTML = 
                '<strong class="text-success">✅ ¡Completado! Ahora elegí las imágenes que más te gusten.</strong>';
        }

        async function buscarImagenesProducto(producto) {
            const statusDiv = document.getElementById('status-' + producto.id);
            const imagenesDiv = document.getElementById('imagenes-' + producto.id);
            
            statusDiv.className = 'badge bg-warning';
            statusDiv.textContent = 'Buscando...';
            
            try {
                const response = await fetch('api/buscar_imagenes_producto.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        producto_id: producto.id,
                        nombre: producto.nombre,
                        categoria: producto.categoria
                    })
                });
                
                const data = await response.json();
                
                if (data.success && data.imagenes.length > 0) {
                    statusDiv.className = 'badge bg-success';
                    statusDiv.textContent = `${data.imagenes.length} imágenes encontradas`;
                    
                    imagenesDiv.innerHTML = data.imagenes.map((img, index) => `
                        <div class="col-md-3 col-sm-6">
                            <img src="${img.url}" 
                                 class="imagen-sugerida" 
                                 onclick="seleccionarImagen(${producto.id}, '${img.url}', ${index})"
                                 id="img-${producto.id}-${index}"
                                 alt="Opción ${index + 1}">
                            <small class="d-block text-center mt-1">Opción ${index + 1}</small>
                        </div>
                    `).join('');
                } else {
                    statusDiv.className = 'badge bg-danger';
                    statusDiv.textContent = 'No se encontraron imágenes';
                    imagenesDiv.innerHTML = `
                        <div class="alert alert-warning">
                            No se encontraron imágenes para "${producto.nombre}".
                            Intenta buscar manualmente o subir una imagen.
                        </div>
                    `;
                }
            } catch (error) {
                statusDiv.className = 'badge bg-danger';
                statusDiv.textContent = 'Error';
                console.error(error);
            }
        }

        async function seleccionarImagen(productoId, imageUrl, index) {
            // Marcar como seleccionada
            const img = document.getElementById(`img-${productoId}-${index}`);
            
            // Desmarcar otras del mismo producto
            document.querySelectorAll(`[id^="img-${productoId}-"]`).forEach(i => {
                i.classList.remove('selected');
            });
            
            img.classList.add('selected');
            
            const statusDiv = document.getElementById('status-' + productoId);
            statusDiv.className = 'badge bg-info';
            statusDiv.textContent = 'Descargando...';
            
            try {
                const response = await fetch('api/descargar_asignar_imagen.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        producto_id: productoId,
                        image_url: imageUrl
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    statusDiv.className = 'badge bg-success';
                    statusDiv.innerHTML = '<i class="fas fa-check"></i> ¡Asignada!';
                    
                    // Opcional: ocultar el producto después de 2 segundos
                    setTimeout(() => {
                        document.getElementById('producto-' + productoId).style.opacity = '0.5';
                    }, 2000);
                } else {
                    statusDiv.className = 'badge bg-danger';
                    statusDiv.textContent = 'Error al asignar';
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                statusDiv.className = 'badge bg-danger';
                statusDiv.textContent = 'Error';
                console.error(error);
            }
        }

        function actualizarProgreso(actual, total) {
            const porcentaje = Math.round((actual / total) * 100);
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            
            progressBar.style.width = porcentaje + '%';
            progressBar.textContent = porcentaje + '%';
            progressText.textContent = `Buscando imágenes para producto ${actual} de ${total}...`;
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
    </script>
</body>
</html>
