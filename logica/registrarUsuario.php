<?php
// =====================================================
// Script: registrarUsuario.php (Lógica)
// Procesa el REGISTRO de usuarios con validación,
// subida de foto, hash de contraseña, activación email,
// y ahora INICIA SESIÓN + carga foto correcta ✅
// =====================================================

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

    if (!$nombre || !$apellido || !$cedula || !$fecha_nacimiento || !$correo || !$telefono) {
        $_SESSION['mensaje'] = "❌ Todos los campos son obligatorios.";
        header("Location: ../views/registro.php"); exit();
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensaje'] = "❌ Correo no válido.";
        header("Location: ../views/registro.php"); exit();
    }

    if ($contrasena !== $confirmar) {
        $_SESSION['mensaje'] = "❌ Las contraseñas no coinciden.";
        header("Location: ../views/registro.php"); exit();
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $contrasena)) {
        $_SESSION['mensaje'] = "❌ La contraseña debe tener mínimo 8 caracteres, una mayúscula y un número.";
        header("Location: ../views/registro.php"); exit();
    }

    $nac = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($nac)->y;

    if ($tipo === "chofer" && $edad < 18) {
        $_SESSION['mensaje'] = "🚫 Debes ser mayor de edad para registrarte como chofer.";
        header("Location: ../views/registro.php"); exit();
    }

    $verDup = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE cedula=? OR correo=? OR telefono=?");
    $verDup->bind_param("sss", $cedula, $correo, $telefono);
    $verDup->execute();
    if ($verDup->get_result()->num_rows > 0) {
        $_SESSION['mensaje'] = "⚠️ Usuario ya registrado.";
        header("Location: ../views/registro.php"); exit();
    }
    $verDup->close();

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $foto_ruta = "";
    if (!empty($_FILES['fotografia']['name'])) {

        $ext = strtolower(pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg','jpeg','png'])) {
            $_SESSION['mensaje'] = "❌ Solo se permiten imágenes JPG o PNG.";
            header("Location: ../views/registro.php"); exit();
        }

        if ($_FILES['fotografia']['size'] > 2 * 1024 * 1024) {
            $_SESSION['mensaje'] = "❌ La imagen debe ser menor a 2MB.";
            header("Location: ../views/registro.php"); exit();
        }

        $nuevoNombre = uniqid("user_") . ".$ext";
        $destino = "../uploads/usuarios/$nuevoNombre";

        if (move_uploaded_file($_FILES['fotografia']['tmp_name'], $destino)) {
            $foto_ruta = "uploads/usuarios/$nuevoNombre";
        }
    }

    $token = bin2hex(random_bytes(32));

    $stmt = $conexion->prepare("INSERT INTO usuarios 
        (nombre, apellido, cedula, fecha_nacimiento, correo, telefono, fotografia, contrasena, tipo, estado, token_activacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)");
        
    $stmt->bind_param("ssssssssss", 
        $nombre, $apellido, $cedula, $fecha_nacimiento,
        $correo, $telefono, $foto_ruta, $hash, $tipo, $token);

    if ($stmt->execute()) {

        enviarCorreoActivacion($correo, $nombre, $token);

        $_SESSION['mensaje'] = "✅ Registro exitoso. Ahora puedes iniciar sesión cuando desees.";

        // Volver al registro con mensaje
        header("Location: ../views/registro.php");
        exit();
    } 
    else {
        $_SESSION['mensaje'] = "❌ Error: " . $stmt->error;
        header("Location: ../views/registro.php");
        exit();
    }

    include("../includes/cerrarConexion.php");
}
?>