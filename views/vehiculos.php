<?php
session_start();
include("../includes/conexion.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
    header("Location: login.php");
    exit;
}

$idChofer = $_SESSION['id_usuario'];
$mensaje = $_GET['msg'] ?? "";

// Obtener lista de vehículos del chofer
$sql = "SELECT * FROM vehiculos WHERE id_chofer='$idChofer'";
$vehiculos = mysqli_query($conexion, $sql);

// Si se está editando un vehículo
$vehiculoEdit = null;
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
    $idVehiculo = $_GET['id'];
    $result = mysqli_query($conexion, "SELECT * FROM vehiculos WHERE id_vehiculo='$idVehiculo' AND id_chofer='$idChofer'");
    $vehiculoEdit = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Vehículos</title>
    <link rel="stylesheet" href="../assets/Estilos/vehiculos.css">
</head>
<body>
<header>
    <h1>Gestión de Vehículos 🚗</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
    <nav>
        <a href="chofer.php">Volver al Panel</a> |
        <a href="../logica/cerrarSesion.php">Cerrar Sesión</a>
    </nav>
</header>

<section class="container">
    <?php if ($mensaje): ?>
        <p class="alert"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <h2><?php echo $vehiculoEdit ? "Editar Vehículo" : "Agregar Vehículo"; ?></h2>

    <!-- Formulario -->
    <form action="../logica/vehiculos.php" method="POST" enctype="multipart/form-data">
        <?php if ($vehiculoEdit): ?>
            <input type="hidden" name="id_vehiculo" value="<?php echo $vehiculoEdit['id_vehiculo']; ?>">
        <?php endif; ?>

        <label>Marca:</label>
        <input type="text" name="marca" required value="<?php echo $vehiculoEdit['marca'] ?? ''; ?>">

        <label>Modelo:</label>
        <input type="text" name="modelo" required value="<?php echo $vehiculoEdit['modelo'] ?? ''; ?>">

        <label>Placa:</label>
        <input type="text" name="placa" required value="<?php echo $vehiculoEdit['placa'] ?? ''; ?>">

        <label>Color:</label>
        <input type="text" name="color" value="<?php echo $vehiculoEdit['color'] ?? ''; ?>">

        <label>Año:</label>
        <input type="number" name="anno" value="<?php echo $vehiculoEdit['anno'] ?? ''; ?>">

        <label>Capacidad:</label>
        <input type="number" name="capacidad" required value="<?php echo $vehiculoEdit['capacidad'] ?? ''; ?>">

        <label>Fotografía:</label>
        <input type="file" name="fotografia" accept=".jpg,.jpeg,.png">

        <input type="submit" value="<?php echo $vehiculoEdit ? 'Actualizar' : 'Guardar'; ?>">
    </form>

    <hr>

    <!-- Tabla -->
    <h2>Mis Vehículos Registrados</h2>
    <table>
        <tr>
            <th>Foto</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Placa</th>
            <th>Color</th>
            <th>Año</th>
            <th>Capacidad</th>
            <th>Acciones</th>
        </tr>
        <?php while ($v = mysqli_fetch_assoc($vehiculos)): ?>
        <tr>
            <td>
                <?php if ($v['fotografia']): ?>
                    <img src="../<?php echo $v['fotografia']; ?>" width="70">
                <?php else: ?>
                    <small>Sin imagen</small>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($v['marca']); ?></td>
            <td><?= htmlspecialchars($v['modelo']); ?></td>
            <td><?= htmlspecialchars($v['placa']); ?></td>
            <td><?= htmlspecialchars($v['color']); ?></td>
            <td><?= htmlspecialchars($v['anno']); ?></td>
            <td><?= htmlspecialchars($v['capacidad']); ?></td>
            <td>
                <a href="vehiculos.php?accion=editar&id=<?= $v['id_vehiculo']; ?>" class="btn-editar">Editar</a> |
                <a href="../logica/vehiculos.php?accion=eliminar&id=<?= $v['id_vehiculo']; ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar este vehículo?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>
</body>
</html>
