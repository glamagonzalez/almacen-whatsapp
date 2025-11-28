<?php
/**
 * DOWNLOAD.PHP - Descargar archivo
 * 
 * Permite descargar archivos del servidor
 */

require_once '../config/database.php';

if (isset($_GET['id'])) {
    $fileId = $_GET['id'];
    
    try {
        // Obtener información del archivo
        $stmt = $pdo->prepare("SELECT * FROM archivos WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();
        
        if ($file) {
            $filePath = '../' . $file['ruta'];
            
            if (file_exists($filePath)) {
                // Configurar headers para descarga
                header('Content-Description: File Transfer');
                header('Content-Type: ' . $file['tipo']);
                header('Content-Disposition: attachment; filename="' . $file['nombre_original'] . '"');
                header('Content-Length: ' . $file['tamanio']);
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                
                // Limpiar buffer
                ob_clean();
                flush();
                
                // Leer y enviar archivo
                readfile($filePath);
                exit;
            } else {
                echo "Archivo no encontrado en el servidor";
            }
        } else {
            echo "Archivo no encontrado en la base de datos";
        }
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    
} else {
    echo "ID de archivo no proporcionado";
}
?>
