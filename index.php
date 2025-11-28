<?php
/**
 * ALMACÉN WHATSAPP - Página Principal
 * Sistema de gestión de archivos con integración WhatsApp
 */
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén WhatsApp - Gestor de Archivos</title>
    
    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" />
    
    <!-- Bootstrap para diseño -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .dropzone {
            border: 3px dashed #667eea;
            border-radius: 15px;
            background: white;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .dropzone:hover {
            border-color: #764ba2;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .dropzone .dz-message {
            font-size: 20px;
            color: #667eea;
            font-weight: 600;
        }
        .file-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        .file-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .header-title {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stats-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="header-title">
            <h1><i class="fas fa-box"></i> Almacén WhatsApp</h1>
            <p class="lead">Sistema de Gestión de Archivos y Documentos</p>
        </div>

        <!-- Menú de navegación -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                    <a href="productos.php" class="btn btn-success">
                        <i class="fas fa-box-open"></i> Productos
                    </a>
                    <a href="demo_cliente.php" class="btn btn-info" target="_blank">
                        <i class="fas fa-mobile-alt"></i> Vista Cliente
                    </a>
                    <a href="preview_mobile.php" class="btn btn-primary" target="_blank">
                        <i class="fas fa-eye"></i> Preview Móvil
                    </a>
                    <a href="catalogo.php" class="btn btn-success">
                        <i class="fas fa-shopping-cart"></i> Catálogo
                    </a>
                    <a href="buscar_imagenes_productos.php" class="btn btn-success">
                        <i class="fas fa-magic"></i> Buscar Imágenes Auto
                    </a>
                    <a href="recortador.php" class="btn btn-danger">
                        <i class="fas fa-crop"></i> Recortar Catálogo
                    </a>
                    <a href="importar_imagenes.php" class="btn btn-warning">
                        <i class="fas fa-file-image"></i> Importar Imágenes
                    </a>
                    <a href="gestionar_imagenes.php" class="btn btn-secondary">
                        <i class="fas fa-images"></i> Gestionar Imágenes
                    </a>
                    <a href="tutorial_imagenes.php" class="btn btn-outline-light">
                        <i class="fas fa-book-open"></i> Tutorial
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-file fa-2x text-primary mb-2"></i>
                    <div class="stats-number" id="totalFiles">0</div>
                    <small class="text-muted">Archivos Totales</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-image fa-2x text-success mb-2"></i>
                    <div class="stats-number" id="totalImages">0</div>
                    <small class="text-muted">Imágenes</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                    <div class="stats-number" id="totalDocs">0</div>
                    <small class="text-muted">Documentos</small>
                </div>
            </div>
        </div>

        <!-- Zona de carga -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="card-title mb-0"><i class="fas fa-cloud-upload-alt"></i> Subir Archivos</h3>
                    <a href="importar.php" class="btn btn-info">
                        <i class="fas fa-file-import"></i> Importar desde carpeta
                    </a>
                </div>
                <form action="upload.php" class="dropzone" id="myDropzone">
                    <div class="dz-message">
                        <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: #667eea;"></i>
                        <h4>Arrastra archivos aquí o haz clic para seleccionar</h4>
                        <p class="text-muted">Soporta: Imágenes, PDFs, Word, Excel, etc.</p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de archivos subidos -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-4"><i class="fas fa-list"></i> Archivos Recientes</h3>
                <div id="filesList"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
