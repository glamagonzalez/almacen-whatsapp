# 🔐 CÓMO CONECTAR MERCADO PAGO

## 📋 Pasos para Configurar

### 1️⃣ Obtener Credenciales

1. **Ve a**: https://www.mercadopago.com.ar/developers
2. **Inicia sesión** con tu cuenta de Mercado Pago
3. **Crea una aplicación**:
   - Click en "Tus integraciones"
   - Click en "Crear aplicación"
   - Nombre: "Almacén WhatsApp"
   - Selecciona: "Pagos online"
4. **Ir a "Credenciales"**

### 2️⃣ Copiar tus Credenciales

#### 🧪 PARA PRUEBAS (Recomendado inicialmente):
```
Access Token de prueba: TEST-1234567890-ABCDEF...
Public Key de prueba: TEST-abc123-def456...
```

#### ✅ PARA PRODUCCIÓN:
```
Access Token: APP_USR-1234567890-ABCDEF...
Public Key: APP_USR-abc123-def456...
```

### 3️⃣ Configurar en tu Sistema

Edita el archivo: `config/mercadopago.php`

```php
<?php
// REEMPLAZA ESTOS VALORES:

// Para PRUEBAS:
define('MP_ACCESS_TOKEN', 'TEST-1234567890-ABCDEF-tu-token-aqui');
define('MP_PUBLIC_KEY', 'TEST-abc123-def456-tu-public-key-aqui');
define('MP_MODO_PRUEBA', true);

// Para PRODUCCIÓN (cuando todo funcione):
// define('MP_ACCESS_TOKEN', 'APP_USR-1234567890-ABCDEF-tu-token-aqui');
// define('MP_PUBLIC_KEY', 'APP_USR-abc123-def456-tu-public-key-aqui');
// define('MP_MODO_PRUEBA', false);
?>
```

### 4️⃣ Actualizar archivo de configuración principal

Edita el archivo: `config/config.php`

Asegúrate de que tenga:
```php
<?php
define('MERCADOPAGO_ACCESS_TOKEN', MP_ACCESS_TOKEN);
define('MERCADOPAGO_PUBLIC_KEY', MP_PUBLIC_KEY);
?>
```

---

## 🧪 PROBAR CON CREDENCIALES DE PRUEBA

### Tarjetas de Prueba

Mercado Pago te da tarjetas de prueba para probar pagos:

#### ✅ **APROBADA** (Pago exitoso):
```
Tarjeta: 5031 7557 3453 0604
CVV: 123
Fecha: 11/25
Nombre: APRO
```

#### ❌ **RECHAZADA** (Fondos insuficientes):
```
Tarjeta: 5031 4332 1540 6351
CVV: 123
Fecha: 11/25
Nombre: FUND
```

#### ⏳ **PENDIENTE**:
```
Tarjeta: 5031 4332 1540 6351
CVV: 123
Fecha: 11/25
Nombre: PEND
```

### 🔗 Más tarjetas de prueba:
https://www.mercadopago.com.ar/developers/es/docs/checkout-api/testing

---

## 🚀 FLUJO COMPLETO DE PRUEBA

1. **Abrir catálogo**: http://localhost/almacen-whatsapp-1/catalogo.php
2. **Agregar productos** al carrito
3. **Aplicar cupón** (ej: PRIMERACOMPRA)
4. **Ir a checkout**: Click en "Finalizar Compra"
5. **Completar datos**:
   - Nombre: Juan Pérez
   - WhatsApp: 5491157816498
   - Email: test@test.com
   - Dirección: Av. Corrientes 1234
   - CP: 1043 (CABA)
   - Ciudad: CABA
   - Provincia: Buenos Aires
6. **Seleccionar envío** (Estándar, Express o Retiro)
7. **Click "Pagar con Mercado Pago"**
8. **Se abre Mercado Pago** → Usar tarjeta de prueba
9. **Completa el pago**
10. **Vuelve a tu sitio** → payment-success.php 🎉

---

## 📊 VERIFICAR QUE FUNCIONE

### ✅ Checklist:

