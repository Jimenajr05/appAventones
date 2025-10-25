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
$fotoUsuario = $_SESSION['foto'] ?? "../assets/Estilos/Imagenes/default-user.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Chofer | Aventones</title>
    <link rel="stylesheet" href="../assets/Estilos/chofer.css">
</head>
<body>

<!-- 🟢 ENCABEZADO SUPERIOR -->
<header class="hero-header">
    <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" class="logo-hero">
    <h1>Bienvenido <span class="resaltado">Aventones.com</span></h1>
    <h2>Tu mejor opción para viajar seguros</h2>
</header>

<!-- ⚪ TOOLBAR INFERIOR -->
<nav class="toolbar">
    <div class="toolbar-left">
        <a href="chofer.php" class="nav-link active">Rides</a>
        <a href="vehiculos.php" class="nav-link">Vehículos</a>
        <a href="choferReservas.php" class="nav-link">Reservas</a>
    </div>
    <div class="toolbar-right">
        <span class="user-name"><?= htmlspecialchars($_SESSION['nombre']); ?></span>
        <img src="<?= htmlspecialchars($fotoUsuario); ?>" alt="Usuario" class="user-photo">
        <a href="../logica/cerrarSesion.php" class="logout-btn">Salir</a>
    </div>
</nav>

<section class="container">
    <h2><?= $editar ? "Editar Ride" : "Nuevo Ride"; ?></h2>
    <form method="POST" class="formulario">
        <?php if ($editar): ?>
            <input type="hidden" name="id_ride" value="<?= $editar['id_ride']; ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div><label>Nombre:</label>
                <input type="text" name="nombre" value="<?= $editar['nombre'] ?? ''; ?>" required></div>
            <div><label>Inicio:</label>
                <input type="text" name="inicio" value="<?= $editar['inicio'] ?? ''; ?>" required></div>
            <div><label>Destino:</label>
                <input type="text" name="fin" value="<?= $editar['fin'] ?? ''; ?>" required></div>
            <div><label>Hora:</label>
                <input type="time" name="hora" value="<?= $editar['hora'] ?? ''; ?>" required></div>
            <div><label>Día:</label>
                <select name="dia" required>
                    <?php
                    $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
                    foreach ($dias as $d) {
                        $sel = ($editar && $editar['dia'] == $d) ? 'selected' : '';
                        echo "<option value='$d' $sel>$d</option>";
                    }
                    ?>
                </select>
            </div>
            <div><label>Vehículo:</label>
                <select name="id_vehiculo" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($vehiculos as $v): ?>
                        <option value="<?= $v['id_vehiculo']; ?>"
                            <?= ($editar && $editar['id_vehiculo'] == $v['id_vehiculo']) ? 'selected' : ''; ?>>
                            <?= $v['marca'] . " " . $v['modelo'] . " (" . $v['placa'] . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label>Costo (₡):</label>
                <input type="number" step="0.01" name="costo" value="<?= $editar['costo'] ?? ''; ?>" required></div>
            <div><label>Espacios:</label>
                <input type="number" name="espacios" value="<?= $editar['espacios'] ?? ''; ?>" required></div>
        </div>

        <div class="center">
            <input type="submit" value="<?= $editar ? 'Actualizar Ride' : 'Guardar Ride'; ?>" class="btn btn-blue">
        </div>
    </form>

    <h2>Rides Registrados</h2>
    <div class="table-container">
        <table>
            <thead>
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
            </thead>
            <tbody>
                <?php foreach ($rides as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nombre']); ?></td>
                        <td><?= htmlspecialchars($r['inicio']); ?></td>
                        <td><?= htmlspecialchars($r['fin']); ?></td>
                        <td><?= htmlspecialchars($r['hora']); ?></td>
                        <td><?= htmlspecialchars($r['dia']); ?></td>
                        <td><?= htmlspecialchars($r['marca']." ".$r['modelo']); ?></td>
                        <td>₡<?= number_format($r['costo'], 2); ?></td>
                        <td><?= htmlspecialchars($r['espacios']); ?></td>
                        <td>
                            <a href="?editar=<?= $r['id_ride']; ?>" class="btn status-btn btn-on">✏️ Editar</a>
                            <a href="?eliminar=<?= $r['id_ride']; ?>" class="btn status-btn btn-off"
                               onclick="return confirm('¿Eliminar este ride?');">🗑️ Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<footer>
    <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
</footer>

</body>
</html>
