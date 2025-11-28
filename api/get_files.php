<?php
/**
 * GET_FILES.PHP - Obtener lista de archivos
 * 
 * Este endpoint devuelve todos los archivos de la base de datos
 * ordenados por fecha (más recientes primero)
 */

header('Content-Type: application/json');
require_once '../config/database.php';

try {
    // Consultar archivos ordenados por fecha descendente
    $stmt = $pdo->query("
        SELECT 
            id,
            nombre_original,
            nombre_archivo,
            tipo,
            tamanio,
            ruta,
            fecha_subida,
            enviado_whatsapp,
            numero_whatsapp
        FROM archivos 
        ORDER BY fecha_subida DESC 
        LIMIT 50
    ");
    
    $files = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'files' => $files,
        'count' => count($files)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener archivos: ' . $e->getMessage()
    ]);
}
?>
