<?php
/**
 * IMPORTAR_CSV.PHP - Importar productos desde Excel/CSV
 * 
 * Este archivo permite importar productos masivamente desde un archivo
 * exportado del sistema de gestión del almacén
 */
require_once 'config/database.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Productos - Almacén WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .ejemplo-csv {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-white">
                <i class="fas fa-file-csv"></i> Importar Productos desde CSV/Excel
            </h1>
            <a href="productos.php" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- Instrucciones -->
        <div class="card">
            <div class="card-body">
                <h4><i class="fas fa-info-circle text-info"></i> ¿Cómo funciona?</h4>
                <ol>
                    <li>Exporta los productos desde tu <strong>sistema de gestión del almacén</strong></li>
                    <li>Asegúrate que el archivo tenga estas columnas (Excel o CSV)</li>
                    <li>Sube el archivo aquí</li>
                    <li>El sistema calculará automáticamente el <strong>precio de venta</strong> con tu margen</li>
                </ol>

                <h5 class="mt-4">📋 Formato del archivo (CSV o Excel):</h5>
                <div class="ejemplo-csv">
nombre,codigo_barras,categoria,precio_compra,stock,proveedor
Coca Cola 2.5L,7790895001234,Bebidas,150.00,24,Maxi Consumo
Arroz Largo Fino 1kg,7791234567890,Alimentos,80.00,50,Maxi Consumo
Detergente 500ml,7795678901234,Limpieza,120.00,15,Maxi Consumo
                </div>

                <div class="alert alert-info mt-3">
                    <strong>💡 Importante:</strong>
                    <ul class="mb-0">
                        <li><strong>precio_compra</strong>: El precio al que compraste el producto</li>
                        <li><strong>Margen</strong>: Se aplicará automáticamente (configurable abajo)</li>
                        <li><strong>Precio venta</strong>: Se calcula solo = compra + margen</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Formulario de importación -->
        <div class="card">
            <div class="card-body">
                <h4><i class="fas fa-upload text-success"></i> Subir archivo de productos</h4>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label"><strong>Archivo CSV o Excel:</strong></label>
                            <input type="file" name="archivo" class="form-control" 
                                   accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted">Formatos soportados: CSV, Excel (.xlsx, .xls)</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Margen de ganancia (%):</strong></label>
                            <input type="number" name="margen_default" class="form-control" 
                                   value="30" step="0.01" min="0" max="500">
                            <small class="text-muted">Se aplicará a todos los productos</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Separador CSV (si es CSV):</strong></label>
                        <select name="separador" class="form-select">
                            <option value=",">Coma (,)</option>
                            <option value=";">Punto y coma (;)</option>
                            <option value="\t">Tabulación (Tab)</option>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="actualizar_existentes" 
                               id="actualizar" value="1">
                        <label class="form-check-label" for="actualizar">
                            <strong>Actualizar productos existentes</strong> (si ya existen por código de barras)
                        </label>
                    </div>

                    <button type="submit" name="importar" class="btn btn-success btn-lg">
                        <i class="fas fa-file-import"></i> Importar Productos
                    </button>
                </form>
            </div>
        </div>

        <?php
        // ==========================================
        // PROCESAR IMPORTACIÓN
        // ==========================================
        if (isset($_POST['importar']) && isset($_FILES['archivo'])) {
            $margenDefault = floatval($_POST['margen_default']);
            $separador = $_POST['separador'] === '\t' ? "\t" : $_POST['separador'];
            $actualizarExistentes = isset($_POST['actualizar_existentes']);
            
            $archivo = $_FILES['archivo'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            
            echo '<div class="card">
                    <div class="card-body">
                        <h4>📊 Procesando importación...</h4>';
            
            try {
                $productos = [];
                
                // ==========================================
                // LEER CSV
                // ==========================================
                if ($extension === 'csv') {
                    if (($handle = fopen($archivo['tmp_name'], 'r')) !== FALSE) {
                        $headers = fgetcsv($handle, 1000, $separador);
                        
                        while (($data = fgetcsv($handle, 1000, $separador)) !== FALSE) {
                            $productos[] = array_combine($headers, $data);
                        }
                        fclose($handle);
                    }
                }
                // ==========================================
                // LEER EXCEL (requiere librería - versión simplificada)
                // ==========================================
                else if (in_array($extension, ['xlsx', 'xls'])) {
                    echo '<div class="alert alert-warning">
                            ⚠️ Para archivos Excel, por favor guárdalos como CSV primero.<br>
                            En Excel: Archivo → Guardar como → CSV (delimitado por comas)
                          </div>';
                    $productos = [];
                }
                
                if (count($productos) > 0) {
                    echo '<div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Código</th>
                                        <th>Compra</th>
                                        <th>Margen</th>
                                        <th>Venta</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    
                    $importados = 0;
                    $actualizados = 0;
                    $errores = 0;
                    
                    foreach ($productos as $prod) {
                        // Validar campos requeridos
                        if (empty($prod['nombre']) || empty($prod['precio_compra'])) {
                            continue;
                        }
                        
                        $nombre = trim($prod['nombre']);
                        $codigoBarras = trim($prod['codigo_barras'] ?? '');
                        $categoria = trim($prod['categoria'] ?? 'Sin categoría');
                        $precioCompra = floatval($prod['precio_compra']);
                        $stock = intval($prod['stock'] ?? 0);
                        $proveedor = trim($prod['proveedor'] ?? 'Maxi Consumo');
                        
                        // CALCULAR PRECIO DE VENTA CON MARGEN
                        $margen = $margenDefault;
                        $precioVenta = $precioCompra + ($precioCompra * $margen / 100);
                        $ganancia = $precioVenta - $precioCompra;
                        
                        try {
                            // Verificar si existe por código de barras
                            $existe = false;
                            if (!empty($codigoBarras)) {
                                $stmtCheck = $pdo->prepare("SELECT id FROM productos WHERE codigo_barras = ?");
                                $stmtCheck->execute([$codigoBarras]);
                                $existe = $stmtCheck->fetch();
                            }
                            
                            if ($existe && $actualizarExistentes) {
                                // ACTUALIZAR
                                $stmt = $pdo->prepare("
                                    UPDATE productos SET
                                        nombre = ?,
                                        categoria = ?,
                                        precio_compra = ?,
                                        margen_porcentaje = ?,
                                        precio_venta = ?,
                                        stock_actual = ?,
                                        proveedor = ?
                                    WHERE codigo_barras = ?
                                ");
                                $stmt->execute([
                                    $nombre, $categoria, $precioCompra, $margen,
                                    $precioVenta, $stock, $proveedor, $codigoBarras
                                ]);
                                $actualizados++;
                                $estado = '<span class="badge bg-info">Actualizado</span>';
                                
                            } else if (!$existe) {
                                // INSERTAR NUEVO
                                $stmt = $pdo->prepare("
                                    INSERT INTO productos (
                                        nombre, codigo_barras, categoria, precio_compra,
                                        margen_porcentaje, precio_venta, stock_actual, proveedor
                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $nombre, $codigoBarras, $categoria, $precioCompra,
                                    $margen, $precioVenta, $stock, $proveedor
                                ]);
                                $importados++;
                                $estado = '<span class="badge bg-success">✅ Nuevo</span>';
                                
                            } else {
                                $estado = '<span class="badge bg-warning">Omitido</span>';
                            }
                            
                            echo '<tr>
                                    <td><strong>' . htmlspecialchars($nombre) . '</strong></td>
                                    <td>' . htmlspecialchars($codigoBarras) . '</td>
                                    <td>$' . number_format($precioCompra, 2) . '</td>
                                    <td>' . number_format($margen, 1) . '%</td>
                                    <td class="text-success">$' . number_format($precioVenta, 2) . '</td>
                                    <td>' . $stock . '</td>
                                    <td>' . $estado . '</td>
                                  </tr>';
                            
                        } catch (Exception $e) {
                            $errores++;
                            echo '<tr>
                                    <td>' . htmlspecialchars($nombre) . '</td>
                                    <td colspan="6">
                                        <span class="badge bg-danger">❌ Error: ' . $e->getMessage() . '</span>
                                    </td>
                                  </tr>';
                        }
                    }
                    
                    echo '</tbody></table></div>';
                    
                    // Resumen
                    echo '<div class="alert alert-success mt-4">
                            <h5>✅ Importación completada</h5>
                            <ul class="mb-0">
                                <li><strong>' . $importados . '</strong> productos nuevos importados</li>
                                <li><strong>' . $actualizados . '</strong> productos actualizados</li>
                                <li><strong>' . $errores . '</strong> errores</li>
                            </ul>
                            <a href="productos.php" class="btn btn-success mt-3">
                                <i class="fas fa-box-open"></i> Ver Productos
                            </a>
                          </div>';
                    
                } else {
                    echo '<div class="alert alert-warning">
                            ⚠️ No se encontraron productos en el archivo
                          </div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">
                        ❌ Error al procesar el archivo: ' . $e->getMessage() . '
                      </div>';
            }
            
            echo '</div></div>';
        }
        ?>

        <!-- Plantilla descargable -->
        <div class="card">
            <div class="card-body">
                <h5><i class="fas fa-download text-primary"></i> Descargar plantilla</h5>
                <p>Si no tienes un archivo, descarga esta plantilla de ejemplo:</p>
                <a href="descargar_plantilla.php" class="btn btn-outline-primary">
                    <i class="fas fa-file-csv"></i> Descargar plantilla.csv
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
