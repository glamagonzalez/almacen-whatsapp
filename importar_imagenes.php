<?php
/**
 * EXTRAER_IMAGENES_PDF.PHP - Extraer imágenes desde PDF de ofertas
 * 
 * Sube el PDF de ofertas de Maxi Consumo y extrae las imágenes
 */
require_once 'config/database.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extraer Imágenes de PDF</title>
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
        .method-card {
            cursor: pointer;
            transition: all 0.3s;
        }
        .method-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .image-item {
            position: relative;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .image-item .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .image-item:hover .overlay {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1200px;">
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-file-image"></i> Importar Imágenes de Productos</h1>
            <p class="lead">Elige el método que prefieras</p>
        </div>

        <div class="row g-4 mb-4">
            <!-- Método 1: Extraer de PDF -->
            <div class="col-md-4">
                <div class="card method-card h-100" onclick="mostrarMetodo('pdf')">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                        <h4>Desde PDF</h4>
                        <p>Sube el PDF de ofertas de Maxi Consumo y extrae automáticamente las imágenes</p>
                        <span class="badge bg-success">⚡ Automático</span>
                    </div>
                </div>
            </div>

            <!-- Método 2: Desde Carpeta -->
            <div class="col-md-4">
                <div class="card method-card h-100" onclick="mostrarMetodo('carpeta')">
                    <div class="card-body text-center">
                        <i class="fas fa-folder fa-4x text-primary mb-3"></i>
                        <h4>Desde Carpeta</h4>
                        <p>Si ya tenés las imágenes guardadas en una carpeta de tu PC</p>
                        <span class="badge bg-info">📁 Local</span>
                    </div>
                </div>
            </div>

            <!-- Método 3: Buscar en Internet -->
            <div class="col-md-4">
                <div class="card method-card h-100" onclick="mostrarMetodo('internet')">
                    <div class="card-body text-center">
                        <i class="fas fa-globe fa-4x text-success mb-3"></i>
                        <h4>Buscar Online</h4>
                        <p>Busca imágenes automáticamente por nombre de producto</p>
                        <span class="badge bg-warning">🔍 Automático</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Método PDF -->
        <div id="metodo-pdf" class="card" style="display: none;">
            <div class="card-body">
                <h4><i class="fas fa-file-pdf text-danger"></i> Extraer Imágenes de PDF</h4>
                <p class="text-muted">Sube el PDF de ofertas y extraemos las imágenes automáticamente</p>
                
                <form id="formPDF" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Archivo PDF de ofertas</label>
                        <input type="file" name="pdf_file" class="form-control" 
                               accept=".pdf" required>
                        <small class="text-muted">
                            Ejemplo: ofertas_maxi_consumo.pdf
                        </small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> Este método requiere <code>ImageMagick</code> o <code>pdfimages</code>
                        instalado en el servidor. Si no funciona, usa los otros métodos.
                    </div>
                    
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fas fa-upload"></i> Extraer Imágenes del PDF
                    </button>
                </form>
                
                <div id="resultadoPDF" class="mt-4"></div>
            </div>
        </div>

        <!-- Método Carpeta -->
        <div id="metodo-carpeta" class="card" style="display: none;">
            <div class="card-body">
                <h4><i class="fas fa-folder text-primary"></i> Importar desde Carpeta</h4>
                
                <div class="alert alert-warning">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Pasos:</strong>
                    <ol class="mb-0">
                        <li>Descarga el PDF de ofertas de Maxi Consumo</li>
                        <li>Abrilo con un visor de PDF</li>
                        <li>Copiá las imágenes (click derecho → Copiar imagen)</li>
                        <li>Pegálas en una carpeta</li>
                        <li>Subí esas imágenes aquí abajo</li>
                    </ol>
                </div>
                
                <form id="formCarpeta" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Selecciona múltiples imágenes</label>
                        <input type="file" name="imagenes[]" class="form-control" 
                               accept="image/*" multiple required>
                        <small class="text-muted">
                            Puedes seleccionar varias imágenes a la vez (Ctrl + Click)
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-upload"></i> Subir Imágenes
                    </button>
                </form>
                
                <div id="resultadoCarpeta" class="mt-4"></div>
            </div>
        </div>

        <!-- Método Internet -->
        <div id="metodo-internet" class="card" style="display: none;">
            <div class="card-body">
                <h4><i class="fas fa-globe text-success"></i> Buscar Imágenes en Internet</h4>
                <p class="text-muted">Busca automáticamente imágenes para tus productos</p>
                
                <div class="mb-3">
                    <label class="form-label">Productos sin imagen:</label>
                    <div id="productosSinImagen"></div>
                </div>
                
                <button class="btn btn-success btn-lg" onclick="buscarImagenesAutomaticas()">
                    <i class="fas fa-search"></i> Buscar Imágenes Automáticamente
                </button>
                
                <div id="resultadoInternet" class="mt-4"></div>
            </div>
        </div>

        <!-- Botón volver -->
        <div class="text-center mt-4">
            <a href="gestionar_imagenes.php" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Volver a Gestionar Imágenes
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarMetodo(metodo) {
            // Ocultar todos
            document.querySelectorAll('[id^="metodo-"]').forEach(el => {
                el.style.display = 'none';
            });
            
            // Mostrar seleccionado
            document.getElementById('metodo-' + metodo).style.display = 'block';
            
            // Si es internet, cargar productos sin imagen
            if (metodo === 'internet') {
                cargarProductosSinImagen();
            }
        }

        // Subir PDF
        document.getElementById('formPDF')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultado = document.getElementById('resultadoPDF');
            
            resultado.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Extrayendo imágenes del PDF...</div>';
            
            try {
                const response = await fetch('api/extraer_pdf.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultado.innerHTML = `
                        <div class="alert alert-success">
                            ✅ Se extrajeron ${data.cantidad} imágenes del PDF
                        </div>
                        <div class="image-grid">
                            ${data.imagenes.map(img => `
                                <div class="image-item">
                                    <img src="${img.ruta}" alt="${img.nombre}">
                                    <div class="overlay">
                                        <small class="text-white">${img.nombre}</small>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <a href="gestionar_imagenes.php" class="btn btn-success mt-3">
                            Ir a asignar imágenes a productos
                        </a>
                    `;
                } else {
                    resultado.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
                }
            } catch (error) {
                resultado.innerHTML = `<div class="alert alert-danger">❌ Error: ${error.message}</div>`;
            }
        });

        // Subir múltiples imágenes desde carpeta
        document.getElementById('formCarpeta')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultado = document.getElementById('resultadoCarpeta');
            
            resultado.innerHTML = '<div class="alert alert-info">Subiendo imágenes...</div>';
            
            try {
                const response = await fetch('api/subir_imagenes_masivo.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultado.innerHTML = `
                        <div class="alert alert-success">
                            ✅ Se subieron ${data.cantidad} imágenes correctamente
                        </div>
                        <div class="image-grid">
                            ${data.imagenes.map(img => `
                                <div class="image-item">
                                    <img src="${img.ruta}" alt="${img.nombre}">
                                    <div class="overlay">
                                        <small class="text-white">${img.nombre}</small>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        <a href="gestionar_imagenes.php" class="btn btn-success mt-3">
                            Ir a asignar imágenes a productos
                        </a>
                    `;
                } else {
                    resultado.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
                }
            } catch (error) {
                resultado.innerHTML = `<div class="alert alert-danger">❌ Error al subir imágenes</div>`;
            }
        });

        // Cargar productos sin imagen
        async function cargarProductosSinImagen() {
            const div = document.getElementById('productosSinImagen');
            div.innerHTML = '<div class="spinner-border"></div> Cargando...';
            
            try {
                const response = await fetch('api/productos_sin_imagen.php');
                const data = await response.json();
                
                if (data.success && data.productos.length > 0) {
                    div.innerHTML = `
                        <div class="list-group">
                            ${data.productos.map(p => `
                                <div class="list-group-item">
                                    <strong>${p.nombre}</strong>
                                    <span class="badge bg-primary float-end">${p.categoria}</span>
                                </div>
                            `).join('')}
                        </div>
                        <p class="text-muted mt-2">Total: ${data.productos.length} productos</p>
                    `;
                } else {
                    div.innerHTML = '<p class="text-success">✅ Todos los productos tienen imagen</p>';
                }
            } catch (error) {
                div.innerHTML = '<p class="text-danger">Error al cargar productos</p>';
            }
        }

        // Buscar imágenes automáticamente
        async function buscarImagenesAutomaticas() {
            const resultado = document.getElementById('resultadoInternet');
            resultado.innerHTML = '<div class="alert alert-info">🔍 Buscando imágenes en internet...</div>';
            
            try {
                const response = await fetch('api/buscar_imagenes_automatico.php');
                const data = await response.json();
                
                if (data.success) {
                    resultado.innerHTML = `
                        <div class="alert alert-success">
                            ✅ Se encontraron ${data.cantidad} imágenes
                        </div>
                        <div class="alert alert-warning">
                            <strong>Nota:</strong> Las imágenes se descargaron y guardaron automáticamente.
                            Ve a <a href="gestionar_imagenes.php">Gestionar Imágenes</a> para asignarlas.
                        </div>
                    `;
                } else {
                    resultado.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
                }
            } catch (error) {
                resultado.innerHTML = `<div class="alert alert-danger">❌ Error en la búsqueda</div>`;
            }
        }
    </script>
</body>
</html>
