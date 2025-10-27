<?php
session_start();
include("../includes/conexion.php");
include("../logica/administrador.php");

// Solo el administrador puede ingresar
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

$admin = new Administrador($conexion);

// Crear nuevo administrador
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crearAdmin'])) {
    $admin->procesarNuevoAdministrador($_POST, $_FILES);
}

// Cambiar estado de usuario (activar/desactivar)
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];
    $nuevoEstado = ($accion === 'activar') ? 'activo' : 'inactivo';
    $admin->cambiarEstado($id, $nuevoEstado);
    header("Location: administrador.php");
    exit;
}

// Obtener lista de usuarios
$usuarios = $admin->obtenerUsuarios();

// ✅ Obtener la foto del administrador logueado
$fotoUsuario = $_SESSION['foto'] ?? "../assets/Estilos/Imagenes/default-user.png";
if (!file_exists($fotoUsuario) && file_exists("../" . $fotoUsuario)) {
    $fotoUsuario = "../" . $fotoUsuario;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Administrador | Aventones</title>
    <link rel="stylesheet" href="../assets/Estilos/administrador.css">
</head>
    <body>

        <header>
            <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" width="170">
            <h1>Bienvenido a <span class="resaltado">Aventones.com</span></h1>
            <p>Tu mejor opción para viajar seguros</p>
        </header>

        <!-- ⚪ TOOLBAR -->
        <nav class="toolbar">
            <div class="toolbar-left">
            </div>
            <div class="toolbar-right">
                <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
                <img src="<?= htmlspecialchars($fotoUsuario); ?>" class="user-photo" alt="Usuario">
                <a href="../logica/cerrarSesion.php" class="logout-btn">Salir</a>
            </div>
        </nav>

        <section class="container">
            <h3>Usuarios registrados</h3>

            <div class="table-container">
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
                                <td><?= $usuario['id_usuario']; ?></td>
                                <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                                <td><?= htmlspecialchars($usuario['correo']); ?></td>
                                <td><?= ucfirst($usuario['tipo']); ?></td>
                                <td>
                                    <span class="estado <?= $usuario['estado']; ?>">
                                        <?= ucfirst($usuario['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($usuario['estado'] === 'activo'): ?>
                                        <a href="?accion=desactivar&id=<?= $usuario['id_usuario']; ?>" class="btn btn-red">Desactivar</a>
                                    <?php else: ?>
                                        <a href="?accion=activar&id=<?= $usuario['id_usuario']; ?>" class="btn btn-green">Activar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <h3>Registrar nuevo administrador</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <label>Nombre:</label>
                <input type="text" name="nombre" required>

                <label>Apellidos:</label>
                <input type="text" name="apellido" required>

                <label>Cédula:</label>
                <input type="text" name="cedula" required>

                <label>Fecha de nacimiento:</label>
                <input type="date" name="fecha_nacimiento" required>

                <label>Correo electrónico:</label>
                <input type="email" name="correo" required>

                <label>Teléfono:</label>
                <input type="text" name="telefono" required>

                <label>Contraseña:</label>
                <input type="password" name="contrasena" required>

                <label>Confirmar contraseña:</label>
                <input type="password" name="confirmar_contrasena" required>

                <label>Fotografía:</label>
                <input type="file" name="fotografia" accept=".jpg,.jpeg,.png">

                <input type="submit" name="crearAdmin" value="Crear Administrador" class="btn btn-blue">

                <?php
                if (isset($_SESSION['mensaje'])) {
                    echo "<div class='mensaje'>" . $_SESSION['mensaje'] . "</div>";
                    unset($_SESSION['mensaje']);
                    
                }
                ?>
            </form>
        </section>

        <footer>
            <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
        </footer>

    </body>
</html>