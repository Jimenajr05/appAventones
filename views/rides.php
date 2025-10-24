<?php
session_start();
include("../includes/conexion.php");
include("../logica/ride.php");

// Verificar acceso
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
    header("Location: login.php");
    exit;
}

$rideObj = new Ride($conexion);
$idChofer = $_SESSION['id_usuario'];

// ✅ Eliminar
if (isset($_GET['eliminar'])) {
    $rideObj->eliminarRide($_GET['eliminar'], $idChofer);
    header("Location: rides.php");
    exit;
}

// ✅ Guardar (crear o editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rideObj->guardarRide($_POST, $idChofer);
    header("Location: rides.php");
    exit;
}

// ✅ Editar
$rideEditar = null;
if (isset($_GET['editar'])) {
    $rideEditar = $rideObj->obtenerRide($_GET['editar'], $idChofer);
}

// ✅ Obtener lista de rides y vehículos
$rides = $rideObj->obtenerRides($idChofer);
$vehiculos = $rideObj->obtenerVehiculos($idChofer);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Rides</title>
    <link rel="stylesheet" href="../assets/Estilos/rides.css">
</head>
<body>
<header>
    <h1>Gestión de Rides</h1>
    <p>Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?> 👋</p>
    <nav>
        <a href="chofer.php">Volver al Panel</a> |
        <a href="../logica/cerrarSesion.php">Cerrar sesión</a>
    </nav>
</header>

<section class="container">
    <h2><?= $rideEditar ? "Editar Ride" : "Crear Ride"; ?></h2>
    <form method="POST" class="formulario">
        <?php if ($rideEditar): ?>
            <input type="hidden" name="id_ride" value="<?= $rideEditar['id_ride']; ?>">
        <?php endif; ?>

        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= $rideEditar['nombre'] ?? ''; ?>" required>

        <label>Inicio:</label>
        <input type="text" name="inicio" value="<?= $rideEditar['inicio'] ?? ''; ?>" required>

        <label>Destino:</label>
        <input type="text" name="fin" value="<?= $rideEditar['fin'] ?? ''; ?>" required>

        <label>Hora:</label>
        <input type="time" name="hora" value="<?= $rideEditar['hora'] ?? ''; ?>" required>

        <label>Día:</label>
        <select name="dia" required>
            <?php
            $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
            foreach ($dias as $d) {
                $sel = ($rideEditar && $rideEditar['dia'] == $d) ? 'selected' : '';
                echo "<option value='$d' $sel>$d</option>";
            }
            ?>
        </select>

        <label>Vehículo:</label>
        <select name="id_vehiculo" required>
            <option value="">Seleccione...</option>
            <?php foreach ($vehiculos as $v): ?>
                <option value="<?= $v['id_vehiculo']; ?>"
                    <?= ($rideEditar && $rideEditar['id_vehiculo'] == $v['id_vehiculo']) ? 'selected' : ''; ?>>
                    <?= $v['marca'] . " " . $v['modelo'] . " (" . $v['placa'] . ")"; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Costo:</label>
        <input type="number" name="costo" step="0.01" value="<?= $rideEditar['costo'] ?? ''; ?>" required>

        <label>Espacios:</label>
        <input type="number" name="espacios" value="<?= $rideEditar['espacios'] ?? ''; ?>" required>

        <input type="submit" value="<?= $rideEditar ? "Actualizar" : "Guardar"; ?>">
    </form>

    <h2>Rides Registrados</h2>
    <table>
        <tr>
            <th>Nombre</th>
            <th>Inicio</th>
            <th>Destino</th>
            <th>Hora</th>
            <th>Día</th>
            <th>Vehículo</th>
            <th>Costo</th>
            <th>Espacios</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($rides as $r): ?>
        <tr>
            <td><?= $r['nombre']; ?></td>
            <td><?= $r['inicio']; ?></td>
            <td><?= $r['fin']; ?></td>
            <td><?= $r['hora']; ?></td>
            <td><?= $r['dia']; ?></td>
            <td><?= $r['marca']." ".$r['modelo']; ?></td>
            <td>₡<?= number_format($r['costo'], 2); ?></td>
            <td><?= $r['espacios']; ?></td>
            <td>
                <a href="?editar=<?= $r['id_ride']; ?>">✏️</a> |
                <a href="?eliminar=<?= $r['id_ride']; ?>" onclick="return confirm('¿Eliminar este r_]()
