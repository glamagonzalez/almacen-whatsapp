<?php
/**
 * GET_PRODUCTO.PHP - Obtener datos de un producto específico
 */
header('Content-Type: application/json');
require_once '../config/database.php';

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("
        SELECT p.*, a.ruta as imagen_ruta
        FROM productos p
        LEFT JOIN archivos a ON p.archivo_id = a.id
        WHERE p.id = ?
    ");
    
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    
    if ($producto) {
        echo json_encode([
            'success' => true,
            'producto' => $producto
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Producto no encontrado'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
