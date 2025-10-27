<?php
session_start();
include("../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    // 🔹 Validación de campos vacíos
    if ($correo === '' || $contrasena === '') {
        $_SESSION['error_login'] = "❗ Completa todos los campos.";
        header("Location: ../views/login.php");
        exit;
    }

    // 🔹 Buscar usuario
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
            $loginValido = true;
        } elseif (password_verify($contrasena, $hash_contrasena)) {
            $loginValido = true;
        }

        if ($loginValido) {
            if ($estado === 'activo') {
                // ✅ Guardamos datos en sesión
                $_SESSION['id_usuario'] = $id_usuario;
                $_SESSION['nombre'] = $nombre;
                $_SESSION['tipo'] = $tipo;
                $_SESSION['foto'] = $fotografia && file_exists("../" . $fotografia)
                    ? "../" . $fotografia
                    : "../assets/Estilos/Imagenes/default-user.png";

                // 🔀 Redirección según tipo
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
                $_SESSION['error_login'] = "⚠️ Tu cuenta está inactiva o pendiente de activación.";
                header("Location: ../views/login.php");
                exit;
            }
        } else {
            $_SESSION['error_login'] = "❌ Usuario o contraseña incorrectos.";
            header("Location: ../views/login.php");
            exit;
        }
    } else {
        $_SESSION['error_login'] = "❌ Usuario o contraseña incorrectos.";
        header("Location: ../views/login.php");
        exit;
    }

    $stmt->close();
}

include("../includes/cerrarConexion.php");
?>
