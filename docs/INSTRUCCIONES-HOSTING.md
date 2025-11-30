# 📦 GUÍA DE INSTALACIÓN EN HOSTING

## 🎯 Archivos a Subir

Sube TODOS los archivos de la carpeta `almacen-whatsapp-1` al hosting, EXCEPTO:
- ❌ `.git/` (carpeta Git)
- ❌ `README.md`, `CHECKOUT-MEJORADO-README.md`, etc. (documentación)
- ❌ Este archivo (`INSTRUCCIONES-HOSTING.md`)

## 📊 Base de Datos

### 1. Crear Base de Datos en cPanel
1. Ve a **phpMyAdmin** en tu hosting
2. Crea una nueva base de datos llamada: `almacen_digital`
3. Crea un usuario con todos los privilegios
4. Anota: nombre de BD, usuario y contraseña

### 2. Importar Datos
1. En phpMyAdmin, selecciona la base de datos `almacen_digital`
2. Ve a la pestaña **"Importar"**
3. Selecciona el archivo `almacen_digital.sql`
4. Haz clic en **"Continuar"**

## ⚙️ Configuración

### 1. Archivo `config/database.php`

Edita este archivo y cambia:

```php
// CONFIGURACIÓN PARA HOSTING
$host = 'localhost';  // Generalmente es 'localhost'
$dbname = 'nombre_de_tu_base_datos';  // El nombre que anotaste
$username = 'usuario_de_tu_base_datos';  // El usuario que anotaste
$password = 'tu_contraseña';  // La contraseña que anotaste
```

### 2. Archivo `config/mercadopago.php`

Las credenciales ya están configuradas:
- ✅ Access Token de PRODUCCIÓN
- ✅ Public Key de PRODUCCIÓN

**IMPORTANTE:** Cambia la URL del webhook:

```php
define('MP_NOTIFICATION_URL', 'https://tudominio.com/webhook_mp.php');
```

Reemplaza `tudominio.com` por tu dominio real.

### 3. Configurar Webhook en Mercado Pago

1. Ve a: https://www.mercadopago.com.ar/developers/panel/app
2. Selecciona tu aplicación
3. Ve a **"Webhooks"**
4. Agrega la URL: `https://tudominio.com/webhook_mp.php`
5. Selecciona eventos: `payment` (todos los eventos de pago)

## 🔗 URLs del Sistema

Una vez subido, tu sistema estará en:

- **Catálogo (clientes):** `https://tudominio.com/catalogo.php`
- **Checkout:** `https://tudominio.com/checkout-mejorado.php`
- **Admin (futuro):** `https://tudominio.com/admin/`

## 🔐 Seguridad

### 1. Proteger archivos sensibles

Crea un archivo `.htaccess` en la raíz con:

```apache
# Proteger archivos de configuración
<FilesMatch "^(config|\.env)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Proteger archivos SQL
<FilesMatch "\.sql$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 2. Cambiar contraseña de admin

En phpMyAdmin, ejecuta:

```sql
UPDATE usuarios 
SET password = MD5('tu_nueva_contraseña_segura') 
WHERE email = 'admin@almacendigital.com';
```

## 📱 WhatsApp (n8n + WAHA)

Para las notificaciones de WhatsApp en el hosting:

### Opción 1: VPS/Servidor Propio
- Instala n8n y WAHA en un VPS
- Configura las URLs en el workflow de n8n
- Actualiza las URLs en `api/procesar_pago.php` y `webhook_mp.php`

### Opción 2: Servicios Cloud
- n8n Cloud: https://n8n.cloud
- WAHA en servidor separado o servicio de WhatsApp API

**NOTA:** WhatsApp requiere servidor con IP fija. El hosting compartido normal NO puede correr WAHA/n8n.

## ✅ Checklist Final

Antes de lanzar:

- [ ] Base de datos importada correctamente
- [ ] Archivo `config/database.php` configurado
- [ ] URL del webhook actualizada en `config/mercadopago.php`
- [ ] Webhook configurado en panel de Mercado Pago
- [ ] Probado el flujo completo: catálogo → checkout → pago
- [ ] Contraseña de admin cambiada
- [ ] Productos reales cargados en la base de datos
- [ ] `.htaccess` configurado para seguridad

## 🆘 Problemas Comunes

### "Error al conectar con la base de datos"
- Verifica usuario, contraseña y nombre de BD en `config/database.php`
- Asegúrate que el usuario tenga todos los privilegios

### "No llega el webhook de Mercado Pago"
- Verifica que la URL sea HTTPS (no HTTP)
- Verifica que el archivo `webhook_mp.php` sea accesible públicamente
- Revisa los logs en `logs/mp-webhook.log`

### "Las imágenes no cargan"
- Verifica permisos de carpeta `uploads/` (755 o 777)
- Verifica que las rutas en la BD sean correctas

## 📞 Soporte

Para más ayuda, revisa:
- Documentación de tu hosting
- Panel de Mercado Pago: https://www.mercadopago.com.ar/developers
- Logs de errores en `logs/`

---

**Fecha de creación:** 29 de Noviembre, 2025
**Sistema:** Almacén Digital v1.0
