-- =============================================
-- BASE DE DATOS: almacen_whatsapp
-- Descripción: Sistema de gestión de archivos
-- =============================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS almacen_whatsapp 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE almacen_whatsapp;

-- =============================================
-- TABLA: archivos
-- Almacena información de todos los archivos subidos
-- =============================================
CREATE TABLE IF NOT EXISTS archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_original VARCHAR(255) NOT NULL COMMENT 'Nombre original del archivo',
    nombre_archivo VARCHAR(255) NOT NULL COMMENT 'Nombre único generado en el servidor',
    tipo VARCHAR(100) NOT NULL COMMENT 'Tipo MIME del archivo',
    tamanio BIGINT NOT NULL COMMENT 'Tamaño en bytes',
    ruta VARCHAR(500) NOT NULL COMMENT 'Ruta donde se guardó el archivo',
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de subida',
    enviado_whatsapp BOOLEAN DEFAULT FALSE COMMENT 'Indica si fue enviado por WhatsApp',
    fecha_envio_whatsapp DATETIME NULL COMMENT 'Fecha de envío por WhatsApp',
    numero_whatsapp VARCHAR(20) NULL COMMENT 'Número al que se envió',
    INDEX idx_fecha (fecha_subida),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: productos
-- Gestión de productos con precios y márgenes
-- =============================================
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_barras VARCHAR(50) NULL COMMENT 'Código de barras del producto',
    nombre VARCHAR(255) NOT NULL COMMENT 'Nombre del producto',
    descripcion TEXT NULL COMMENT 'Descripción detallada',
    categoria VARCHAR(100) NULL COMMENT 'Categoría (Alimentos, Bebidas, Limpieza, etc)',
    
    -- PRECIOS Y MÁRGENES
    precio_compra DECIMAL(10,2) NOT NULL COMMENT 'Precio al que lo compraste',
    margen_porcentaje DECIMAL(5,2) DEFAULT 30.00 COMMENT 'Margen de ganancia en %',
    precio_venta DECIMAL(10,2) NOT NULL COMMENT 'Precio final de venta',
    
    -- INVENTARIO
    stock_actual INT DEFAULT 0 COMMENT 'Cantidad disponible',
    stock_minimo INT DEFAULT 5 COMMENT 'Alerta cuando llegue a este stock',
    
    -- IMÁGENES
    archivo_id INT NULL COMMENT 'ID del archivo (imagen del producto)',
    imagen_url VARCHAR(500) NULL COMMENT 'URL de la imagen',
    
    -- PROVEEDOR
    proveedor VARCHAR(100) DEFAULT 'Maxi Consumo' COMMENT 'De dónde lo compraste',
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (archivo_id) REFERENCES archivos(id) ON DELETE SET NULL,
    INDEX idx_categoria (categoria),
    INDEX idx_codigo (codigo_barras)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: usuarios (para futuras mejoras)
-- =============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: pedidos
-- Gestión de pedidos con Mercado Pago
-- =============================================
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- CLIENTE
    cliente_nombre VARCHAR(150) NOT NULL COMMENT 'Nombre del cliente',
    cliente_telefono VARCHAR(20) NOT NULL COMMENT 'Teléfono/WhatsApp del cliente',
    cliente_email VARCHAR(150) NULL COMMENT 'Email del cliente',
    cliente_direccion TEXT NULL COMMENT 'Dirección de envío',
    
    -- PRODUCTOS (JSON con array de productos)
    productos_json TEXT NOT NULL COMMENT 'Lista de productos en formato JSON',
    
    -- TOTALES
    subtotal DECIMAL(10,2) NOT NULL COMMENT 'Suma de productos',
    envio DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Costo de envío',
    total DECIMAL(10,2) NOT NULL COMMENT 'Total a pagar',
    
    -- MERCADO PAGO
    mp_preference_id VARCHAR(100) NULL COMMENT 'ID de preferencia de Mercado Pago',
    mp_payment_id VARCHAR(100) NULL COMMENT 'ID del pago en Mercado Pago',
    mp_status VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, approved, rejected',
    mp_link_pago TEXT NULL COMMENT 'Link de pago de Mercado Pago',
    
    -- ESTADO DEL PEDIDO
    estado VARCHAR(50) DEFAULT 'pendiente' COMMENT 'pendiente, pagado, enviado, entregado, cancelado',
    fecha_pago DATETIME NULL COMMENT 'Fecha en que se confirmó el pago',
    fecha_envio DATETIME NULL COMMENT 'Fecha en que se envió',
    fecha_entrega DATETIME NULL COMMENT 'Fecha de entrega',
    
    -- NOTAS
    notas TEXT NULL COMMENT 'Notas adicionales del pedido',
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_cliente_telefono (cliente_telefono),
    INDEX idx_estado (estado),
    INDEX idx_mp_payment (mp_payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: log_actividades
-- Para auditoría y seguimiento
-- =============================================
CREATE TABLE IF NOT EXISTS log_actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    archivo_id INT NULL,
    pedido_id INT NULL,
    accion VARCHAR(50) NOT NULL COMMENT 'upload, download, delete, send_whatsapp, pago_recibido, envio_realizado',
    descripcion TEXT NULL,
    ip_address VARCHAR(50) NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (archivo_id) REFERENCES archivos(id) ON DELETE SET NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Insertar algunos datos de ejemplo (opcional)
-- =============================================
INSERT INTO usuarios (nombre, email, password, activo) VALUES
('Administrador', 'admin@almacen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
-- Password: password (hash generado con password_hash())

-- =============================================
-- Ejemplos de productos
-- =============================================
INSERT INTO productos (nombre, categoria, precio_compra, margen_porcentaje, precio_venta, stock_actual, proveedor) VALUES
('Coca Cola 2.5L', 'Bebidas', 150.00, 30.00, 195.00, 24, 'Maxi Consumo'),
('Arroz Largo Fino 1kg', 'Alimentos', 80.00, 35.00, 108.00, 50, 'Maxi Consumo'),
('Detergente Magistral 500ml', 'Limpieza', 120.00, 25.00, 150.00, 15, 'Maxi Consumo');

-- =============================================
-- TABLA: configuracion_mp
-- Configuración de Mercado Pago
-- =============================================
CREATE TABLE IF NOT EXISTS configuracion_mp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    access_token TEXT NOT NULL COMMENT 'Access Token de Mercado Pago',
    public_key TEXT NOT NULL COMMENT 'Public Key de Mercado Pago',
    activo BOOLEAN DEFAULT TRUE,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- EXPLICACIÓN DE LA ESTRUCTURA:
-- 
-- 1. TABLA archivos: Es el corazón del sistema
--    - Guarda toda la información de los archivos
--    - Incluye campos para WhatsApp
--    - Los índices mejoran la velocidad de búsqueda
--
-- 2. TABLA usuarios: Para control de acceso
--    - Permitirá login en futuras versiones
--    - Password encriptado con bcrypt
--
-- 3. TABLA log_actividades: Auditoría
--    - Registra todas las acciones importantes
--    - Útil para seguridad y análisis
-- =============================================