- [ ] Credenciales configuradas en `config/mercadopago.php`
- [ ] Archivo `config/config.php` tiene las constantes correctas
- [ ] Compositor instalado: `vendor/autoload.php` existe
- [ ] Base de datos actualizada (cupones, pedido_items, etc.)
- [ ] Productos en el catálogo
- [ ] Carrito funciona (localStorage)
- [ ] Checkout muestra productos
- [ ] Botón "Pagar con Mercado Pago" funciona
- [ ] Redirige a Mercado Pago
- [ ] Vuelve a payment-success.php después del pago

### 🐛 Si algo falla:

1. **Ver errores PHP**: Abre `c:\xampp\apache\logs\error.log`
2. **Ver consola del navegador**: F12 → Console
3. **Verificar Network**: F12 → Network → Ver si `api/procesar_pago.php` responde

---

## 🔧 TROUBLESHOOTING

### ❌ Error: "SDK de Mercado Pago no encontrado"

**Solución**: Instalar el SDK

```powershell
cd c:\xampp\htdocs\almacen-whatsapp-1
composer require mercadopago/dx-php
```

Si no tienes Composer:
1. Descargar de: https://getcomposer.org/download/
2. Instalar Composer
3. Ejecutar el comando de arriba

---

### ❌ Error: "Access Token inválido"

**Solución**: Verificar credenciales

1. Ve a https://www.mercadopago.com.ar/developers/panel/app
2. Copia nuevamente el Access Token
3. Asegúrate de usar el de **PRUEBA** primero
4. Pega en `config/mercadopago.php`

---

### ❌ Error: "No se puede crear preferencia"

**Solución**: Verificar datos enviados

1. Abre `api/procesar_pago.php`
2. Al inicio, agrega:
```php
error_log("Datos recibidos: " . print_r($data, true));
```
3. Ver log: `c:\xampp\apache\logs\error.log`

---

### ❌ No redirige después del pago

**Solución**: Verificar URLs de retorno

En `api/procesar_pago.php`, líneas 113-117:

```php
$preference->back_urls = [
    'success' => 'http://localhost/almacen-whatsapp-1/payment-success.php',
    'failure' => 'http://localhost/almacen-whatsapp-1/payment-failure.php',
    'pending' => 'http://localhost/almacen-whatsapp-1/payment-pending.php'
];
```

Asegúrate de que las URLs sean correctas.

---

## 🌐 PASAR A PRODUCCIÓN

Cuando todo funcione en pruebas:

### 1. Cambiar a credenciales de producción

```php
// config/mercadopago.php
define('MP_ACCESS_TOKEN', 'APP_USR-tu-token-real');
define('MP_PUBLIC_KEY', 'APP_USR-tu-public-key-real');
define('MP_MODO_PRUEBA', false); // ⚠️ IMPORTANTE
```

### 2. Actualizar URLs

```php
// En api/procesar_pago.php
$base_url = 'https://tudominio.com';

$preference->back_urls = [
    'success' => $base_url . '/payment-success.php',
    'failure' => $base_url . '/payment-failure.php',
    'pending' => $base_url . '/payment-pending.php'
];
```

### 3. Configurar Webhooks (Notificaciones)

1. Ve a tu app en Mercado Pago
2. Sección "Webhooks"
3. Agregar URL: `https://tudominio.com/webhook_mp.php`
4. Seleccionar eventos: `payment`, `merchant_order`

---

## 📞 SOPORTE

### 📚 Documentación Oficial:
- API Reference: https://www.mercadopago.com.ar/developers/es/reference
- SDK PHP: https://www.mercadopago.com.ar/developers/es/docs/sdks-library/server-side/php
- Testing: https://www.mercadopago.com.ar/developers/es/docs/checkout-api/testing

### 💬 Comunidad:
- Foro: https://www.mercadopago.com.ar/developers/es/community

---

## ✅ RESUMEN RÁPIDO

```bash
# 1. Obtener credenciales de:
https://www.mercadopago.com.ar/developers/panel/app

# 2. Editar archivo:
config/mercadopago.php

# 3. Reemplazar:
define('MP_ACCESS_TOKEN', 'TU_TOKEN_AQUI');
define('MP_PUBLIC_KEY', 'TU_PUBLIC_KEY_AQUI');

# 4. Probar con tarjeta:
5031 7557 3453 0604 (APRO - Aprobada)

# 5. ¡Listo! 🎉
```

---

**Fecha**: Noviembre 2024  
**Versión**: 2.0  
**Estado**: ✅ Listo para configurar
