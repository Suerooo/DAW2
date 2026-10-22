
CREATE DATABASE IF NOT EXISTS rentacar
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE rentacar;

-- ====================================
-- REINICIAR TABLAS
-- ====================================

DROP TABLE IF EXISTS vehicle_blocks;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS users;

-- ====================================
-- 1. USUARIOS
-- ====================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') NOT NULL DEFAULT 'client'
) ENGINE=InnoDB;

-- ====================================
-- 2. CLIENTES
-- ====================================

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    dni VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(150) NOT NULL,
    birthdate DATE NOT NULL,
    driver_license VARCHAR(255),

    CONSTRAINT fk_clients_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ====================================
-- 3. VEHÍCULOS
-- ====================================

CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate VARCHAR(15) NOT NULL UNIQUE,
    brand VARCHAR(80) NOT NULL,
    model VARCHAR(80) NOT NULL,

    color ENUM(
        'blanco', 'negro', 'gris', 'plata',
        'rojo', 'azul', 'verde', 'amarillo', 'otro'
    ) NOT NULL,

    doors TINYINT UNSIGNED NOT NULL,
    seats TINYINT UNSIGNED NOT NULL,

    transmission ENUM(
        'manual', 'automatic'
    ) NOT NULL,

    motorization ENUM(
        'gasolina', 'diesel', 'hibrido',
        'hibrido_enchufable', 'electrico', 'otro'
    ) NOT NULL,

    vehicle_class ENUM(
        'A', 'B', 'C', 'D', 'E', 'F', 'G'
    ) NOT NULL,

    base_price DECIMAL(10,2) NOT NULL,

    active BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT chk_vehicle_doors
        CHECK (doors IN (2, 3, 4, 5)),

    CONSTRAINT chk_vehicle_seats
        CHECK (seats BETWEEN 2 AND 9),

    CONSTRAINT chk_vehicle_price
        CHECK (base_price > 0),

    CONSTRAINT chk_vehicle_active
        CHECK (active IN (0, 1)),

    INDEX idx_vehicle_catalog (
        active, vehicle_class, brand, model
    )
) ENGINE=InnoDB;

-- ====================================
-- 4. CIERRES DE RESERVA
-- ====================================

CREATE TABLE vehicle_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(255),

    CONSTRAINT fk_blocks_vehicle
        FOREIGN KEY (vehicle_id)
        REFERENCES vehicles(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_block_dates
        CHECK (end_date >= start_date),

    INDEX idx_vehicle_dates (
        vehicle_id, start_date, end_date
    )
) ENGINE=InnoDB;

-- ====================================
-- INSERTS DE PRUEBA
-- ====================================

START TRANSACTION;

-- USUARIOS
-- Contraseña de todos: Prueba123!

INSERT INTO users (id, email, password, role) VALUES

(1, 'admin@rentacar.example.com',
'$2y$12$2aBpA/CS6XO5ZrLeNBUZHuc76xcLL6wPVXujakRC3yojYr9IyIs1.',
'admin'),

(2, 'ana@example.com',
'$2y$12$oRIVJi4WDa0ZygO9EgJ6nOCWKwXbFDJv1x5df470eio0Ia6Ki3n6u',
'client'),

(3, 'carlos@example.com',
'$2y$12$ShOvFoVj5YwJh5tuzv63EeLjDxxf1r/bdiQPVHB7crnRYU57OMwfy',
'client'),

(4, 'maria@example.com',
'$2y$12$5nQfhRSsPN32uFhLbFuggeUnwi7L3cwOcAXt9rZ2MNhUC65dx1rju',
'client'),

(5, 'pedro@example.com',
'$2y$12$b94F6ramw6iBmlMrq0.ZGefzZ5z9Um8gIqicanj39je.wGAWSnZFm',
'client'),

(6, 'laura@example.com',
'$2y$12$8o2yWsi52kpH2m5a0CZcEezwZVii13CGB8POA67e1paX9dN5oanJa',
'client'),

(7, 'javier@example.com',
'$2y$12$Nst/RlcyLj4v29jbG8GdEuHG1frZlwVyVEo2KMEInIPWSLUp9btJG',
'client');


-- CLIENTES

INSERT INTO clients
(user_id, dni, name, surname, birthdate, driver_license)
VALUES
(2, '00000000T', 'Ana', 'Garcia Lopez',
 '1995-04-12', 'licenses/ana.jpg'),

(3, '00000001R', 'Carlos', 'Perez Martin',
 '1988-09-23', 'licenses/carlos.jpg'),

(4, '00000002W', 'Maria', 'Rodriguez Diaz',
 '2000-01-15', 'licenses/maria.jpg'),

(5, '00000003A', 'Pedro', 'Hernandez Cruz',
 '1992-06-30', NULL),

(6, '00000004G', 'Laura', 'Suarez Ramos',
 '1998-11-08', 'licenses/laura.jpg'),

(7, '00000005M', 'Javier', 'Morales Perez',
 '1985-03-19', NULL);


-- VEHÍCULOS

