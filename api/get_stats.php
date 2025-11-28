<?php
/**
 * GET_STATS.PHP - Obtener estadísticas
 * 
 * Devuelve contadores de archivos por tipo
 */

header('Content-Type: application/json');
require_once '../config/database.php';

try {
    // Total de archivos
    $totalStmt = $pdo->query("SELECT COUNT(*) as total FROM archivos");
    $total = $totalStmt->fetch()['total'];
    
    // Total de imágenes
    $imagesStmt = $pdo->query("SELECT COUNT(*) as total FROM archivos WHERE tipo LIKE 'image/%'");
    $images = $imagesStmt->fetch()['total'];
    
    // Total de documentos (PDF, Word, Excel)
    $docsStmt = $pdo->query("
        SELECT COUNT(*) as total FROM archivos 
        WHERE tipo LIKE '%pdf%' 
        OR tipo LIKE '%word%' 
        OR tipo LIKE '%excel%'
        OR tipo LIKE '%document%'
    ");
    $documents = $docsStmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total' => $total,
            'images' => $images,
            'documents' => $documents
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
    ]);
}
?>
