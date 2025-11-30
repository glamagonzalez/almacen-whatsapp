<?php
/**
 * CONFIGURACIÓN GENERAL DEL SISTEMA
 */

// Incluir configuración de base de datos
require_once __DIR__ . '/database.php';

// Incluir configuración de Mercado Pago
require_once __DIR__ . '/mercadopago.php';

// Definir constantes para Mercado Pago (compatibilidad con api/procesar_pago.php)
define('MERCADOPAGO_ACCESS_TOKEN', MP_ACCESS_TOKEN);
define('MERCADOPAGO_PUBLIC_KEY', MP_PUBLIC_KEY);

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Errores en desarrollo (cambiar a false en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

// Crear carpeta de logs si no existe
$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

/**
 * Función helper para obtener conexión a la base de datos
 */
function getDBConnection() {
    global $pdo;
    return $pdo;
}
?>
