<?php
/**
 * ASIGNAR_IMAGEN.PHP - Vincular imagen con producto
 */
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $productoId = $data['producto_id'] ?? 0;
    $archivoId = $data['archivo_id'] ?? null;
    
    if (!$productoId) {
        throw new Exception('ID de producto no válido');
    }
    
    // Actualizar producto con la imagen
    $stmt = $pdo->prepare("
        UPDATE productos 
        SET archivo_id = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$archivoId, $productoId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Imagen asignada correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
