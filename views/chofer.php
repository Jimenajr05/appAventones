<?php
session_start();
include("../includes/conexion.php");
include("../logica/ride.php");

// Seguridad: solo chofer
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
    header("Location: login.php");
    exit;
}

$ride = new Ride($conexion);
$idChofer = $_SESSION['id_usuario'];

// Eliminar ride
if (isset($_GET['eliminar'])) {
    $ride->eliminarRide($_GET['eliminar'], $idChofer);
    header("Location: chofer.php");
    exit;
}

// Guardar (crear o editar)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ride->guardarRide($_POST, $idChofer);
    header("Location: chofer.php");
    exit;
}

// Editar
$editar = null;
if (isset($_GET['editar'])) {
    $editar = $ride->obtenerRide($_GET['editar'], $idChofer);
}

// Obtener todos los rides y vehículos
$rides = $ride->obtenerRides($idChofer);
$vehiculos = $ride->obtenerVehiculos($idChofer);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Chofer</title>
    <link rel="stylesheet" href="../assets/Estilos/rides.css">
</head>
<body>
<header>
    <h1>Gestión de Rides</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> 👋</p>
    <nav>
        <a href="vehiculos.php">🚗 Mis Vehículos</a> |
        <a href="choferReservas.php">🎟️ Mis Reservas</a> |
        <a href="../logica/cerrarSesion.php">🚪 Cerrar sesión</a>
    </nav>
</header>

<section class="container">
    <h2><?= $editar ? "Editar Ride" : "Nuevo Ride"; ?></h2>
    <form method="POST">
        <?php if ($editar): ?>
            <input type="hidden" name="id_ride" value="<?= $editar['id_ride']; ?>">
        <?php endif; ?>

        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= $editar['nombre'] ?? ''; ?>" required>

        <label>Inicio:</label>
        <input type="text" name="inicio" value="<?= $editar['inicio'] ?? ''; ?>" required>

        <label>Destino:</label>
        <input type="text" name="fin" value="<?= $editar['fin'] ?? ''; ?>" required>

        <label>Hora:</label>
        <input type="time" name="hora" value="<?= $editar['hora'] ?? ''; ?>" required>

        <label>Día:</label>
        <select name="dia" required>
            <?php
            $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
            foreach ($dias as $d) {
                $sel = ($editar && $editar['dia'] == $d) ? 'selected' : '';
                echo "<option value='$d' $sel>$d</option>";
            }
            ?>
        </select>

        <label>Vehículo:</label>
        <select name="id_vehiculo" required>
            <option value="">Seleccione...</option>
            <?php foreach ($vehiculos as $v): ?>
                <option value="<?= $v['id_vehiculo']; ?>"
                    <?= ($editar && $editar['id_vehiculo'] == $v['id_vehiculo']) ? 'selected' : ''; ?>>
                    <?= $v['marca'] . " " . $v['modelo'] . " (" . $v['placa'] . ")"; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Costo por espacio:</label>
        <input type="number" step="0.01" name="costo" value="<?= $editar['costo'] ?? ''; ?>" required>

        <label>Espacios disponibles:</label>
        <input type="number" name="espacios" value="<?= $editar['espacios'] ?? ''; ?>" required>

        <input type="submit" value="<?= $editar ? 'Actualizar' : 'Guardar'; ?>">
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
            <td><?= $r['marca'] . " " . $r['modelo']; ?></td>
            <td>₡<?= number_format($r['costo'], 2); ?></td>
            <td><?= $r['espacios']; ?></td>
            <td>
                <a href="?editar=<?= $r['id_ride']; ?>">✏️</a> |
                <a href="?eliminar=<?= $r['id_ride']; ?>" onclick="return confirm('¿Eliminar este ride?');">🗑️</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>
</body>
</html>
