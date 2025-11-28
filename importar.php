<?php
/**
 * IMPORTAR.PHP - Importar archivos existentes al sistema
 * 
 * Este script permite importar archivos que ya tienes en otras carpetas
 * sin necesidad de subirlos manualmente uno por uno
 */

require_once 'config/database.php';

// ==========================================
// CONFIGURACIÓN: Cambia esta ruta a tu carpeta
// ==========================================
$carpetaOrigen = 'C:/xampp/htdocs/maxi-consumo/'; // 👈 CAMBIA ESTO

// Crear carpeta uploads si no existe
$carpetaDestino = 'uploads/';
if (!file_exists($carpetaDestino)) {
    mkdir($carpetaDestino, 0777, true);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Archivos - Almacén WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .file-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <i class="fas fa-file-import"></i> Importar Archivos Existentes
                </h2>
                
                <!-- Formulario de configuración -->
                <form method="POST" action="" class="mb-4">
                    <div class="mb-3">
                        <label class="form-label"><strong>📁 Carpeta de origen:</strong></label>
                        <input type="text" name="carpeta" class="form-control" 
                               value="<?php echo htmlspecialchars($carpetaOrigen); ?>"
                               placeholder="C:/xampp/htdocs/maxi-consumo/">
                        <small class="text-muted">Ruta donde están tus archivos de productos</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>⚙️ Opciones:</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="modo" value="copiar" id="copiar" checked>
                            <label class="form-check-label" for="copiar">
                                📋 Copiar archivos (duplica las imágenes)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="modo" value="vincular" id="vincular">
                            <label class="form-check-label" for="vincular">
                                🔗 Solo vincular (usa las imágenes donde están)
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" name="importar" class="btn btn-primary btn-lg">
                        <i class="fas fa-download"></i> Importar Archivos
                    </button>
                    <a href="index.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </form>

                <hr>

                <?php
                // ==========================================
                // PROCESAR IMPORTACIÓN
                // ==========================================
                if (isset($_POST['importar'])) {
                    $carpeta = $_POST['carpeta'];
                    $modo = $_POST['modo'];
                    
                    // Validar que la carpeta existe
                    if (!is_dir($carpeta)) {
                        echo '<div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                La carpeta no existe: ' . htmlspecialchars($carpeta) . '
                              </div>';
                    } else {
                        echo '<h4 class="mb-3">📦 Procesando archivos...</h4>';
                        
                        // Extensiones permitidas
                        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
                        
                        // Leer archivos de la carpeta
                        $archivos = scandir($carpeta);
                        $importados = 0;
                        $errores = 0;
                        
                        echo '<div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Preview</th>
                                            <th>Archivo</th>
                                            <th>Tamaño</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                        
                        foreach ($archivos as $archivo) {
                            // Saltar . y ..
                            if ($archivo == '.' || $archivo == '..') continue;
                            
                            $rutaCompleta = $carpeta . $archivo;
                            
                            // Verificar que es un archivo
                            if (!is_file($rutaCompleta)) continue;
                            
                            // Obtener extensión
                            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                            
                            // Verificar extensión permitida
                            if (!in_array($extension, $extensionesPermitidas)) {
                                continue;
                            }
                            
                            // Obtener información del archivo
                            $tamano = filesize($rutaCompleta);
                            $tipo = mime_content_type($rutaCompleta);
                            
                            // Generar nombre único
                            $nombreNuevo = uniqid('import_', true) . '.' . $extension;
                            
                            try {
                                if ($modo == 'copiar') {
                                    // COPIAR archivo
                                    $rutaDestino = $carpetaDestino . $nombreNuevo;
                                    copy($rutaCompleta, $rutaDestino);
                                    $rutaGuardar = $rutaDestino;
                                } else {
                                    // VINCULAR (guardar ruta original)
                                    $rutaGuardar = $rutaCompleta;
                                }
                                
                                // Guardar en base de datos
                                $stmt = $pdo->prepare("
                                    INSERT INTO archivos (nombre_original, nombre_archivo, tipo, tamanio, ruta)
                                    VALUES (?, ?, ?, ?, ?)
                                ");
                                
                                $stmt->execute([
                                    $archivo,
                                    $nombreNuevo,
                                    $tipo,
                                    $tamano,
                                    $rutaGuardar
                                ]);
                                
                                // Mostrar resultado
                                $preview = '';
                                if (strpos($tipo, 'image') !== false) {
                                    $previewPath = ($modo == 'copiar') ? $rutaDestino : $rutaCompleta;
                                    $preview = '<img src="' . $previewPath . '" class="file-preview">';
                                } else {
                                    $preview = '<i class="fas fa-file fa-3x text-secondary"></i>';
                                }
                                
                                echo '<tr>
                                        <td>' . $preview . '</td>
                                        <td><strong>' . htmlspecialchars($archivo) . '</strong></td>
                                        <td>' . formatBytes($tamano) . '</td>
                                        <td><span class="badge bg-success">✅ Importado</span></td>
                                      </tr>';
                                
                                $importados++;
                                
                            } catch (Exception $e) {
                                echo '<tr>
                                        <td>-</td>
                                        <td>' . htmlspecialchars($archivo) . '</td>
                                        <td>' . formatBytes($tamano) . '</td>
                                        <td><span class="badge bg-danger">❌ Error</span></td>
                                      </tr>';
                                $errores++;
                            }
                        }
                        
                        echo '</tbody></table></div>';
                        
                        // Resumen
                        echo '<div class="alert alert-info mt-4">
                                <h5>📊 Resumen de Importación</h5>
                                <p class="mb-0">
                                    ✅ <strong>' . $importados . '</strong> archivos importados correctamente<br>
                                    ❌ <strong>' . $errores . '</strong> errores
                                </p>
                              </div>';
                    }
                }
                
                // Función auxiliar para formatear bytes
                function formatBytes($bytes) {
                    if ($bytes === 0) return '0 Bytes';
                    $k = 1024;
                    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    $i = floor(log($bytes) / log($k));
                    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
