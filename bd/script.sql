CREATE DATABASE IF NOT EXISTS aventones;
USE aventones;

-- ======================================================
-- 🧍 TABLA DE USUARIOS
-- ======================================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    fotografia VARCHAR(255),
    contrasena VARCHAR(255) NOT NULL,
    tipo ENUM('administrador', 'chofer', 'pasajero') DEFAULT 'pasajero',
    estado ENUM('pendiente', 'activo', 'inactivo') DEFAULT 'pendiente',
    token_activacion VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ======================================================
-- 🚗 TABLA DE VEHÍCULOS
-- ======================================================
CREATE TABLE vehiculos (
    id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    id_chofer INT NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    placa VARCHAR(20) NOT NULL UNIQUE,
    color VARCHAR(30),
    anno INT, -- año del vehículo
    capacidad INT DEFAULT 4,
    fotografia VARCHAR(255),
    FOREIGN KEY (id_chofer) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- ======================================================
-- 🛣️ TABLA DE RIDES (VIAJES)
-- ======================================================
CREATE TABLE rides (
    id_ride INT AUTO_INCREMENT PRIMARY KEY,
    id_chofer INT NOT NULL,
    id_vehiculo INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    inicio VARCHAR(100) NOT NULL,
    fin VARCHAR(100) NOT NULL,
    hora TIME NOT NULL,
    dia VARCHAR(20) NOT NULL,
    costo DECIMAL(10,2) NOT NULL,
    espacios INT NOT NULL,
    FOREIGN KEY (id_chofer) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo) ON DELETE CASCADE
);

-- ======================================================
-- 📅 TABLA DE RESERVAS
-- ======================================================
CREATE TABLE reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_ride INT NOT NULL,
    id_pasajero INT NOT NULL,
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente','aceptada','rechazada','cancelada') DEFAULT 'pendiente',
    FOREIGN KEY (id_ride) REFERENCES rides(id_ride) ON DELETE CASCADE,
    FOREIGN KEY (id_pasajero) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- ======================================================
-- 👑 USUARIO ADMIN POR DEFECTO
-- ======================================================
INSERT INTO usuarios (
    nombre, apellido, cedula, fecha_nacimiento, correo, telefono, contrasena, tipo, estado
) VALUES (
    'Admin', 'Principal', '0001', '1990-01-01', 'admin@aventones.com', '88888888', 'Admin123', 'administrador', 'activo'
);
