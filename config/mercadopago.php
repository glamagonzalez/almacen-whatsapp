<?php
/**
 * CONFIGURACIÓN DE MERCADO PAGO
 * 
 * IMPORTANTE: Obtén tus credenciales en:
 * https://www.mercadopago.com.ar/developers/panel/app
 */

// Credenciales de Mercado Pago - PRODUCCIÓN
define('MP_ACCESS_TOKEN', 'APP_USR-7544114614777894-112915-efb36d1a0152e91909406f8f3710edfc-62732469');
define('MP_PUBLIC_KEY', 'APP_USR-3c847c3f-cc9c-4aba-b2a9-62899023373f');

// URL de notificaciones (webhook)
define('MP_NOTIFICATION_URL', 'http://localhost/almacen-whatsapp-1/webhook_mp.php');

// Configuración general
define('MP_MODO_PRUEBA', false); // true = testing, false = producción

/**
 * PASOS PARA OBTENER TUS CREDENCIALES:
 * 
 * 1. Ve a: https://www.mercadopago.com.ar/developers/panel/app
 * 2. Crea una aplicación o selecciona una existente
 * 3. Ve a "Credenciales"
 * 4. Copia:
 *    - Access Token (token de acceso)
 *    - Public Key (clave pública)
 * 5. Reemplaza los valores arriba
 * 
 * IMPORTANTE:
 * - Para PRUEBAS: Usa las credenciales de "Credenciales de prueba"
 * - Para PRODUCCIÓN: Usa las credenciales de "Credenciales de producción"
 */
?>
