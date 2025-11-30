<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

try {
    // Consultar productos activos con stock
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nombre,
            descripcion,
            precio,
            stock,
            categoria,
            imagen
        FROM productos 
        WHERE activo = 1
        ORDER BY nombre ASC
    ");
    
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Asegurar que las imágenes tengan URL completa
    foreach ($productos as &$producto) {
        if ($producto['imagen'] && !filter_var($producto['imagen'], FILTER_VALIDATE_URL)) {
            // Si no es una URL completa, agregar el path del servidor
            $producto['imagen'] = 'uploads/' . basename($producto['imagen']);
        }
    }
    
    echo json_encode($productos);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al cargar productos: ' . $e->getMessage()
    ]);
}
?>
