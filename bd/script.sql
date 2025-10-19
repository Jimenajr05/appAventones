CREATE DATABASE aventones;
USE aventones;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    fotografia VARCHAR(255),
    contrasena VARCHAR(255) NOT NULL,
    tipo ENUM('administrador', 'chofer', 'pasajero') DEFAULT 'pasajero',
    estado ENUM('pendiente', 'activo', 'inactivo') DEFAULT 'pendiente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehiculos (
    id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    id_chofer INT NOT NULL,
    placa VARCHAR(20) UNIQUE NOT NULL,
    color VARCHAR(50),
    marca VARCHAR(50),
    modelo VARCHAR(50),
    anno INT,
    capacidad INT,
    fotografia VARCHAR(255),
    FOREIGN KEY (id_chofer) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE rides (
    id_ride INT AUTO_INCREMENT PRIMARY KEY,
    id_chofer INT NOT NULL,
    id_vehiculo INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    inicio VARCHAR(100) NOT NULL,
    fin VARCHAR(100) NOT NULL,
    hora TIME,
    dia VARCHAR(20),
    costo DECIMAL(10,2) DEFAULT 0.00,
    espacios INT DEFAULT 1,
    FOREIGN KEY (id_chofer) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,
    FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo)
        ON DELETE CASCADE
);

CREATE TABLE reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_ride INT NOT NULL,
    id_pasajero INT NOT NULL,
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente','aceptada','rechazada','cancelada') DEFAULT 'pendiente',
    FOREIGN KEY (id_ride) REFERENCES rides(id_ride)
        ON DELETE CASCADE,
    FOREIGN KEY (id_pasajero) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);