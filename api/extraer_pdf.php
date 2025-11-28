<?php
/**
 * EXTRAER_PDF.PHP - Extraer imágenes de un PDF
 * 
 * OPCIÓN 1: Usando ImageMagick (necesita estar instalado)
 * OPCIÓN 2: Usando pdfimages de Poppler (alternativa)
 * OPCIÓN 3: Manual - Indicar al usuario cómo hacerlo
 */
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    if (!isset($_FILES['pdf_file'])) {
        throw new Exception('No se recibió el archivo PDF');
    }
    
    $file = $_FILES['pdf_file'];
    
    // Validar que sea PDF
    if ($file['type'] !== 'application/pdf' && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'pdf') {
        throw new Exception('El archivo debe ser un PDF');
    }
    
    $uploadDir = '../uploads/';
    $tempDir = '../temp/';
    
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
    
    // Guardar PDF temporal
    $pdfPath = $tempDir . 'ofertas_' . uniqid() . '.pdf';
    move_uploaded_file($file['tmp_name'], $pdfPath);
    
    $imagenesExtraidas = [];
    
    // MÉTODO 1: Intentar con ImageMagick
    if (extension_loaded('imagick')) {
        try {
            $imagick = new Imagick();
            $imagick->setResolution(300, 300);
            $imagick->readImage($pdfPath);
            
            $numPages = $imagick->getNumberImages();
            
            foreach (range(0, $numPages - 1) as $page) {
                $imagick->setIteratorIndex($page);
                
                $fileName = 'pdf_page_' . $page . '_' . uniqid() . '.jpg';
                $filePath = $uploadDir . $fileName;
                
                $imagick->setImageFormat('jpg');
                $imagick->setImageCompressionQuality(85);
                $imagick->writeImage($filePath);
                
                // Guardar en BD
                $stmt = $pdo->prepare("
                    INSERT INTO archivos (nombre_original, nombre_archivo, tipo, tamanio, ruta)
                    VALUES (?, ?, 'image/jpeg', ?, ?)
                ");
                
                $fileSize = filesize($filePath);
                $stmt->execute([
                    'Página ' . ($page + 1) . ' - ' . $file['name'],
                    $fileName,
                    $fileSize,
                    $filePath
                ]);
                
                $imagenesExtraidas[] = [
                    'id' => $pdo->lastInsertId(),
                    'nombre' => $fileName,
                    'ruta' => $filePath
                ];
            }
            
            $imagick->clear();
            $imagick->destroy();
            
        } catch (Exception $e) {
            throw new Exception('Error con ImageMagick: ' . $e->getMessage());
        }
    }
    // MÉTODO 2: Intentar con pdfimages (requiere Poppler instalado)
    else if (shell_exec('which pdfimages') || shell_exec('where pdfimages')) {
        $outputPrefix = $tempDir . 'img_' . uniqid();
        shell_exec("pdfimages -j '{$pdfPath}' '{$outputPrefix}'");
        
        // Buscar imágenes extraídas
        $extractedFiles = glob($outputPrefix . '*');
        
        foreach ($extractedFiles as $extractedFile) {
            $fileName = 'pdf_' . uniqid() . '.' . pathinfo($extractedFile, PATHINFO_EXTENSION);
            $filePath = $uploadDir . $fileName;
            
            rename($extractedFile, $filePath);
            
            // Guardar en BD
            $stmt = $pdo->prepare("
                INSERT INTO archivos (nombre_original, nombre_archivo, tipo, tamanio, ruta)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $fileSize = filesize($filePath);
            $mimeType = mime_content_type($filePath);
            
            $stmt->execute([
                basename($extractedFile),
                $fileName,
                $mimeType,
                $fileSize,
                $filePath
            ]);
            
            $imagenesExtraidas[] = [
                'id' => $pdo->lastInsertId(),
                'nombre' => $fileName,
                'ruta' => $filePath
            ];
        }
    }
    // MÉTODO 3: No hay herramientas disponibles
    else {
        throw new Exception('
            No hay herramientas para extraer imágenes de PDF instaladas.
            
            SOLUCIONES:
            1. Instalar ImageMagick: https://imagemagick.org/script/download.php
            2. Instalar Poppler: https://github.com/oschwartz10612/poppler-windows/releases/
            3. Usar método manual:
               - Abrí el PDF con Adobe Reader
               - Click derecho en cada imagen → "Copiar imagen"
               - Pegalas en Paint o Photoshop
               - Guardalas en una carpeta
               - Usá el método "Desde Carpeta" para subirlas
        ');
    }
    
    // Limpiar PDF temporal
    unlink($pdfPath);
    
    if (count($imagenesExtraidas) > 0) {
        echo json_encode([
            'success' => true,
            'cantidad' => count($imagenesExtraidas),
            'imagenes' => $imagenesExtraidas,
            'message' => count($imagenesExtraidas) . ' imágenes extraídas del PDF'
        ]);
    } else {
        throw new Exception('No se encontraron imágenes en el PDF');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
