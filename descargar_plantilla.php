<?php
/**
 * DESCARGAR_PLANTILLA.PHP - Genera plantilla CSV de ejemplo
 */

// Definir encabezados para descarga
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="plantilla_productos.csv"');

// Abrir salida
$output = fopen('php://output', 'w');

// Escribir encabezados
fputcsv($output, ['nombre', 'codigo_barras', 'categoria', 'precio_compra', 'stock', 'proveedor']);

// Escribir ejemplos
fputcsv($output, ['Coca Cola 2.5L', '7790895001234', 'Bebidas', '150.00', '24', 'Maxi Consumo']);
fputcsv($output, ['Arroz Largo Fino 1kg', '7791234567890', 'Alimentos', '80.00', '50', 'Maxi Consumo']);
fputcsv($output, ['Detergente Magistral 500ml', '7795678901234', 'Limpieza', '120.00', '15', 'Maxi Consumo']);
fputcsv($output, ['Fideos Matarazzo 500g', '7790123456789', 'Alimentos', '45.00', '100', 'Maxi Consumo']);
fputcsv($output, ['Leche La Serenísima 1L', '7790876543210', 'Lácteos', '95.00', '30', 'Maxi Consumo']);

fclose($output);
exit;
?>
