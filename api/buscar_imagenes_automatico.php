<?php
/**
 * BUSCAR_IMAGENES_AUTOMATICO.PHP - Buscar imágenes en internet por nombre de producto
 */
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    // Obtener productos sin imagen
    $stmt = $pdo->query("
        SELECT id, nombre, categoria
        FROM productos
        WHERE activo = 1 AND (archivo_id IS NULL OR archivo_id = 0)
        ORDER BY nombre
        LIMIT 20
    ");
    
    $productos = $stmt->fetchAll();
    
    if (empty($productos)) {
        echo json_encode([
            'success' => true,
            'cantidad' => 0,
            'message' => 'Todos los productos ya tienen imagen'
        ]);
        exit;
    }
    
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $imagenesDescargadas = 0;
    
    foreach ($productos as $producto) {
        // Buscar imagen en servicios públicos (Unsplash API gratuita)
        $query = urlencode($producto['nombre']);
        
        // Usar API de Lorem Picsum como ejemplo (cambiar por API real si querés)
        // Para producción, usar Unsplash API, Pexels API, o Google Images API
        $imageUrl = "https://source.unsplash.com/400x400/?product,{$query}";
        
        // Descargar imagen
        $imageContent = @file_get_contents($imageUrl);
        
        if ($imageContent !== false) {
            $fileName = 'auto_' . uniqid() . '_' . $producto['id'] . '.jpg';
            $filePath = $uploadDir . $fileName;
            
            file_put_contents($filePath, $imageContent);
            
            // Guardar en base de datos
            $stmt = $pdo->prepare("
                INSERT INTO archivos (nombre_original, nombre_archivo, tipo, tamanio, ruta)
                VALUES (?, ?, 'image/jpeg', ?, ?)
            ");
            
            $stmt->execute([
                $producto['nombre'] . '.jpg',
                $fileName,
                strlen($imageContent),
                $filePath
            ]);
            
            $archivoId = $pdo->lastInsertId();
            
            // Asignar al producto
            $stmt = $pdo->prepare("UPDATE productos SET archivo_id = ? WHERE id = ?");
            $stmt->execute([$archivoId, $producto['id']]);
            
            $imagenesDescargadas++;
            
            // Esperar un poco para no saturar el servicio
            usleep(500000); // 0.5 segundos
        }
    }
    
    echo json_encode([
        'success' => true,
        'cantidad' => $imagenesDescargadas,
        'message' => "Se descargaron {$imagenesDescargadas} imágenes automáticamente"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
