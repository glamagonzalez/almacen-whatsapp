-- Actualizar estructura de tabla productos
-- Ejecutar en phpMyAdmin o MySQL

-- Verificar estructura actual
DESCRIBE productos;

-- Las columnas YA EXISTEN, solo verificamos que estén bien
-- Si necesitas modificar alguna columna existente:
-- ALTER TABLE productos MODIFY COLUMN precio DECIMAL(10,2) DEFAULT 0.00;
-- ALTER TABLE productos MODIFY COLUMN stock INT DEFAULT 0;
-- etc...

-- Opción 2: Si prefieres recrear la tabla completa (¡CUIDADO! Esto borra los datos)
/*
DROP TABLE IF EXISTS productos;

CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    categoria VARCHAR(100) DEFAULT 'General',
    imagen VARCHAR(500) DEFAULT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

-- Ver cuántos productos hay
SELECT COUNT(*) as total_productos FROM productos WHERE activo = 1;

-- Ver los productos actuales (usando nombres correctos de columnas)
SELECT id, nombre, precio_venta, stock_actual, activo FROM productos;

-- Si NO hay productos, insertar ejemplos:
-- DESCOMENTAR Y EJECUTAR SI LA TABLA ESTÁ VACÍA:
/*
INSERT INTO productos (nombre, descripcion, precio_compra, precio_venta, stock_actual, stock_minimo, categoria, imagen_url, activo) VALUES
('Coca Cola 2.25L', 'Gaseosa sabor cola 2.25 litros', 280.00, 350.00, 50, 10, 'Bebidas', 'https://via.placeholder.com/300x200?text=Coca+Cola', 1),
('Arroz Gallo de Oro 1kg', 'Arroz blanco tipo doble carolina', 220.00, 280.00, 30, 10, 'Almacén', 'https://via.placeholder.com/300x200?text=Arroz', 1),
('Detergente Magistral 500ml', 'Detergente líquido para ropa', 350.00, 450.00, 20, 5, 'Limpieza', 'https://via.placeholder.com/300x200?text=Detergente', 1);
*/
