<?php
session_start();
include("../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT * FROM usuarios WHERE correo = '$correo' AND contrasena = '$contrasena'";
    $resultado = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($resultado) === 1) {
        $usuario = mysqli_fetch_assoc($resultado);

        if ($usuario['estado'] === 'activo') {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['tipo'] = $usuario['tipo'];

            header("Location: ../views/Dashboard.php");
            exit;
        } else {
            echo "<p style='color:red; text-align:center;'>⚠️ Tu cuenta está pendiente o inactiva.</p>";
            echo "<p style='text-align:center;'><a href='../views/login.php'>Volver</a></p>";
        }
    } else {
        echo "<p style='color:red; text-align:center;'>❌ Usuario o contraseña incorrectos.</p>";
        echo "<p style='text-align:center;'><a href='../views/login.php'>Volver</a></p>";
    }
}

include("../includes/cerrarConexion.php");
?>