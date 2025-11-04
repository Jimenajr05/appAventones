<?php
// =====================================================
// Script: administrador.php (Vista/Controlador).
// Descripción: Panel de control exclusivo para el Administrador.
// Incluye la lógica de gestión (activar/desactivar usuarios
// y crear admins) y renderiza la vista.
// Creado por: Jimena Jara y Fernanda Sibaja.
// =====================================================

session_start();
include("../includes/conexion.php");
include("../logica/administrador.php");

// Solo el administrador puede ingresar
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

// Instanciar la clase Administrador
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

// Foto de usuario del sistema
if (!empty($_SESSION['foto'])) {
    if (str_starts_with($_SESSION['foto'], 'uploads/')) {
        $fotoUsuario = "../" . $_SESSION['foto'] . "?v=" . time();
    } else {
        $fotoUsuario = "../assets/Estilos/Imagenes/" . $_SESSION['foto'] . "?v=" . time();
    }
} else {
    $fotoUsuario = "../assets/Estilos/Imagenes/default-user.png";
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

<nav class="toolbar">
    <div class="toolbar-left"></div>
    <div class="toolbar-right">
        <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
        <img src="<?= htmlspecialchars($fotoUsuario); ?>" class="user-photo" alt="Usuario">
        <a href="editarPerfil.php" class="edit-btn">Editar Perfil</a>
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
                <!-- Mostrar usuarios -->
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

    <div class="form-row">
        <label>Nombre:</label>
        <input type="text" name="nombre" required>
    </div>

    <div class="form-row">
        <label>Apellidos:</label>
        <input type="text" name="apellido" required>
    </div>

    <div class="form-row">
        <label>Cédula:</label>
        <input type="text" name="cedula" required>
    </div>

    <div class="form-row">
        <label>Fecha de nacimiento:</label>
        <input type="date" name="fecha_nacimiento" required>
    </div>

    <div class="form-row">
        <label>Correo electrónico:</label>
        <input type="email" name="correo" required>
    </div>

    <div class="form-row">
        <label>Teléfono:</label>
        <input type="text" name="telefono" required>
    </div>

    <div class="form-row">
        <label>Contraseña:</label>
        <input type="password" name="contrasena" required>
    </div>

    <div class="form-row">
        <label>Confirmar contraseña:</label>
        <input type="password" name="confirmar_contrasena" required>
    </div>

    <div class="form-row">
        <label>Fotografía:</label>
        <input type="file" name="fotografia" accept=".jpg,.jpeg,.png">
    </div>

    <input type="submit" name="crearAdmin" value="Crear Administrador" class="btn btn-blue">

        <!-- Mensaje de éxito/error -->
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
