<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial: Extraer Imágenes del PDF de Ofertas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            padding: 30px;
        }
        .tutorial-step {
            background: white;
            border-left: 5px solid #007bff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .step-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            margin-right: 15px;
        }
        .code-box {
            background: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .btn-copy {
            margin-top: 10px;
        }
        img.screenshot {
            max-width: 100%;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div class="text-center mb-5">
            <h1><i class="fas fa-book-open"></i> Tutorial: Extraer Imágenes de Ofertas de Maxi Consumo</h1>
            <p class="lead">3 métodos fáciles para obtener las imágenes de productos</p>
        </div>

        <!-- MÉTODO 1: Adobe Reader (El más fácil) -->
        <div class="tutorial-step">
            <h3>
                <span class="step-number">1</span>
                Método Adobe Reader / Foxit (RECOMENDADO)
            </h3>
            <p><strong>Lo más fácil:</strong> Copiar imagen por imagen</p>
            
            <ol>
                <li><strong>Abrí el PDF</strong> de ofertas con Adobe Reader o Foxit Reader</li>
                <li><strong>Click derecho</strong> sobre cada imagen de producto</li>
                <li>Seleccioná <strong>"Copiar imagen"</strong> o <strong>"Copy Image"</strong></li>
                <li>Abrí <strong>Paint</strong> (Win + R → escribe "mspaint")</li>
                <li><strong>Pegar</strong> la imagen (Ctrl + V)</li>
                <li><strong>Guardar</strong> con el nombre del producto (ejemplo: coca_cola.jpg)</li>
                <li>Repetir para cada producto</li>
            </ol>

            <div class="alert alert-info">
                <i class="fas fa-lightbulb"></i>
                <strong>Tip:</strong> Guardá todas las imágenes en una carpeta (ejemplo: "imagenes_productos").
                Después vas a <a href="importar_imagenes.php">Importar Imágenes</a> y elegís "Desde Carpeta".
            </div>
        </div>

        <!-- MÉTODO 2: Snipping Tool (Más rápido) -->
        <div class="tutorial-step">
            <h3>
                <span class="step-number">2</span>
                Método Snipping Tool / Recortes (RÁPIDO)
            </h3>
            <p><strong>Capturas de pantalla</strong> de cada producto</p>
            
            <ol>
                <li>Abrí el PDF de ofertas en pantalla completa</li>
                <li>Presioná <strong>Win + Shift + S</strong> (Windows 10/11)</li>
                <li>Recortá la imagen del producto</li>
                <li>Se copia automáticamente al portapapeles</li>
                <li>Abrí Paint y pegá (Ctrl + V)</li>
                <li>Guardá con el nombre del producto</li>
            </ol>

            <div class="code-box">
                <strong>Atajos útiles:</strong><br>
                Win + Shift + S → Herramienta de recorte<br>
                Alt + Tab → Cambiar entre ventanas<br>
                Ctrl + V → Pegar en Paint
            </div>
        </div>

        <!-- MÉTODO 3: Extraer desde PDF (Automático pero necesita instalar) -->
        <div class="tutorial-step">
            <h3>
                <span class="step-number">3</span>
                Método Automático (Necesita instalación)
            </h3>
            <p><strong>Extraer todas las imágenes</strong> del PDF de una vez</p>
            
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Este método requiere instalar software adicional
            </div>

            <h5>Opción A: PDF-XChange Editor (Gratis)</h5>
            <ol>
                <li>Descargá <a href="https://www.tracker-software.com/product/pdf-xchange-editor" target="_blank">PDF-XChange Editor</a></li>
                <li>Instalalo</li>
                <li>Abrí el PDF de ofertas</li>
                <li>Menú: <strong>Tools → Export Images</strong></li>
                <li>Elegí carpeta de destino</li>
                <li>Exportar todas las imágenes</li>
            </ol>

            <h5 class="mt-4">Opción B: Online (Sin instalar nada)</h5>
            <ol>
                <li>Andá a <a href="https://www.ilovepdf.com/es/extraer-imagenes-pdf" target="_blank">iLovePDF - Extraer Imágenes</a></li>
                <li>Subí el PDF de ofertas</li>
                <li>Click en "Extraer imágenes"</li>
                <li>Descargá el ZIP con todas las imágenes</li>
                <li>Descomprimí y renombrá las imágenes con nombres de productos</li>
            </ol>
        </div>

        <!-- MÉTODO 4: Usar el sistema que ya creé -->
        <div class="tutorial-step" style="border-left-color: #28a745;">
            <h3>
                <span class="step-number" style="background: #28a745;">4</span>
                Después de obtener las imágenes
            </h3>
            <p><strong>Una vez que tengas las imágenes guardadas</strong>, seguí estos pasos:</p>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                            <h5>Si están en una carpeta</h5>
                            <p>Usá el importador masivo</p>
                            <a href="importar_imagenes.php" class="btn btn-primary">
                                <i class="fas fa-folder"></i> Importar desde Carpeta
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-images fa-3x text-success mb-3"></i>
                            <h5>Si ya están en el sistema</h5>
                            <p>Asignalas a cada producto</p>
                            <a href="gestionar_imagenes.php" class="btn btn-success">
                                <i class="fas fa-link"></i> Asignar Imágenes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONSEJOS EXTRA -->
        <div class="alert alert-success mt-4">
            <h5><i class="fas fa-star"></i> Consejos para mejores resultados:</h5>
            <ul>
                <li><strong>Nombrá las imágenes</strong> igual que los productos en tu sistema (coca_cola.jpg, arroz_gallo_oro.jpg)</li>
                <li><strong>Tamaño recomendado:</strong> 400x400 píxeles o más</li>
                <li><strong>Formato:</strong> JPG o PNG (JPG es más liviano)</li>
                <li><strong>Calidad:</strong> Tratá que se vea bien el producto</li>
                <li><strong>Fondo:</strong> Blanco es ideal para catálogos</li>
            </ul>
        </div>

        <!-- BOTONES DE ACCIÓN -->
        <div class="text-center mt-5">
            <a href="importar_imagenes.php" class="btn btn-primary btn-lg">
                <i class="fas fa-upload"></i> Ir a Importar Imágenes
            </a>
            <a href="gestionar_imagenes.php" class="btn btn-success btn-lg">
                <i class="fas fa-images"></i> Gestionar Imágenes
            </a>
            <a href="index.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
