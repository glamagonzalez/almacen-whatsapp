<?php
/**
 * GUARDAR_PRODUCTO.PHP - API para crear/editar productos
 */
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    // Obtener datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $codigo_barras = $_POST['codigo_barras'] ?? null;
    $categoria = $_POST['categoria'] ?? '';
    $descripcion = $_POST['descripcion'] ?? null;
    $precio_compra = $_POST['precio_compra'] ?? 0;
    $margen_porcentaje = $_POST['margen_porcentaje'] ?? 30;
    $precio_venta = $_POST['precio_venta'] ?? 0;
    $stock_actual = $_POST['stock_actual'] ?? 0;
    $stock_minimo = $_POST['stock_minimo'] ?? 5;
    $proveedor = $_POST['proveedor'] ?? 'Maxi Consumo';
    
    // Validaciones básicas
    if (empty($nombre) || empty($categoria)) {
        throw new Exception('Nombre y categoría son obligatorios');
    }
    
    // Manejar imagen si se subió
    $archivo_id = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $uploadDir = '../uploads/';
        $file = $_FILES['imagen'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid('producto_', true) . '.' . $fileExt;
        $fileDestination = $uploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $fileDestination)) {
            // Guardar en tabla archivos
            $stmtArchivo = $pdo->prepare("
                INSERT INTO archivos (nombre_original, nombre_archivo, tipo, tamanio, ruta)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtArchivo->execute([
                $file['name'],
                $newFileName,
                $file['type'],
                $file['size'],
                $fileDestination
            ]);
            $archivo_id = $pdo->lastInsertId();
        }
    }
    
    // Insertar producto
    $stmt = $pdo->prepare("
        INSERT INTO productos (
            nombre, codigo_barras, categoria, descripcion,
            precio_compra, margen_porcentaje, precio_venta,
            stock_actual, stock_minimo, proveedor, archivo_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $nombre,
        $codigo_barras,
        $categoria,
        $descripcion,
        $precio_compra,
        $margen_porcentaje,
        $precio_venta,
        $stock_actual,
        $stock_minimo,
        $proveedor,
        $archivo_id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto guardado correctamente',
        'id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
