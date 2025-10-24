<?php
session_start();
include("../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic input validation / sanitization
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

    if ($correo === '' || $contrasena === '') {
        echo "<p style='color:red; text-align:center;'>❗ Completa todos los campos.</p>";
        echo "<p style='text-align:center;'><a href='../views/login.php'>Volver</a></p>";
        include("../includes/cerrarConexion.php");
        exit;
    }

    // Prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($conexion, "SELECT id_usuario, nombre, tipo, contrasena, estado FROM usuarios WHERE correo = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $correo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $id_usuario, $nombre, $tipo, $hash_contrasena, $estado);
            mysqli_stmt_fetch($stmt);

            // Verify password - temporary fix for admin
            if (($tipo === 'administrador' && $contrasena === $hash_contrasena) || 
                ($tipo !== 'administrador' && password_verify($contrasena, $hash_contrasena))) {
                if ($estado === 'activo') {
                    $_SESSION['id_usuario'] = $id_usuario;
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['tipo'] = $tipo;

                    mysqli_stmt_close($stmt);
                    include("../includes/cerrarConexion.php");
                    header("Location: ../views/dashboard.php");
                    exit;
                } else {
                    echo "<p style='color:red; text-align:center;'>⚠️ Tu cuenta está pendiente o inactiva.</p>";
                    echo "<p style='text-align:center;'><a href='../views/login.php'>Volver</a></p>";
                }
            } else {
                echo "<p style='color:red; text-align:center;'>❌ Usuario o contraseña incorrectos.</p>";
                echo "<p style='text-align:center;'><a href='../views/login.php'>Volver</a></p>";
            }
        } else {
            echo "<p style='color:red; text-align:center;'>❌ Usuario o contraseña incorrectos.</p>";
            echo "<p style='text-align:center;'><a href='../views/login.php'>Volver</a></p>";
        }

        mysqli_stmt_close($stmt);
    } else {
        // Fallback error
        echo "<p style='color:red; text-align:center;'>6A8 Error del servidor. Intenta de nuevo más tarde.</p>";
    }
}

include("../includes/cerrarConexion.php");
?>