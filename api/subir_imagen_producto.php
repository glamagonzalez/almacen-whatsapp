<?php
/**
 * SUBIR_IMAGEN_PRODUCTO.PHP - Subir y asignar imagen a producto
 */
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $productoId = $_POST['producto_id'] ?? 0;
    
    if (!$productoId) {
        throw new Exception('ID de producto no válido');
    }
    
    if (!isset($_FILES['imagen'])) {
        throw new Exception('No se recibió ninguna imagen');
    }
    
    $file = $_FILES['imagen'];
    
    // Validar que sea una imagen
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Solo se permiten imágenes (JPG, PNG, GIF, WEBP)');
    }
    
    // Validar tamaño (máx 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('La imagen es muy grande (máx 5MB)');
    }
    
    // Crear carpeta uploads si no existe
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generar nombre único
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = 'producto_' . $productoId . '_' . uniqid() . '.' . $extension;
    $fileDestination = $uploadDir . $newFileName;
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $fileDestination)) {
        throw new Exception('Error al guardar la imagen');
    }
    
    // Guardar en tabla archivos
    $stmt = $pdo->prepare("
        INSERT INTO archivos (nombre_original, nombre_archivo, tipo, tamanio, ruta)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $file['name'],
        $newFileName,
        $file['type'],
        $file['size'],
        $fileDestination
    ]);
    
    $archivoId = $pdo->lastInsertId();
    
    // Asignar al producto
    $stmtUpdate = $pdo->prepare("
        UPDATE productos 
        SET archivo_id = ?
        WHERE id = ?
    ");
    
    $stmtUpdate->execute([$archivoId, $productoId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Imagen subida y asignada correctamente',
        'archivo_id' => $archivoId,
        'ruta' => $fileDestination
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