INSERT INTO vehicles
(id, plate, brand, model, color, doors, seats,
 transmission, motorization, vehicle_class,
 base_price, active)
VALUES

-- Clase A
(1, '1000TST', 'Fiat', '500', 'blanco', 3, 4,
 'manual', 'gasolina', 'A', 28.50, 1),

(2, '1001TST', 'Fiat', '500', 'rojo', 3, 4,
 'manual', 'gasolina', 'A', 28.50, 1),

(3, '1002TST', 'Kia', 'Picanto', 'gris', 5, 5,
 'manual', 'gasolina', 'A', 29.90, 1),

-- Clase B
(4, '2000TST', 'Seat', 'Ibiza', 'blanco', 5, 5,
 'manual', 'gasolina', 'B', 35.00, 1),

(5, '2001TST', 'Seat', 'Ibiza', 'azul', 5, 5,
 'automatic', 'gasolina', 'B', 42.00, 1),

(6, '2002TST', 'Volkswagen', 'Polo', 'negro', 5, 5,
 'manual', 'gasolina', 'B', 39.00, 1),

-- Clase C
(7, '3000TST', 'Renault', 'Clio', 'gris', 5, 5,
 'automatic', 'hibrido', 'C', 49.00, 1),

(8, '3001TST', 'Toyota', 'Corolla', 'plata', 5, 5,
 'automatic', 'hibrido', 'C', 55.00, 1),

-- Clase D
(9, '4000TST', 'Dacia', 'Duster', 'blanco', 5, 5,
 'manual', 'gasolina', 'D', 58.00, 1),

(10, '4001TST', 'Hyundai', 'Tucson', 'gris', 5, 5,
 'automatic', 'hibrido', 'D', 69.00, 1),

-- Clase E
(11, '5000TST', 'Peugeot', '5008', 'azul', 5, 7,
 'automatic', 'diesel', 'E', 75.00, 1),

-- Clase F
(12, '6000TST', 'Mercedes', 'Vito', 'negro', 4, 9,
 'manual', 'diesel', 'F', 110.00, 1),

-- Clase G
(13, '7000TST', 'Tesla', 'Model 3', 'blanco', 4, 5,
 'automatic', 'electrico', 'G', 125.00, 1),

-- Inactivos
(14, '9000TST', 'Nissan', 'Micra', 'rojo', 5, 5,
 'manual', 'gasolina', 'B', 32.00, 0),

(15, '9001TST', 'Opel', 'Corsa', 'gris', 5, 5,
 'manual', 'gasolina', 'B', 30.00, 0);


-- CIERRES DE RESERVA
-- Fechas relativas al día en el que ejecutes el script.

INSERT INTO vehicle_blocks
(vehicle_id, start_date, end_date, reason)
VALUES

-- Cierre pasado
(1,
 DATE_SUB(CURDATE(), INTERVAL 30 DAY),
 DATE_SUB(CURDATE(), INTERVAL 28 DAY),
 'Mantenimiento pasado'),

-- Dos cierres futuros del mismo coche
(1,
 DATE_ADD(CURDATE(), INTERVAL 10 DAY),
 DATE_ADD(CURDATE(), INTERVAL 12 DAY),
 'Reserva manual'),

(1,
 DATE_ADD(CURDATE(), INTERVAL 40 DAY),
 DATE_ADD(CURDATE(), INTERVAL 42 DAY),
 'Mantenimiento programado'),

-- Otro vehículo bloqueado los mismos días
(2,
 DATE_ADD(CURDATE(), INTERVAL 10 DAY),
 DATE_ADD(CURDATE(), INTERVAL 12 DAY),
 'Uso interno'),

-- Bloqueo que incluye el día actual
(4,
 DATE_SUB(CURDATE(), INTERVAL 1 DAY),
 DATE_ADD(CURDATE(), INTERVAL 1 DAY),
 'Averia actual'),

-- Bloqueos futuros
(4,
 DATE_ADD(CURDATE(), INTERVAL 40 DAY),
 DATE_ADD(CURDATE(), INTERVAL 45 DAY),
 'Uso interno'),

(7,
 DATE_ADD(CURDATE(), INTERVAL 40 DAY),
 DATE_ADD(CURDATE(), INTERVAL 42 DAY),
 'Revision tecnica'),

(7,
 DATE_ADD(CURDATE(), INTERVAL 70 DAY),
 DATE_ADD(CURDATE(), INTERVAL 82 DAY),
 'Cierre prolongado'),

(10,
 DATE_ADD(CURDATE(), INTERVAL 70 DAY),
 DATE_ADD(CURDATE(), INTERVAL 75 DAY),
 'Mantenimiento'),

-- Bloqueo de un solo día
(13,
 DATE_ADD(CURDATE(), INTERVAL 11 DAY),
 DATE_ADD(CURDATE(), INTERVAL 11 DAY),
 'Inspeccion de un dia'),

-- Bloqueo de vehículo inactivo
(14,
 DATE_SUB(CURDATE(), INTERVAL 1 DAY),
 DATE_ADD(CURDATE(), INTERVAL 9 DAY),
 'Vehiculo inactivo');

COMMIT;
