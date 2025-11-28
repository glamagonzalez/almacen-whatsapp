<?php
/**
 * DESCARGAR_ASIGNAR_IMAGEN.PHP - Descargar imagen de URL y asignarla a producto
 */
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $productoId = $input['producto_id'] ?? 0;
    $imageUrl = $input['image_url'] ?? '';
    
    if (!$productoId || !$imageUrl) {
        throw new Exception('Datos incompletos');
    }
    
    // Verificar que el producto existe
    $stmt = $pdo->prepare("SELECT id, nombre FROM productos WHERE id = ?");
    $stmt->execute([$productoId]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        throw new Exception('Producto no encontrado');
    }
    
    // Descargar imagen
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Usar cURL para descargar con headers
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $imageContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$imageContent) {
        throw new Exception('No se pudo descargar la imagen');
    }
    
    // Generar nombre de archivo único
    $fileName = 'producto_' . $productoId . '_' . uniqid() . '.jpg';
    $filePath = $uploadDir . $fileName;
    
    // Guardar imagen
    if (!file_put_contents($filePath, $imageContent)) {
        throw new Exception('Error al guardar la imagen');
    }
    
    $fileSize = filesize($filePath);
    
    // Insertar en tabla archivos
    $stmt = $pdo->prepare("
        INSERT INTO archivos (nombre_original, nombre_archivo, tipo, tamanio, ruta)
        VALUES (?, ?, 'image/jpeg', ?, ?)
    ");
    
    $stmt->execute([
        $producto['nombre'] . '.jpg',
        $fileName,
        $fileSize,
        $filePath
    ]);
    
    $archivoId = $pdo->lastInsertId();
    
    // Asignar al producto
    $stmt = $pdo->prepare("UPDATE productos SET archivo_id = ? WHERE id = ?");
    $stmt->execute([$archivoId, $productoId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Imagen descargada y asignada correctamente',
        'archivo_id' => $archivoId,
        'producto_id' => $productoId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
