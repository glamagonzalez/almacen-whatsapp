-- =============================================
-- ACTUALIZACIÓN DE BASE DE DATOS - CHECKOUT MEJORADO
-- Agrega columnas para soportar cupones, envío mejorado, etc.
-- =============================================

USE almacen_whatsapp;

-- Agregar columnas para información de envío y cupones
ALTER TABLE pedidos 
ADD COLUMN IF NOT EXISTS cliente_cp VARCHAR(10) NULL COMMENT 'Código postal' AFTER cliente_direccion,
ADD COLUMN IF NOT EXISTS cliente_ciudad VARCHAR(100) NULL COMMENT 'Ciudad' AFTER cliente_cp,
ADD COLUMN IF NOT EXISTS cliente_provincia VARCHAR(100) NULL COMMENT 'Provincia' AFTER cliente_ciudad,
ADD COLUMN IF NOT EXISTS cliente_aclaraciones TEXT NULL COMMENT 'Aclaraciones del cliente' AFTER cliente_provincia,

ADD COLUMN IF NOT EXISTS metodo_envio VARCHAR(50) NULL COMMENT 'estandar, express, retiro' AFTER cliente_aclaraciones,
ADD COLUMN IF NOT EXISTS costo_envio DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Costo del envío' AFTER metodo_envio,

ADD COLUMN IF NOT EXISTS cupon_codigo VARCHAR(50) NULL COMMENT 'Código del cupón aplicado' AFTER costo_envio,
ADD COLUMN IF NOT EXISTS cupon_descuento DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Monto del descuento' AFTER cupon_codigo,

ADD COLUMN IF NOT EXISTS mp_external_reference VARCHAR(100) NULL COMMENT 'Referencia externa de MP' AFTER mp_link_pago;

-- Crear tabla para items del pedido (detalle de productos)
CREATE TABLE IF NOT EXISTS pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL COMMENT 'ID del pedido',
    producto_id INT NOT NULL COMMENT 'ID del producto',
    producto_nombre VARCHAR(255) NOT NULL COMMENT 'Nombre del producto (backup)',
    cantidad INT NOT NULL COMMENT 'Cantidad comprada',
    precio_unitario DECIMAL(10,2) NOT NULL COMMENT 'Precio al momento de la compra',
    subtotal DECIMAL(10,2) NOT NULL COMMENT 'Cantidad x Precio',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    
    INDEX idx_pedido (pedido_id),
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla para cupones de descuento
CREATE TABLE IF NOT EXISTS cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE COMMENT 'Código del cupón',
    descripcion VARCHAR(255) NULL COMMENT 'Descripción del cupón',
    tipo ENUM('porcentaje', 'fijo', 'envio') NOT NULL COMMENT 'Tipo de descuento',
    valor DECIMAL(10,2) NOT NULL COMMENT 'Valor del descuento (% o $)',
    
    fecha_inicio DATE NULL COMMENT 'Fecha desde cuando es válido',
    fecha_fin DATE NULL COMMENT 'Fecha hasta cuando es válido',
    
    usos_maximos INT NULL COMMENT 'Cantidad máxima de usos (NULL = ilimitado)',
    usos_actuales INT DEFAULT 0 COMMENT 'Cantidad de veces que se usó',
    
    monto_minimo DECIMAL(10,2) NULL COMMENT 'Monto mínimo de compra requerido',
    
    activo BOOLEAN DEFAULT TRUE COMMENT 'Si está activo o no',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar cupones por defecto
INSERT INTO cupones (codigo, descripcion, tipo, valor, activo, monto_minimo) VALUES
('PRIMERACOMPRA', 'Descuento 10% para tu primera compra', 'porcentaje', 10.00, TRUE, 500.00),
('VERANO2025', 'Super descuento verano 15%', 'porcentaje', 15.00, TRUE, 1000.00),
('ENVIOGRATIS', 'Envío gratis en tu compra', 'envio', 100.00, TRUE, 800.00),
('DESCUENTO50', 'Descuento fijo de $50', 'fijo', 50.00, TRUE, 300.00),
('BIENVENIDO', 'Bienvenido! 5% de descuento', 'porcentaje', 5.00, TRUE, 200.00)
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion);

