<?php
/**
 * CONFIGURACIÓN DE MERCADO PAGO
 * 
 * IMPORTANTE: Obtén tus credenciales en:
 * https://www.mercadopago.com.ar/developers/panel/app
 */

// Credenciales de Mercado Pago
// REEMPLAZA ESTOS VALORES CON TUS CREDENCIALES REALES
define('MP_ACCESS_TOKEN', 'TU_ACCESS_TOKEN_AQUI');
define('MP_PUBLIC_KEY', 'TU_PUBLIC_KEY_AQUI');

// URL de notificaciones (webhook)
define('MP_NOTIFICATION_URL', 'http://localhost/almacen-whatsapp-1/webhook_mp.php');

// Configuración general
define('MP_MODO_PRUEBA', true); // true = testing, false = producción

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
