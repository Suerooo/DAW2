
CREATE DATABASE IF NOT EXISTS rentacar;

USE rentacar;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') NOT NULL DEFAULT 'client'
);

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    dni VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(150) NOT NULL,
    birthdate DATE NOT NULL,
    driver_license VARCHAR(255),

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate VARCHAR(15) NOT NULL UNIQUE,
    brand VARCHAR(80) NOT NULL,
    model VARCHAR(80) NOT NULL,
    color VARCHAR(50) NOT NULL,
    doors TINYINT UNSIGNED NOT NULL,
    seats TINYINT UNSIGNED NOT NULL,
    transmission ENUM('manual', 'automatic') NOT NULL,
    motorization VARCHAR(50) NOT NULL,
    vehicle_class CHAR(1) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE
);


ALTER TABLE vehicles
ADD COLUMN base_price DECIMAL(10,2) NOT NULL DEFAULT 0.00;


CREATE TABLE vehicle_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,

    vehicle_id INT NOT NULL,

    start_date DATE NOT NULL,
    end_date DATE NOT NULL,

    reason VARCHAR(255),

    FOREIGN KEY (vehicle_id)
        REFERENCES vehicles(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_block_dates
        CHECK (end_date >= start_date),

    INDEX idx_vehicle_dates (
        vehicle_id,
        start_date,
        end_date
    )
);
