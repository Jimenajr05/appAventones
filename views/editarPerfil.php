<?php
session_start();
include("../includes/conexion.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['id_usuario'];
$result = $conexion->query("SELECT * FROM usuarios WHERE id_usuario=$id");
$usuario = $result->fetch_assoc();

// Foto actual
$foto = (!empty($_SESSION['foto']))
    ? "../" . $_SESSION['foto'] . "?v=" . time()
    : "../assets/Estilos/Imagenes/default.png";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil | Aventones</title>
    <link rel="stylesheet" href="../assets/Estilos/editarPerfil.css">
</head>
<body>

<header>
    <img src="../assets/Estilos/Imagenes/logo.png" width="170">
    <h1>Bienvenido a <span class="resaltado">Aventones.com</span></h1>
    <p>Tu mejor opción para viajar seguros</p>
</header>

<section class="perfil-container">

    <h2>Editar Perfil</h2>

    <form action="../logica/editarPerfil.php" method="POST" enctype="multipart/form-data">

        <div class="perfil-grid">

            <div class="col-foto">
                <img src="<?= htmlspecialchars($foto); ?>" class="foto-perfil">
            </div>

            <div class="col-datos">

                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']); ?>" required>

                <label>Apellidos:</label>
                <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido']); ?>" required>

                <label>Correo:</label>
                <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']); ?>" required>

                <label>Teléfono:</label>
                <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']); ?>">

                <label>Cédula:</label>
                <input type="text" value="<?= htmlspecialchars($usuario['cedula']); ?>" readonly>

                <label>Fecha de nacimiento:</label>
                <input type="date" name="fecha_nacimiento" value="<?= $usuario['fecha_nacimiento']; ?>">

                <label>Fotografía:</label>
                <input type="file" name="fotografia" accept=".jpg,.jpeg,.png">

                <h3 class="titulo-pass">Cambiar contraseña (opcional)</h3>

                <label>Nueva contraseña:</label>
                <input type="password" name="nueva">

                <label>Confirmar contraseña:</label>
                <input type="password" name="confirmar">

            </div>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje-exito"><?= $_SESSION['mensaje']; ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <input type="submit" value="Guardar Cambios" class="btn">

    </form>

    <?php
    $volver = "#";
    if (isset($_SESSION['tipo'])) {
        if ($_SESSION['tipo'] === "administrador") {
            $volver = "administrador.php";
        } elseif ($_SESSION['tipo'] === "chofer") {
            $volver = "chofer.php";
        } elseif ($_SESSION['tipo'] === "pasajero") {
            $volver = "pasajero.php";
        }
    }
    ?>

    <a href="<?= $volver ?>" class="btn-volver">⟵ Volver</a>

</section>

<footer>
    <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
</footer>

</body>
</html>