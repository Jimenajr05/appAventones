<?php
session_start();
include("../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($correo === '' || $contrasena === '') {
        echo "<p style='color:red; text-align:center;'>❗ Completa todos los campos.</p>";
        echo "<p style='text-align:center;'><a href='../views/login.php'>Volver</a></p>";
        include("../includes/cerrarConexion.php");
        exit;
    }

    // 🔹 Ahora también obtenemos la fotografía
    $stmt = $conexion->prepare("SELECT id_usuario, nombre, tipo, contrasena, estado, fotografia FROM usuarios WHERE correo = ? LIMIT 1");
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id_usuario, $nombre, $tipo, $hash_contrasena, $estado, $fotografia);
        $stmt->fetch();

        // 🔐 Verificación de contraseña
        $loginValido = false;
        if ($tipo === 'administrador' && $contrasena === $hash_contrasena) {
            $loginValido = true; // los admin pueden tener clave sin hash
        } elseif (password_verify($contrasena, $hash_contrasena)) {
            $loginValido = true;
        }

        if ($loginValido) {
            if ($estado === 'activo') {
                // ✅ Guardamos todos los datos en sesión
                $_SESSION['id_usuario'] = $id_usuario;
                $_SESSION['nombre'] = $nombre;
                $_SESSION['tipo'] = $tipo;
                $_SESSION['foto'] = $fotografia && file_exists("../" . $fotografia)
                    ? "../" . $fotografia
                    : "../assets/Estilos/Imagenes/default-user.png";

                // 🔀 Redirección según el tipo de usuario
                switch ($tipo) {
                    case 'chofer':
                        header("Location: ../views/chofer.php");
                        break;
                    case 'pasajero':
                        header("Location: ../views/pasajero.php");
                        break;
                    case 'administrador':
                        header("Location: ../views/administrador.php");
                        break;
                    default:
                        header("Location: ../views/dashboard.php");
                        break;
                }
                $stmt->close();
                include("../includes/cerrarConexion.php");
                exit;
            } else {
                echo "<p style='color:red; text-align:center;'>⚠ Tu cuenta está pendiente o inactiva.</p>";
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

    $stmt->close();
}

include("../includes/cerrarConexion.php");
?>