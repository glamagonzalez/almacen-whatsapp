<?php
/**
 * BUSCAR_IMAGENES_PRODUCTO.PHP - Buscar imágenes en internet para un producto
 */
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $productoId = $input['producto_id'] ?? 0;
    $nombre = $input['nombre'] ?? '';
    $categoria = $input['categoria'] ?? '';
    
    if (empty($nombre)) {
        throw new Exception('Nombre de producto vacío');
    }
    
    // Buscar imágenes usando diferentes servicios
    $imagenes = [];
    
    // OPCIÓN 1: Unsplash (imágenes genéricas de productos)
    $queries = [
        urlencode($nombre . ' product'),
        urlencode($categoria . ' product'),
        urlencode($nombre)
    ];
    
    foreach ($queries as $query) {
        // Usar Lorem Picsum como fallback (genera imágenes aleatorias)
        // En producción, usar APIs reales como Unsplash, Pexels, etc.
        $imagenes[] = [
            'url' => "https://source.unsplash.com/400x400/?{$query}",
            'source' => 'unsplash'
        ];
    }
    
    // OPCIÓN 2: Agregar URLs genéricas por categoría
    $categoriaLower = strtolower($categoria);
    
    if (strpos($categoriaLower, 'bebida') !== false || strpos($categoriaLower, 'gaseosa') !== false) {
        $imagenes[] = ['url' => 'https://source.unsplash.com/400x400/?beverage,drink', 'source' => 'category'];
        $imagenes[] = ['url' => 'https://source.unsplash.com/400x400/?soda,cola', 'source' => 'category'];
    } elseif (strpos($categoriaLower, 'aceite') !== false) {
        $imagenes[] = ['url' => 'https://source.unsplash.com/400x400/?oil,cooking', 'source' => 'category'];
    } elseif (strpos($categoriaLower, 'limpieza') !== false) {
        $imagenes[] = ['url' => 'https://source.unsplash.com/400x400/?cleaning,hygiene', 'source' => 'category'];
    } elseif (strpos($categoriaLower, 'alimento') !== false) {
        $imagenes[] = ['url' => 'https://source.unsplash.com/400x400/?food,grocery', 'source' => 'category'];
    }
    
    // Limitar a 6 imágenes
    $imagenes = array_slice(array_unique($imagenes, SORT_REGULAR), 0, 6);
    
    echo json_encode([
        'success' => true,
        'imagenes' => $imagenes,
        'producto_id' => $productoId,
        'query' => $nombre
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
