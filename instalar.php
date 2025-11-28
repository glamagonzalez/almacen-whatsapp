<?php
/**
 * INSTALAR.PHP - Actualizar base de datos
 * Ejecuta este archivo UNA VEZ para crear la tabla de productos
 */
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Almacén WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px;
        }
        .card { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="card shadow-lg">
        <div class="card-body">
            <h2 class="card-title mb-4">🔧 Instalador de Base de Datos</h2>
            
            <?php
            if (isset($_POST['instalar'])) {
                echo '<div class="alert alert-info">Ejecutando instalación...</div>';
                
                try {
                    // Crear tabla productos
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS productos (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            codigo_barras VARCHAR(50) NULL COMMENT 'Código de barras del producto',
                            nombre VARCHAR(255) NOT NULL COMMENT 'Nombre del producto',
                            descripcion TEXT NULL COMMENT 'Descripción detallada',
                            categoria VARCHAR(100) NULL COMMENT 'Categoría (Alimentos, Bebidas, Limpieza, etc)',
                            
                            precio_compra DECIMAL(10,2) NOT NULL COMMENT 'Precio al que lo compraste',
                            margen_porcentaje DECIMAL(5,2) DEFAULT 30.00 COMMENT 'Margen de ganancia en %',
                            precio_venta DECIMAL(10,2) NOT NULL COMMENT 'Precio final de venta',
                            
                            stock_actual INT DEFAULT 0 COMMENT 'Cantidad disponible',
                            stock_minimo INT DEFAULT 5 COMMENT 'Alerta cuando llegue a este stock',
                            
                            archivo_id INT NULL COMMENT 'ID del archivo (imagen del producto)',
                            imagen_url VARCHAR(500) NULL COMMENT 'URL de la imagen',
                            
                            proveedor VARCHAR(100) DEFAULT 'Maxi Consumo' COMMENT 'De dónde lo compraste',
                            
                            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
                            fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            activo BOOLEAN DEFAULT TRUE,
                            
                            FOREIGN KEY (archivo_id) REFERENCES archivos(id) ON DELETE SET NULL,
                            INDEX idx_categoria (categoria),
                            INDEX idx_codigo (codigo_barras)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    
                    echo '<div class="alert alert-success">✅ Tabla "productos" creada correctamente</div>';
                    
                    // Insertar productos de ejemplo
                    $pdo->exec("
                        INSERT INTO productos (nombre, categoria, precio_compra, margen_porcentaje, precio_venta, stock_actual, proveedor) VALUES
                        ('Coca Cola 2.5L', 'Bebidas', 150.00, 30.00, 195.00, 24, 'Maxi Consumo'),
                        ('Arroz Largo Fino 1kg', 'Alimentos', 80.00, 35.00, 108.00, 50, 'Maxi Consumo'),
                        ('Detergente Magistral 500ml', 'Limpieza', 120.00, 25.00, 150.00, 15, 'Maxi Consumo')
                    ");
                    
                    echo '<div class="alert alert-success">✅ Productos de ejemplo insertados</div>';
                    echo '<div class="alert alert-info">
                            <h5>🎉 ¡Instalación completada!</h5>
                            <p class="mb-0">Ya puedes ir a <a href="productos.php" class="alert-link">Gestión de Productos</a></p>
                          </div>';
                    
                } catch (PDOException $e) {
                    echo '<div class="alert alert-warning">
                            ⚠️ ' . $e->getMessage() . '
                            <br><small>Puede que la tabla ya exista, esto es normal.</small>
                          </div>';
                }
            }
            ?>
            
            <form method="POST">
                <p class="lead">
                    Este instalador creará la tabla de <strong>productos</strong> en tu base de datos.
                </p>
                
                <div class="alert alert-warning">
                    <strong>⚠️ Importante:</strong>
                    <ul class="mb-0">
                        <li>Solo debes ejecutar esto UNA VEZ</li>
                        <li>Si ya existe la tabla, no pasará nada</li>
                        <li>Se crearán 3 productos de ejemplo</li>
                    </ul>
                </div>
                
                <button type="submit" name="instalar" class="btn btn-primary btn-lg">
                    <i class="fas fa-download"></i> Instalar Tabla de Productos
                </button>
                <a href="index.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </form>
        </div>
    </div>
</body>
</html>
