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