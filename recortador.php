<?php
/**
 * RECORTADOR DE IMÁGENES - Herramienta para recortar productos de catálogos
 */
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recortador de Catálogos</title>
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
        }
        #canvas-container {
            position: relative;
            display: inline-block;
            cursor: crosshair;
            max-width: 100%;
            overflow: auto;
        }
        #catalog-image {
            max-width: 100%;
            display: block;
            user-select: none;
        }
        .selection-box {
            position: absolute;
            border: 3px dashed #00ff00;
            background: rgba(0, 255, 0, 0.1);
            pointer-events: none;
        }
        .crop-preview {
            border: 2px solid #ddd;
            border-radius: 8px;
            margin: 10px;
            padding: 10px;
            display: inline-block;
            background: white;
        }
        .crop-preview img {
            max-width: 200px;
            display: block;
        }
        .instructions {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1400px;">
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-crop"></i> Recortador de Catálogos</h1>
            <p class="lead">Recorta productos de catálogos de mayoristas fácilmente</p>
        </div>

        <!-- Instrucciones -->
        <div class="card mb-4">
            <div class="card-body">
                <h4><i class="fas fa-info-circle"></i> Cómo usar:</h4>
                <div class="instructions">
                    <ol class="mb-0">
                        <li><strong>Sube la imagen</strong> del catálogo completo (la que me mostraste)</li>
                        <li><strong>Click y arrastra</strong> sobre cada producto para seleccionarlo</li>
                        <li>Se va a <strong>recortar automáticamente</strong> y aparece abajo</li>
                        <li>Escribí el <strong>nombre del producto</strong> en cada recorte</li>
                        <li>Click en <strong>"Descargar Todo"</strong> para guardar todas las imágenes</li>
                        <li>Después usá <strong>"Importar desde Carpeta"</strong> para subirlas al sistema</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Upload -->
        <div class="card mb-4">
            <div class="card-body">
                <h4><i class="fas fa-upload"></i> Paso 1: Subir catálogo</h4>
                <input type="file" id="catalog-upload" class="form-control" accept="image/*">
                <small class="text-muted">Sube la imagen completa del catálogo de Maxi Consumo</small>
            </div>
        </div>

        <!-- Canvas -->
        <div class="card mb-4" id="canvas-card" style="display: none;">
            <div class="card-body">
                <h4><i class="fas fa-crop"></i> Paso 2: Recortar productos</h4>
                <p class="text-muted">Click y arrastra sobre cada producto para recortarlo</p>
                
                <div id="canvas-container">
                    <img id="catalog-image" alt="Catálogo">
                    <div id="selection-box" class="selection-box" style="display: none;"></div>
                </div>

                <div class="mt-3">
                    <button class="btn btn-warning" onclick="resetSelection()">
                        <i class="fas fa-undo"></i> Limpiar selección
                    </button>
                    <button class="btn btn-info" onclick="undoLastCrop()">
                        <i class="fas fa-arrow-left"></i> Deshacer último
                    </button>
                </div>
            </div>
        </div>

        <!-- Previews -->
        <div class="card" id="previews-card" style="display: none;">
            <div class="card-body">
                <h4><i class="fas fa-images"></i> Paso 3: Productos recortados (<span id="crop-count">0</span>)</h4>
                <div id="crops-preview"></div>
                
                <div class="mt-3">
                    <button class="btn btn-success btn-lg" onclick="downloadAll()">
                        <i class="fas fa-download"></i> Descargar Todas las Imágenes
                    </button>
                    <button class="btn btn-danger" onclick="clearAllCrops()">
                        <i class="fas fa-trash"></i> Borrar todas
                    </button>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-arrow-right"></i>
                    <strong>Siguiente paso:</strong> Una vez descargadas, andá a 
                    <a href="importar_imagenes.php" class="alert-link">Importar Imágenes → Desde Carpeta</a>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-light">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script>
        let catalogImage = null;
        let isSelecting = false;
        let startX, startY;
        let crops = [];
        let cropCounter = 1;

        // Upload image
        document.getElementById('catalog-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.getElementById('catalog-image');
                img.src = event.target.result;
                catalogImage = img;
                
                document.getElementById('canvas-card').style.display = 'block';
                setupCanvas();
            };
            reader.readAsDataURL(file);
        });

        function setupCanvas() {
            const container = document.getElementById('canvas-container');
            const img = document.getElementById('catalog-image');
            const selectionBox = document.getElementById('selection-box');

            // Mouse down - start selection
            container.addEventListener('mousedown', function(e) {
                if (e.target !== img) return;
                
                isSelecting = true;
                const rect = img.getBoundingClientRect();
                startX = e.clientX - rect.left;
                startY = e.clientY - rect.top;
                
                selectionBox.style.left = startX + 'px';
                selectionBox.style.top = startY + 'px';
                selectionBox.style.width = '0px';
                selectionBox.style.height = '0px';
                selectionBox.style.display = 'block';
            });

            // Mouse move - update selection
            container.addEventListener('mousemove', function(e) {
                if (!isSelecting) return;
                
                const rect = img.getBoundingClientRect();
                const currentX = e.clientX - rect.left;
                const currentY = e.clientY - rect.top;
                
                const width = currentX - startX;
                const height = currentY - startY;
                
                if (width > 0 && height > 0) {
                    selectionBox.style.width = width + 'px';
                    selectionBox.style.height = height + 'px';
                }
            });

            // Mouse up - crop selection
            container.addEventListener('mouseup', function(e) {
                if (!isSelecting) return;
                isSelecting = false;
                
                const rect = img.getBoundingClientRect();
                const endX = e.clientX - rect.left;
                const endY = e.clientY - rect.top;
                
                const width = endX - startX;
                const height = endY - startY;
                
                if (width > 20 && height > 20) {
                    cropImage(startX, startY, width, height);
                }
                
                selectionBox.style.display = 'none';
            });
        }

        function cropImage(x, y, width, height) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Get original image dimensions
            const img = document.getElementById('catalog-image');
            const scaleX = img.naturalWidth / img.width;
            const scaleY = img.naturalHeight / img.height;
            
            canvas.width = width * scaleX;
            canvas.height = height * scaleY;
            
            ctx.drawImage(
                img,
                x * scaleX, y * scaleY,
                width * scaleX, height * scaleY,
                0, 0,
                canvas.width, canvas.height
            );
            
            const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
            
            crops.push({
                id: cropCounter,
                dataUrl: croppedDataUrl,
                name: 'producto_' + cropCounter
            });
            
            cropCounter++;
            updatePreview();
        }

        function updatePreview() {
            const previewDiv = document.getElementById('crops-preview');
            const previewCard = document.getElementById('previews-card');
            const countSpan = document.getElementById('crop-count');
            
            previewCard.style.display = 'block';
            countSpan.textContent = crops.length;
            
            previewDiv.innerHTML = crops.map(crop => `
                <div class="crop-preview" id="crop-${crop.id}">
                    <img src="${crop.dataUrl}" alt="${crop.name}">
                    <input type="text" 
                           class="form-control form-control-sm mt-2" 
                           value="${crop.name}"
                           onchange="updateCropName(${crop.id}, this.value)"
                           placeholder="Nombre del producto">
                    <button class="btn btn-sm btn-danger mt-1 w-100" onclick="deleteCrop(${crop.id})">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            `).join('');
        }

        function updateCropName(id, newName) {
            const crop = crops.find(c => c.id === id);
            if (crop) crop.name = newName;
        }

        function deleteCrop(id) {
            crops = crops.filter(c => c.id !== id);
            updatePreview();
        }

        function clearAllCrops() {
            if (confirm('¿Borrar todas las imágenes recortadas?')) {
                crops = [];
                updatePreview();
            }
        }

        function undoLastCrop() {
            if (crops.length > 0) {
                crops.pop();
                updatePreview();
            }
        }

        function resetSelection() {
            document.getElementById('selection-box').style.display = 'none';
            isSelecting = false;
        }

        async function downloadAll() {
            if (crops.length === 0) {
                alert('No hay imágenes para descargar');
                return;
            }

            const zip = new JSZip();
            const folder = zip.folder('productos');

            crops.forEach(crop => {
                const base64Data = crop.dataUrl.split(',')[1];
                folder.file(crop.name + '.jpg', base64Data, {base64: true});
            });

            const content = await zip.generateAsync({type: 'blob'});
            const url = URL.createObjectURL(content);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'productos_recortados.zip';
            a.click();
            
            alert(`✅ Se descargaron ${crops.length} imágenes en productos_recortados.zip\n\nAhora:\n1. Descomprimí el ZIP\n2. Andá a "Importar Imágenes → Desde Carpeta"\n3. Subí todas las imágenes`);
        }
    </script>
</body>
</html>
