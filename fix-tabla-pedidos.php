<?php
/**
 * Script para corregir la tabla pedidos
 * Ejecuta este archivo UNA VEZ desde el navegador
 */
require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reparar Tabla Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title mb-4">🔧 Reparar Tabla Pedidos</h2>
                
                <?php
                try {
                    echo '<div class="alert alert-info">Verificando estructura de la tabla...</div>';
                    
                    // Ver columnas actuales
                    $stmt = $pdo->query('DESCRIBE pedidos');
                    $columnas_actuales = [];
                    
                    echo '<h5>Columnas actuales:</h5><ul>';
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $columnas_actuales[] = $row['Field'];
                        echo '<li>' . htmlspecialchars($row['Field']) . ' - ' . htmlspecialchars($row['Type']) . '</li>';
                    }
                    echo '</ul>';
                    
                    // Agregar columna envio si no existe
                    if (!in_array('envio', $columnas_actuales)) {
                        echo '<div class="alert alert-warning">⚠️ Falta la columna "envio". Agregando...</div>';
                        $pdo->exec('ALTER TABLE pedidos ADD COLUMN envio DECIMAL(10,2) DEFAULT 0.00 COMMENT "Costo de envío"');
                        echo '<div class="alert alert-success">✅ Columna "envio" agregada correctamente</div>';
                    } else {
                        echo '<div class="alert alert-success">✅ La columna "envio" ya existe</div>';
                    }
                    
                    echo '<hr>';
                    echo '<div class="alert alert-success">';
                    echo '<h4>✅ ¡Tabla reparada exitosamente!</h4>';
                    echo '<p>Ahora puedes cerrar esta ventana y probar nuevamente el checkout.</p>';
                    echo '<a href="catalogo.php" class="btn btn-primary">Ir al Catálogo</a> ';
                    echo '<a href="checkout-mejorado.php" class="btn btn-success">Ir al Checkout</a>';
                    echo '</div>';
                    
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">';
                    echo '<h4>❌ Error:</h4>';
                    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
