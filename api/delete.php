<?php
/**
 * DELETE.PHP - Eliminar archivo
 * 
 * Elimina el archivo del servidor y de la base de datos
 */

header('Content-Type: application/json');
require_once '../config/database.php';

// Obtener datos JSON
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $fileId = $data['id'];
    
    try {
        // Obtener información del archivo
        $stmt = $pdo->prepare("SELECT ruta FROM archivos WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();
        
        if ($file) {
            // Eliminar archivo físico del servidor
            if (file_exists('../' . $file['ruta'])) {
                unlink('../' . $file['ruta']);
            }
            
            // Eliminar registro de base de datos
            $deleteStmt = $pdo->prepare("DELETE FROM archivos WHERE id = ?");
            $deleteStmt->execute([$fileId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Archivo eliminado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Archivo no encontrado'
            ]);
        }
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de archivo no proporcionado'
    ]);
}
?>
