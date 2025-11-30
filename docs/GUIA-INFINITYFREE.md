# 🚀 GUÍA PARA SUBIR A INFINITYFREE

## 📋 Requisitos Previos

1. Cuenta en InfinityFree: https://infinityfree.net
2. Dominio gratuito (te lo da InfinityFree) o tu propio dominio
3. Archivos del sistema listos para subir

---

## 🔧 PASO 1: Configurar Hosting en InfinityFree

### 1.1 Crear Cuenta
1. Ve a https://infinityfree.net
2. Regístrate gratis
3. Verifica tu email

### 1.2 Crear Sitio Web
1. En el panel, haz clic en **"Create Account"**
2. Elige un subdominio (ejemplo: `tualmacen.rf.gd`)
3. Espera 5-10 minutos a que se active

### 1.3 Obtener Datos de Base de Datos
1. En el panel de InfinityFree, ve a **"MySQL Databases"**
2. Crea una base de datos
3. Anota estos datos (los necesitarás):
   ```
   Database Name: epiz_XXXXXXXX_almacen
   Username: epiz_XXXXXXXX
   Password: [la que elegiste]
   Hostname: sqlXXX.infinityfree.com
   ```

---

## 📤 PASO 2: Subir Archivos

### Opción A: FTP con FileZilla (Recomendado)

1. **Descargar FileZilla:**
   - https://filezilla-project.org/download.php?type=client

2. **Obtener datos FTP de InfinityFree:**
   - En tu panel, ve a **"FTP Details"**
   - Anota:
     ```
     FTP Hostname: ftpupload.net
     FTP Username: epiz_XXXXXXXX
     FTP Password: [tu contraseña]
     FTP Port: 21
     ```

3. **Conectar con FileZilla:**
   - Abre FileZilla
   - Host: `ftpupload.net`
   - Usuario: `epiz_XXXXXXXX`
   - Contraseña: tu contraseña
   - Puerto: `21`
   - Clic en **"Conexión rápida"**

4. **Subir archivos:**
   - En el panel derecho, navega a la carpeta `/htdocs/`
   - En el panel izquierdo, navega a: `C:\xampp\htdocs\almacen-whatsapp-1\`
   - Selecciona TODOS los archivos (excepto documentación)
   - Arrastra al panel derecho
   - Espera a que termine (puede tardar 10-20 min)

### Opción B: File Manager de cPanel

1. Ve a tu panel de InfinityFree
2. Clic en **"Control Panel"** (abre VistaPanel)
3. Busca **"Online File Manager"**
4. Navega a `/htdocs/`
5. Sube todos los archivos (puedes comprimir en ZIP primero)

---

## 🗄️ PASO 3: Importar Base de Datos

### 3.1 Acceder a phpMyAdmin
1. En el panel de InfinityFree, busca **"MySQL Databases"**
2. Clic en **"phpMyAdmin"**
3. Inicia sesión con tus datos de BD

### 3.2 Importar SQL
1. Selecciona tu base de datos (ejemplo: `epiz_XXXXXXXX_almacen`)
2. Ve a la pestaña **"Import"** o **"Importar"**
3. Clic en **"Choose File"**
4. Selecciona: `C:\xampp\htdocs\almacen-whatsapp-1\almacen_digital.sql`
5. Clic en **"Go"** o **"Continuar"**
6. Espera a que termine (debería decir "Import successful")

---

## ⚙️ PASO 4: Configurar Archivos

### 4.1 Editar `config/database.php`

Usando FileZilla o File Manager, edita este archivo:

```php
<?php
// CONFIGURACIÓN PARA INFINITYFREE
$host = 'sqlXXX.infinityfree.com';  // Reemplaza XXX con tu número
$dbname = 'epiz_XXXXXXXX_almacen';   // Tu nombre de BD
$username = 'epiz_XXXXXXXX';          // Tu usuario
$password = 'tu_contraseña_aqui';     // Tu contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
```

### 4.2 Editar `config/mercadopago.php`

Cambia la URL del webhook:

```php
// URL de notificaciones (webhook)
define('MP_NOTIFICATION_URL', 'https://tualmacen.rf.gd/webhook_mp.php');
// Reemplaza 'tualmacen.rf.gd' por tu dominio real
```

---

## 🔗 PASO 5: Configurar Mercado Pago

1. Ve a: https://www.mercadopago.com.ar/developers/panel/app
2. Selecciona tu aplicación
3. Ve a **"Webhooks"** en el menú lateral
4. Clic en **"Configurar notificaciones"**
5. Ingresa: `https://tualmacen.rf.gd/webhook_mp.php`
6. Selecciona el evento: **"Pagos"** (payment)
7. Guarda

---

## ✅ PASO 6: Probar el Sistema

### 6.1 Acceder al Catálogo
Abre en tu navegador:
```
https://tualmacen.rf.gd/catalogo.php
```

### 6.2 Prueba Completa
1. Agrega productos al carrito
2. Ve al checkout
3. Completa los datos
4. **IMPORTANTE:** Para probar el pago, pide a un amigo/familiar que haga la compra
5. Verifica que el pedido llegue a la base de datos

---

## ⚠️ LIMITACIONES DE INFINITYFREE

### Lo que SÍ funciona:
- ✅ PHP y MySQL
- ✅ Catálogo de productos
- ✅ Checkout y pagos con Mercado Pago
- ✅ Base de datos
- ✅ Webhooks de Mercado Pago

### Lo que NO funciona:
- ❌ n8n (requiere Node.js)
- ❌ WAHA/WhatsApp API (requiere servidor con IP fija)
- ❌ Notificaciones automáticas de WhatsApp

### Solución para WhatsApp:
Opciones:
1. **Desactivar WhatsApp** por ahora (el sistema funciona sin él)
2. **Usar un VPS barato** para n8n + WAHA:
   - Contabo VPS: €3.99/mes
   - DigitalOcean: $6/mes
   - Vultr: $5/mes

---

## 🔐 Seguridad Importante

### 1. Cambiar Contraseña de Admin
En phpMyAdmin, ejecuta:
```sql
UPDATE usuarios 
SET password = MD5('nueva_contraseña_super_segura_123') 
WHERE email = 'admin@almacendigital.com';
```

### 2. Eliminar Archivos Innecesarios
Borra del hosting:
- `almacen_digital.sql` (después de importar)
- `test-mp.php`
- `verificar-config.php`
- Archivos `.md` de documentación

---

## 🆘 Problemas Comunes en InfinityFree

### "Error 508 - Resource Limit Reached"
- InfinityFree tiene límites de recursos
- Espera 1 hora y vuelve a intentar
- Considera actualizar a Premium ($1.99/mes)

### "Cannot connect to database"
- Verifica que usaste el hostname correcto (sqlXXX.infinityfree.com)
- Verifica usuario y contraseña
- Asegúrate que la BD se creó correctamente

### "403 Forbidden en vendor/"
- Común en InfinityFree
- Crea un archivo `.htaccess` en `/vendor/` con:
  ```apache
  Options -Indexes
  Allow from all
  ```

### Archivos no se suben
- InfinityFree bloquea algunos tipos de archivos
- Comprime en ZIP y sube
- Descomprime en el File Manager

---

## 📞 Soporte

- **InfinityFree Forum:** https://forum.infinityfree.net
- **Documentación:** https://infinityfree.net/support

---

**¡Tu sistema estará listo para recibir pedidos reales!** 🎉

Una vez configurado, comparte el link: `https://tualmacen.rf.gd/catalogo.php`
