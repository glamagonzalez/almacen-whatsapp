<?php
/**
 * DATABASE.PHP - Configuración de base de datos
 * 
 * Establece la conexión con MySQL usando PDO
 */

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'almacen_digital';
$username = 'root';
$password = '';

try {
    // Crear conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Si hay error de conexión
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión a base de datos: ' . $e->getMessage()
    ]));
}

/**
 * EXPLICACIÓN:
 * 
 * PDO (PHP Data Objects) es una interfaz para acceder a bases de datos en PHP
 * Es más segura que mysqli porque previene inyecciones SQL
 * 
 * Parámetros de conexión:
 * - host: donde está el servidor MySQL (localhost si es local)
 * - dbname: nombre de nuestra base de datos
 * - username: usuario de MySQL (por defecto 'root' en XAMPP)
 * - password: contraseña (vacía por defecto en XAMPP)
 */
?>
