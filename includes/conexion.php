<?php
    // =====================================================
    // Script: conexion.php
    // Descripción: Establece la conexión a la base de datos MySQL.
    // Creado por: Jimena Jara y Fernanda Sibaja.
    // =====================================================

    $servidor = "localhost";
    $usuario = "root";
    $clave = "";
    $baseDatos = "aventones";

    $conexion = mysqli_connect($servidor, $usuario, $clave, $baseDatos);

    if (!$conexion) {
        die("Error al conectar: " . mysqli_connect_error());
    }
?>