
CREATE DATABASE online_shop

USE online_shop;

CREATE TABLE category (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL
);

CREATE TABLE product (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(120) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    imagen VARCHAR(255) DEFAULT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1 CHECK (active IN (0, 1)),
    added_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category
        FOREIGN KEY (category_id) REFERENCES category(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE client (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(150) DEFAULT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    register_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    state ENUM('pendiente', 'confirmado') NOT NULL DEFAULT 'pendiente',
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00 CHECK (total >= 0), 
    shipping_address VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_order_client
        FOREIGN KEY (client_id) REFERENCES client(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE details_order (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    amount INT UNSIGNED NOT NULL CHECK (amount > 0),
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 CHECK (unit_price >= 0),
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00 CHECK (subtotal >= 0),
    CONSTRAINT fk_details_order
        FOREIGN KEY (order_id) REFERENCES order(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_details_order
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

INSERT IGNORE INTO category (id, name, description) VALUES
(1, 'Ratones', 'Ratones con cable e inalámbricos'),
(2, 'Teclados', 'Teclados de oficina y para juegos'),
(3, 'Memoria RAM', 'Módulos y kits de memoria DDR4 y DDR5'),
(4, 'Fuentes de alimentación', 'Fuentes de alimentación para PC'),
(5, 'Procesadores', 'Procesadores para ordenadores de sobremesa'),
(6, 'Tarjetas gráficas', 'Tarjetas gráficas dedicadas'),
(7, 'Almacenamiento SSD', 'Unidades de almacenamiento en estado sólido'),
(8, 'Placas base', 'Placas base para diferentes plataformas');

INSERT IGNORE INTO productos
(id, category_id, name, brand, model, description, price, stock, imagen) VALUES
(1, 1, 'Logitech G305 LIGHTSPEED', 'Logitech', 'G305 LIGHTSPEED', 'Ratón inalámbrico con sensor óptico.', 39.90, 15, NULL),
(2, 1, 'Razer DeathAdder Essential', 'Razer', 'DeathAdder Essential', 'Ratón con cable y diseño ergonómico.', 24.95, 0, NULL),
(3, 1, 'Logitech M185', 'Logitech', 'M185', 'Ratón inalámbrico compacto para uso diario.', 14.50, 24, NULL),

(4, 2, 'Logitech K120', 'Logitech', 'K120', 'Teclado de membrana con conexión USB.', 13.95, 30, NULL),
(5, 2, 'Corsair K70 RGB PRO', 'Corsair', 'K70 RGB PRO', 'Teclado mecánico con iluminación RGB.', 139.90, 6, NULL),
(6, 2, 'Keychron K2', 'Keychron', 'K2', 'Teclado mecánico compacto.', 89.00, 0, NULL),

(7, 3, 'Kingston FURY Beast 16 GB DDR4 3200', 'Kingston', 'FURY Beast DDR4 3200', 'Módulo de 16 GB de memoria DDR4.', 45.90, 20, NULL),
(8, 3, 'Corsair Vengeance 32 GB DDR5 6000', 'Corsair', 'Vengeance DDR5 6000', 'Kit de 32 GB de memoria DDR5.', 115.00, 9, NULL),
(9, 3, 'Crucial 8 GB DDR4 3200', 'Crucial', 'DDR4 3200 8GB', 'Módulo de 8 GB de memoria DDR4.', 22.90, 25, NULL),

(10, 4, 'Corsair RM750e 750 W', 'Corsair', 'RM750e', 'Fuente de alimentación modular de 750 W.', 109.90, 12, NULL),
(11, 4, 'MSI MAG A650BN 650 W', 'MSI', 'MAG A650BN', 'Fuente de alimentación de 650 W.', 62.50, 0, NULL),
(12, 4, 'be quiet! Pure Power 12 M 850 W', 'be quiet!', 'Pure Power 12 M', 'Fuente de alimentación modular de 850 W.', 139.00, 4, NULL),

(13, 5, 'AMD Ryzen 5 5600', 'AMD', 'Ryzen 5 5600', 'Procesador de sobremesa de 6 núcleos.', 119.00, 18, NULL),
(14, 5, 'Intel Core i5-12400F', 'Intel', 'Core i5-12400F', 'Procesador de sobremesa de 6 núcleos.', 138.90, 13, NULL),
(15, 5, 'AMD Ryzen 7 7700', 'AMD', 'Ryzen 7 7700', 'Procesador de sobremesa de 8 núcleos.', 289.00, 5, NULL),

(16, 6, 'MSI GeForce RTX 4060 VENTUS 2X 8 GB', 'MSI', 'RTX 4060 VENTUS 2X', 'Tarjeta gráfica dedicada con 8 GB de memoria.', 329.00, 3, NULL),
(17, 6, 'Sapphire PULSE Radeon RX 7600 8 GB', 'Sapphire', 'PULSE RX 7600', 'Tarjeta gráfica dedicada con 8 GB de memoria.', 279.00, 8, NULL),
(18, 6, 'ASUS Dual GeForce RTX 4070 SUPER 12 GB', 'ASUS', 'Dual RTX 4070 SUPER', 'Tarjeta gráfica dedicada con 12 GB de memoria.', 649.00, 0, NULL),

(19, 7, 'Samsung 990 EVO 1 TB', 'Samsung', '990 EVO 1TB', 'Unidad SSD M.2 NVMe de 1 TB.', 99.90, 17, NULL),
(20, 7, 'Crucial P3 Plus 2 TB', 'Crucial', 'P3 Plus 2TB', 'Unidad SSD M.2 NVMe de 2 TB.', 119.00, 10, NULL),
(21, 7, 'Kingston NV2 500 GB', 'Kingston', 'NV2 500GB', 'Unidad SSD M.2 NVMe de 500 GB.', 36.50, 25, NULL),

(22, 8, 'ASUS PRIME B550M-A', 'ASUS', 'PRIME B550M-A', 'Placa base micro-ATX para procesadores AMD compatibles.', 99.00, 9, NULL),
(23, 8, 'MSI B650 GAMING PLUS WIFI', 'MSI', 'B650 GAMING PLUS WIFI', 'Placa base ATX con conexión Wi-Fi.', 179.00, 6, NULL),
(24, 8, 'Gigabyte B760M DS3H DDR4', 'Gigabyte', 'B760M DS3H DDR4', 'Placa base micro-ATX con ranuras para DDR4.', 119.00, 0, NULL);

-- CONSULTAS PARA IMPLEMENTAR LAS VISTAS EN PHP (EJEMPLOS)
--
-- Catálogo agrupado por categoría:
-- SELECT c.id AS categoria_id, c.nombre AS categoria,
--        p.id AS producto_id, p.nombre, p.precio, p.imagen, p.stock
-- FROM categorias AS c
-- LEFT JOIN productos AS p ON p.categoria_id = c.id AND p.activo = 1
-- ORDER BY c.nombre, p.nombre;
--
-- Detalle de un producto (en PHP, sustituir ? con un parámetro PDO):
-- SELECT p.*, c.nombre AS categoria
-- FROM productos p JOIN categorias c ON c.id = p.categoria_id
-- WHERE p.id = ? AND p.activo = 1;
--
-- Búsqueda AJAX, devolviendo como máximo 8 resultados (usar parámetros PDO):
-- SELECT id, nombre, precio, imagen, stock
-- FROM productos
-- WHERE activo = 1 AND (nombre LIKE CONCAT('%', ?, '%')
--     OR marca LIKE CONCAT('%', ?, '%') OR modelo LIKE CONCAT('%', ?, '%'))
-- ORDER BY nombre LIMIT 8;
--
-- Pedidos confirmados de un cliente:
-- SELECT * FROM pedidos
-- WHERE cliente_id = ? AND estado = 'confirmado'
-- ORDER BY fecha_pedido DESC;
--
-- El carrito puede guardarse en una cookie como pares producto_id/cantidad.
-- Las consultas o categorías visitadas se pueden guardar en otra cookie.
-- Nunca confiar en precios o stock de cookies: se consultan a productos.