-- Crear tabla para registrar usos de cupones
CREATE TABLE IF NOT EXISTS cupon_usos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cupon_id INT NOT NULL,
    pedido_id INT NOT NULL,
    cliente_email VARCHAR(150) NULL,
    cliente_telefono VARCHAR(20) NULL,
    monto_descuento DECIMAL(10,2) NOT NULL,
    fecha_uso DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cupon_id) REFERENCES cupones(id) ON DELETE CASCADE,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    
    INDEX idx_cupon (cupon_id),
    INDEX idx_pedido (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Actualizar columna de envio (cambio de nombre)
ALTER TABLE pedidos CHANGE COLUMN envio envio_old DECIMAL(10,2);
-- (La nueva columna costo_envio ya fue agregada arriba)

-- Actualizar productos_json a productos (ahora usamos tabla pedido_items)
-- No borramos productos_json por compatibilidad con pedidos antiguos

-- =============================================
-- VISTAS ÚTILES
-- =============================================

-- Vista de pedidos con información completa
CREATE OR REPLACE VIEW vista_pedidos_completos AS
SELECT 
    p.id,
    p.cliente_nombre,
    p.cliente_telefono,
    p.cliente_email,
    p.cliente_direccion,
    p.cliente_ciudad,
    p.cliente_provincia,
    p.metodo_envio,
    p.costo_envio,
    p.cupon_codigo,
    p.cupon_descuento,
    p.subtotal,
    p.total,
    p.estado,
    p.mp_status,
    p.mp_payment_id,
    p.fecha_creacion,
    p.fecha_pago,
    COUNT(pi.id) as cantidad_items,
    GROUP_CONCAT(CONCAT(pi.cantidad, 'x ', pi.producto_nombre) SEPARATOR ', ') as productos
FROM pedidos p
LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
GROUP BY p.id;

-- Vista de cupones activos
CREATE OR REPLACE VIEW vista_cupones_activos AS
SELECT 
    c.id,
    c.codigo,
    c.descripcion,
    c.tipo,
    c.valor,
    c.usos_maximos,
    c.usos_actuales,
    c.monto_minimo,
    CASE 
        WHEN c.usos_maximos IS NULL THEN 'Ilimitado'
        WHEN c.usos_actuales >= c.usos_maximos THEN 'Agotado'
        ELSE CONCAT(c.usos_maximos - c.usos_actuales, ' usos restantes')
    END as disponibilidad,
    CASE
        WHEN c.fecha_inicio IS NOT NULL AND CURDATE() < c.fecha_inicio THEN 'No comenzó'
        WHEN c.fecha_fin IS NOT NULL AND CURDATE() > c.fecha_fin THEN 'Expirado'
        ELSE 'Válido'
    END as estado_temporal
FROM cupones c
WHERE c.activo = TRUE;

-- =============================================
-- TRIGGERS
-- =============================================

-- Actualizar stock al crear items del pedido
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS after_pedido_item_insert
AFTER INSERT ON pedido_items
FOR EACH ROW
BEGIN
    -- Descontar del stock
    UPDATE productos 
    SET stock_actual = stock_actual - NEW.cantidad
    WHERE id = NEW.producto_id;
    
    -- Log de la actividad
    INSERT INTO log_actividades (pedido_id, accion, descripcion)
    VALUES (NEW.pedido_id, 'item_agregado', 
            CONCAT('Producto ', NEW.producto_nombre, ' x', NEW.cantidad));
END$$

-- Incrementar contador de usos de cupón
CREATE TRIGGER IF NOT EXISTS after_pedido_insert_cupon
AFTER INSERT ON pedidos
FOR EACH ROW
BEGIN
    IF NEW.cupon_codigo IS NOT NULL THEN
        UPDATE cupones 
        SET usos_actuales = usos_actuales + 1
        WHERE codigo = NEW.cupon_codigo;
        
        -- Registrar uso del cupón
        INSERT INTO cupon_usos (cupon_id, pedido_id, cliente_email, cliente_telefono, monto_descuento)
        SELECT id, NEW.id, NEW.cliente_email, NEW.cliente_telefono, NEW.cupon_descuento
        FROM cupones WHERE codigo = NEW.cupon_codigo;
    END IF;
END$$

DELIMITER ;

-- =============================================
-- Mensaje de confirmación
-- =============================================
SELECT 'Base de datos actualizada correctamente para CHECKOUT MEJORADO v2.0' AS mensaje;
SELECT COUNT(*) as cupones_disponibles FROM cupones WHERE activo = TRUE;
