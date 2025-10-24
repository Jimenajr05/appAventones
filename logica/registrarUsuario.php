<?php
session_start();
include("../includes/conexion.php");
include("../includes/enviarCorreo.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tipo             = trim($_POST['tipo']);
    $nombre           = trim($_POST['nombre']);
    $apellido         = trim($_POST['apellido']);
    $cedula           = trim($_POST['cedula']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $correo           = trim($_POST['correo']);
    $telefono         = trim($_POST['telefono']);
    $contrasena       = $_POST['contrasena'];
    $confirmar        = $_POST['confirmar'];

    // =============================
    // 🧾 VALIDACIONES GENERALES
    // =============================
    if (!$nombre || !$apellido || !$cedula || !$fecha_nacimiento || !$correo || !$telefono) {
        $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>❌ Todos los campos son obligatorios.</p>";
        header("Location: ../views/registro.php");
        exit();
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>❌ El correo electrónico no es válido.</p>";
        header("Location: ../views/registro.php");
        exit();
    }
    if ($contrasena !== $confirmar) {
        $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>❌ Las contraseñas no coinciden.</p>";
        header("Location: ../views/registro.php");
        exit();
    }
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $contrasena)) {
        $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>❌ La contraseña debe tener al menos 8 caracteres, una mayúscula y un número.</p>";
        header("Location: ../views/registro.php");
        exit();
    }

    // =============================
    // 🎂 VALIDACIÓN DE EDAD
    // =============================
    $nac = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($nac)->y;

    if ($nac > $hoy || $nac < new DateTime('1900-01-01')) {
        $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>❌ La fecha de nacimiento no es válida.</p>";
        header("Location: ../views/registro.php");
        exit();
    }

    if ($tipo === "chofer" && $edad < 18) {
        $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>🚫 Debes ser mayor de edad para registrarte como chofer.</p>";
        header("Location: ../views/registro.php");
        exit();
    }

    // =============================
    // 🔍 VALIDAR DUPLICADOS
    // =============================
    $verDup = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE cedula=? OR correo=? OR telefono=?");
    $verDup->bind_param("sss", $cedula, $correo, $telefono);
    $verDup->execute();
    if ($verDup->get_result()->num_rows > 0) {
        $verDup->close();
        include("../includes/cerrarConexion.php");
        $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>⚠️ Ya existe un usuario con la misma cédula, correo o teléfono.</p>";
        header("Location: ../views/registro.php");
        exit();
    }
    $verDup->close();

    // =============================
    // 🔐 Hash de contraseña
    // =============================
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // =============================
    // 📸 Imagen opcional
    // =============================
    $foto_ruta = "";
    if (!empty($_FILES['fotografia']['name'])) {
        $ext = strtolower(pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            include("../includes/cerrarConexion.php");
            $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>❌ Solo se permiten imágenes JPG o PNG.</p>";
            header("Location: ../views/registro.php");
            exit();
        }

        if ($_FILES['fotografia']['size'] > 2 * 1024 * 1024) {
            include("../includes/cerrarConexion.php");
            $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>❌ La imagen no debe superar los 2 MB.</p>";
            header("Location: ../views/registro.php");
            exit();
        }

        $nuevoNombre = uniqid("user_") . ".$ext";
        $destino = "../uploads/usuarios/$nuevoNombre";
        if (move_uploaded_file($_FILES['fotografia']['tmp_name'], $destino)) {
            $foto_ruta = "uploads/usuarios/$nuevoNombre";
        } else {
            include("../includes/cerrarConexion.php");
            $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>❌ Error al subir la imagen.</p>";
            header("Location: ../views/registro.php");
            exit();
        }
    }

    // =============================
    // 🧩 Token de activación
    // =============================
    $token = bin2hex(random_bytes(32));

    // =============================
    // 💾 Insertar usuario
    // =============================
    $stmt = $conexion->prepare("INSERT INTO usuarios 
        (nombre, apellido, cedula, fecha_nacimiento, correo, telefono, fotografia, contrasena, tipo, estado, token_activacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)");
    $stmt->bind_param("ssssssssss", $nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $foto_ruta, $hash, $tipo, $token);

    if ($stmt->execute()) {
        // ✉️ Enviar correo de activación
        if (enviarCorreoActivacion($correo, $nombre, $token)) {
            $_SESSION['mensaje'] = "<p style='color:green;text-align:center;'>✅ Registro exitoso. Revisa tu correo para activar tu cuenta.</p>";
        } else {
            $_SESSION['mensaje'] = "<p style='color:orange;text-align:center;'>⚠️ Usuario creado, pero no se pudo enviar el correo.</p>";
        }
    } else {
        $_SESSION['mensaje'] = "<p style='color:red;text-align:center;'>❌ Error al registrar: " . htmlspecialchars($stmt->error) . "</p>";
    }

    $stmt->close();

    // 🔚 Cerrar conexión
    include("../includes/cerrarConexion.php");
    
    header("Location: ../views/registro.php");
    exit();
}
?>
