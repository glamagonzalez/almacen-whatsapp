<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

// Configurar credenciales
MercadoPagoConfig::setAccessToken(MP_ACCESS_TOKEN);

try {
    $client = new PreferenceClient();
    
    // Crear preferencia de prueba
    $preference = $client->create([
        "items" => [
            [
                "title" => "Producto de Prueba",
                "quantity" => 1,
                "unit_price" => 100
            ]
        ],
        "payer" => [
            "name" => "Test",
            "email" => "test@test.com"
        ],
        "statement_descriptor" => "TEST MP"
    ]);
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Test Mercado Pago</title>
        <meta charset='UTF-8'>
    </head>
    <body style='font-family: Arial; padding: 20px;'>
        <h1>✅ Conexión exitosa con Mercado Pago</h1>
        <p><strong>Modo:</strong> " . (MP_MODO_PRUEBA ? 'TEST' : 'PRODUCCIÓN') . "</p>
        <p><strong>Public Key:</strong> " . substr(MP_PUBLIC_KEY, 0, 30) . "...</p>
        <p><strong>Preference ID:</strong> {$preference->id}</p>
        <hr>
        <a href='{$preference->init_point}' target='_blank' style='display: inline-block; background: #009ee3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px;'>
            Ir a Pagar (TEST)
        </a>
    </body>
    </html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error Test MP</title>
        <meta charset='UTF-8'>
    </head>
    <body style='font-family: Arial; padding: 20px;'>
        <h1>❌ Error al conectar con Mercado Pago</h1>
        <pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>" . $e->getMessage() . "</pre>
        <hr>
        <p><strong>Verifica:</strong></p>
        <ul>
            <li>Public Key: " . substr(MP_PUBLIC_KEY, 0, 30) . "...</li>
            <li>Access Token: " . substr(MP_ACCESS_TOKEN, 0, 30) . "...</li>
            <li>Modo: " . (MP_MODO_PRUEBA ? 'TEST' : 'PRODUCCIÓN') . "</li>
        </ul>
    </body>
    </html>";
}
