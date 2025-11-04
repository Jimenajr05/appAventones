<?php
// =====================================================
// Script: activar.php (Lógica)
// =====================================================

include("../includes/conexion.php");

if (!isset($_GET['token'])) {
    header("Location: ../views/activar.php?status=missing");
    exit;
}

$token = trim($_GET['token']);

if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    header("Location: ../views/activar.php?status=invalid");
    exit;
}

$stmt = $conexion->prepare("
    SELECT id_usuario 
    FROM usuarios 
    WHERE token_activacion = ? AND estado = 'pendiente'
");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $u = $res->fetch_assoc();
    $id = $u['id_usuario'];

    $update = $conexion->prepare("
        UPDATE usuarios SET estado='activo', token_activacion=NULL 
        WHERE id_usuario = ?
    ");
    $update->bind_param("i", $id);

    if ($update->execute()) {
        header("Location: ../views/activar.php?status=ok");
        exit;
    }
}

header("Location: ../views/activar.php?status=used");
exit;
?>