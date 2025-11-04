<?php
// =====================================================
// Lógica: editarPerfil.php
// Descripción: Procesa la edición del perfil de usuario.
// Creado por: Jimena Jara y Fernanda Sibaja.
// =====================================================

session_start();
include("../includes/conexion.php");

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/login.php");
    exit;
}


$id = $_SESSION['id_usuario'];

$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$correo = trim($_POST['correo']);
$telefono = trim($_POST['telefono']);
$fecha_nac = $_POST['fecha_nacimiento'];

$nueva = $_POST['nueva'];
$confirmar = $_POST['confirmar'];
$contrasenaSql = "";

// Cambiar contraseña
if (!empty($nueva)) {
    if ($nueva !== $confirmar) {
        $_SESSION['mensaje'] = "⚠️ Las contraseñas no coinciden.";
        header("Location: ../views/editarPerfil.php");
        exit;
    }
    
    $hash = password_hash($nueva, PASSWORD_DEFAULT);
    $contrasenaSql = ", contrasena='$hash'";
}

// Foto actual
$fotoRuta = $_SESSION['foto'] ?? null;

// Subir nueva foto
if (!empty($_FILES['fotografia']['name'])) {

    // Validar tipo de archivo
    $ext = strtolower(pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png'])) {
        $_SESSION['mensaje'] = "❌ Solo JPG o PNG.";
        header("Location: ../views/editarPerfil.php");
        exit;
    }

    // Guardar archivo
    $nombreArchivo = time() . "_" . basename($_FILES["fotografia"]["name"]);
    $rutaServidor = "../uploads/usuarios/" . $nombreArchivo; 
    $rutaWeb = "uploads/usuarios/" . $nombreArchivo;         

    // Mover archivo
    if (move_uploaded_file($_FILES["fotografia"]["tmp_name"], $rutaServidor)) {
        $fotoRuta = $rutaWeb;
        $_SESSION['foto'] = $fotoRuta;
    } else {
        $_SESSION['mensaje'] = "❌ Error al guardar la imagen.";
        header("Location: ../views/editarPerfil.php");
        exit;
    }
}

// Actualizar BD
$sql = "UPDATE usuarios SET 
        nombre='$nombre',
        apellido='$apellido',
        correo='$correo',
        telefono='$telefono',
        fecha_nacimiento='$fecha_nac',
        fotografia='$fotoRuta'
        $contrasenaSql
        WHERE id_usuario=$id";

if ($conexion->query($sql)) {
    $_SESSION['nombre'] = $nombre;
    $_SESSION['correo'] = $correo;
    $_SESSION['foto'] = $fotoRuta;

    $_SESSION['mensaje'] = "✅ Cambios guardados correctamente";
} else {
    $_SESSION['mensaje'] = "❌ Error al actualizar";
}

// Redirigir con refresh para ver cambios
header("Location: ../views/editarPerfil.php?reload=" . time());
exit;
?>