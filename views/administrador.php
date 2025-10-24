<?php
session_start();
include("../includes/conexion.php");
include("../logica/administrador.php");

// Solo admin puede acceder
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

$admin = new Administrador($conexion);

// Activar o desactivar usuario
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $accion = $_GET['accion'];
    $nuevoEstado = ($accion === 'activar') ? 'activo' : 'inactivo';
    $admin->cambiarEstado($id, $nuevoEstado);
    header("Location: administrador.php");
    exit;
}

$usuarios = $admin->obtenerUsuarios();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador</title>
    <link rel="stylesheet" href="../assets/Estilos/administrador.css">
</head>
<body>
<header>
    <h1>Panel del Administrador</h1>
    <p>Bienvenido, <?php echo $_SESSION['nombre']; ?> (<a href="../logica/cerrarSesion.php">Cerrar sesión</a>)</p>
</header>

<section class="container">
    <h2>Gestión de Usuarios</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo $usuario['id_usuario']; ?></td>
                    <td><?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></td>
                    <td><?php echo $usuario['correo']; ?></td>
                    <td><?php echo ucfirst($usuario['tipo']); ?></td>
                    <td><?php echo ucfirst($usuario['estado']); ?></td>
                    <td>
                        <?php if ($usuario['estado'] === 'activo'): ?>
                            <a href="?accion=desactivar&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-red">Desactivar</a>
                        <?php else: ?>
                            <a href="?accion=activar&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-green">Activar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Crear nuevo Administrador</h2>
    <form action="" method="POST">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="apellido" placeholder="Apellido" required>
        <input type="email" name="correo" placeholder="Correo" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <input type="submit" name="crearAdmin" value="Crear Administrador">
    </form>

    <?php
    if (isset($_POST['crearAdmin'])) {
        $admin->crearAdministrador($_POST['nombre'], $_POST['apellido'], $_POST['correo'], $_POST['contrasena']);
        echo "<p class='msg'>Administrador creado con éxito ✅</p>";
    }
    ?>
</section>
</body>
</html>
