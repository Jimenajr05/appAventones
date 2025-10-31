<!--
    // =====================================================
    // Script: conexion.php
    // Descripción: Establece la conexión con la base de datos 'aventones'.
    // En caso de fallo, detiene la ejecución y muestra un error.
    // By: Jimena y Fernanda
    // =====================================================
-->
<?php
    $servidor = "localhost";
    $usuario = "root";
    $clave = "";
    $baseDatos = "aventones";

    $conexion = mysqli_connect($servidor, $usuario, $clave, $baseDatos);

    if (!$conexion) {
        die("Error al conectar: " . mysqli_connect_error());
    }
?>