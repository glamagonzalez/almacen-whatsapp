<?php
/**
 * PRODUCTOS_SIN_IMAGEN.PHP - Listar productos sin imagen
 */
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT id, nombre, categoria
        FROM productos
        WHERE activo = 1 AND (archivo_id IS NULL OR archivo_id = 0)
        ORDER BY nombre
    ");
    
    $productos = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'productos' => $productos,
        'cantidad' => count($productos)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
