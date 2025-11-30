<?php
require_once 'config/config.php';

header('Content-Type: application/json');

echo json_encode([
    'public_key' => MP_PUBLIC_KEY,
    'modo_prueba' => MP_MODO_PRUEBA,
    'tipo' => (strpos(MP_PUBLIC_KEY, 'TEST-') === 0) ? 'CREDENCIALES DE TEST ✅' : 'CREDENCIALES DE PRODUCCIÓN',
    'access_token_inicio' => substr(MP_ACCESS_TOKEN, 0, 20) . '...'
], JSON_PRETTY_PRINT);
