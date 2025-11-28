<?php
/**
 * SUBIR_IMAGENES_MASIVO.PHP - Subir múltiples imágenes a la vez
 */
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    if (!isset($_FILES['imagenes'])) {
        throw new Exception('No se recibieron imágenes');
    }
    
    $files = $_FILES['imagenes'];
    $uploadDir = '../uploads/';
    
    // Crear carpeta si no existe
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $imagenesSubidas = [];
    $errores = [];
    
    // Procesar cada archivo
    $totalFiles = count($files['name']);
    
    for ($i = 0; $i < $totalFiles; $i++) {
        // Verificar si hay error en este archivo
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $errores[] = $files['name'][$i] . ': Error al subir';
            continue;
        }
        
        $fileName = $files['name'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];
        $fileType = $files['type'][$i];
        
        // Validar que sea imagen
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($fileType, $allowedTypes)) {
            $errores[] = $fileName . ': No es una imagen válida';
            continue;
        }
        
        // Validar tamaño (máx 10MB)
        if ($fileSize > 10 * 1024 * 1024) {
            $errores[] = $fileName . ': Muy grande (máx 10MB)';
            continue;
        }
        
        // Generar nombre único
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = 'img_' . uniqid() . '.' . $extension;
        $fileDestination = $uploadDir . $newFileName;
        
        // Mover archivo
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            // Guardar en base de datos
            $stmt = $pdo->prepare("
                INSERT INTO archivos (nombre_original, nombre_archivo, tipo, tamanio, ruta)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $fileName,
                $newFileName,
                $fileType,
                $fileSize,
                $fileDestination
            ]);
            
            $imagenesSubidas[] = [
                'id' => $pdo->lastInsertId(),
                'nombre' => $fileName,
                'ruta' => $fileDestination
            ];
        } else {
            $errores[] = $fileName . ': Error al guardar';
        }
    }
    
    if (count($imagenesSubidas) > 0) {
        echo json_encode([
            'success' => true,
            'cantidad' => count($imagenesSubidas),
            'imagenes' => $imagenesSubidas,
            'errores' => $errores,
            'message' => count($imagenesSubidas) . ' imágenes subidas correctamente'
        ]);
    } else {
        throw new Exception('No se pudo subir ninguna imagen. Errores: ' . implode(', ', $errores));
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
