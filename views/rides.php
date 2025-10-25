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
$fotoUsuario = $_SESSION['foto'] ?? "../assets/Estilos/Imagenes/default-user.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Rides | Aventones</title>
    <link rel="stylesheet" href="../assets/Estilos/rides.css">
</head>
<body>

<!-- 🟢 BARRA SUPERIOR -->
<nav class="topbar">
    <div class="left">
        <img src="../assets/Estilos/Imagenes/logo.png" class="logo-nav" alt="Logo">
        <a href="chofer.php" class="link">Dashboard</a>
        <a href="rides.php" class="link active">Rides</a>
        <a href="perfil.php" class="link">Configuración</a>
    </div>
    <div class="right">
        <span class="user-welcome">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
        <img src="<?= htmlspecialchars($fotoUsuario); ?>" class="user-photo" alt="Usuario">
        <a href="../logica/cerrarSesion.php" class="logout-link">Salir</a>
    </div>
</nav>

<section class="container">
    <h2><?= $rideEditar ? "Editar Ride" : "Registrar Ride"; ?></h2>

    <form method="POST" class="formulario">
        <?php if ($rideEditar): ?>
            <input type="hidden" name="id_ride" value="<?= $rideEditar['id_ride']; ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div><label>Nombre:</label>
                <input type="text" name="nombre" value="<?= $rideEditar['nombre'] ?? ''; ?>" required></div>

            <div><label>Inicio:</label>
                <input type="text" name="inicio" value="<?= $rideEditar['inicio'] ?? ''; ?>" required></div>

            <div><label>Destino:</label>
                <input type="text" name="fin" value="<?= $rideEditar['fin'] ?? ''; ?>" required></div>

            <div><label>Hora:</label>
                <input type="time" name="hora" value="<?= $rideEditar['hora'] ?? ''; ?>" required></div>

            <div><label>Día:</label>
                <select name="dia" required>
                    <?php
                    $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
                    foreach ($dias as $d) {
                        $sel = ($rideEditar && $rideEditar['dia'] == $d) ? 'selected' : '';
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
                            <?= ($rideEditar && $rideEditar['id_vehiculo'] == $v['id_vehiculo']) ? 'selected' : ''; ?>>
                            <?= $v['marca'] . " " . $v['modelo'] . " (" . $v['placa'] . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div><label>Costo (₡):</label>
                <input type="number" name="costo" step="0.01" value="<?= $rideEditar['costo'] ?? ''; ?>" required></div>

            <div><label>Espacios:</label>
                <input type="number" name="espacios" value="<?= $rideEditar['espacios'] ?? ''; ?>" required></div>
        </div>

        <div class="center">
            <input type="submit" value="<?= $rideEditar ? "Actualizar Ride" : "Guardar Ride"; ?>" class="btn btn-blue">
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
                           onclick="return confirm('¿Eliminar este ride?')">🗑️ Eliminar</a>
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
