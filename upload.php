<?php
/**
 * UPLOAD.PHP - Procesa la carga de archivos
 * 
 * Este archivo recibe los archivos desde Dropzone y los guarda
 * en el servidor, además los registra en la base de datos
 */

header('Content-Type: application/json');

// Incluir conexión a base de datos
require_once 'config/database.php';

// Crear carpeta de uploads si no existe
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Verificar que se recibió un archivo
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Información del archivo
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];
    
    // Verificar errores
    if ($fileError === 0) {
        // Generar nombre único para el archivo
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid('file_', true) . '.' . $fileExt;
        $fileDestination = $uploadDir . $newFileName;
        
        // Mover archivo a la carpeta de uploads
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            
            // Guardar en base de datos
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO archivos (nombre_original, nombre_archivo, tipo, tamanio, ruta, fecha_subida)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $fileName,
                    $newFileName,
                    $fileType,
                    $fileSize,
                    $fileDestination
                ]);
                
                $fileId = $pdo->lastInsertId();
                
                // Respuesta exitosa
                echo json_encode([
                    'success' => true,
                    'message' => 'Archivo subido correctamente',
                    'file' => [
                        'id' => $fileId,
                        'name' => $fileName,
                        'size' => $fileSize,
                        'type' => $fileType,
                        'path' => $fileDestination
                    ]
                ]);
                
            } catch (PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al guardar en base de datos: ' . $e->getMessage()
                ]);
            }
            
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al mover el archivo'
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error en la carga del archivo'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No se recibió ningún archivo'
    ]);
}
?>
