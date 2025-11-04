<?php
// =====================================================
// Lógica: activar.php
// Descripción: Activa la cuenta de usuario mediante un token enviado por correo.
// Creado por: Jimena Jara y Fernanda Sibaja.
// =====================================================

include("../includes/conexion.php");

// Validar existencia del token en la URL
if (!isset($_GET['token'])) {
    header("Location: ../views/activar.php?status=missing");
    exit;
}

$token = trim($_GET['token']);

// Validar formato del token (64 caracteres hexadecimales)
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    header("Location: ../views/activar.php?status=invalid");
    exit;
}

// Buscar usuario con token válido y estado pendiente
$stmt = $conexion->prepare("
    SELECT id_usuario 
    FROM usuarios 
    WHERE token_activacion = ? AND estado = 'pendiente'
");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

// Si existe usuario asociado al token, activar cuenta
if ($res->num_rows > 0) {
    $u = $res->fetch_assoc();
    $id = $u['id_usuario'];

    $update = $conexion->prepare("
        UPDATE usuarios 
        SET estado='activo', token_activacion=NULL 
        WHERE id_usuario = ?
    ");
    $update->bind_param("i", $id);

    if ($update->execute()) {
        header("Location: ../views/activar.php?status=ok");
        exit;
    }
}

// Token inválido o ya usado
header("Location: ../views/activar.php?status=used");
exit;
?>
