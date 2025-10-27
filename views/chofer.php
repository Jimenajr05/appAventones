<?php
session_start();
include("../includes/conexion.php");
include("../logica/rides.php");

// Seguridad: solo chofer
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
    header("Location: login.php");
    exit;
}

$ride = new Ride($conexion);
$idChofer = (int)$_SESSION['id_usuario'];

// Eliminar ride
if (isset($_GET['eliminar'])) {
    $ride->eliminarRide((int)$_GET['eliminar'], $idChofer);
    header("Location: chofer.php");
    exit;
}

// Guardar (crear o editar)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // valida: año ≥ 2010, espacios ≤ 5 y ≤ capacidad, reglas de costo, etc.
        $ride->guardarRide($_POST, $idChofer);
        header("Location: chofer.php");
        exit;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// Editar
$editar = null;
if (isset($_GET['editar'])) {
    $editar = $ride->obtenerRide((int)$_GET['editar'], $idChofer);
}

// Listas
$rides = $ride->obtenerRides($idChofer);
$vehiculos = $ride->obtenerVehiculos($idChofer); // debe traer capacidad y anno
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
        <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
        <img src="<?= htmlspecialchars($fotoUsuario); ?>" alt="Usuario" class="user-photo">
        <a href="../logica/cerrarSesion.php" class="logout-btn">Salir</a>
    </div>
</nav>

<section class="container">
    <h2><?= $editar ? "Editar Ride" : "Nuevo Ride"; ?></h2>

    <?php if (!empty($errorMsg)): ?>
        <p class="alert" style="color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;padding:.75rem;border-radius:.5rem;">
            <?= htmlspecialchars($errorMsg) ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="formulario" id="formRide">
        <?php if ($editar): ?>
            <input type="hidden" name="id_ride" value="<?= (int)$editar['id_ride']; ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div><label>Nombre:</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($editar['nombre'] ?? ''); ?>" required></div>

            <div><label>Inicio:</label>
                <input type="text" name="inicio" value="<?= htmlspecialchars($editar['inicio'] ?? ''); ?>" required></div>

            <div><label>Destino:</label>
                <input type="text" name="fin" value="<?= htmlspecialchars($editar['fin'] ?? ''); ?>" required></div>

            <div><label>Hora:</label>
                <input type="time" name="hora" value="<?= htmlspecialchars($editar['hora'] ?? ''); ?>" required></div>

            <div><label>Día:</label>
                <select name="dia" required>
                    <?php
                    $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
                    foreach ($dias as $d) {
                        $sel = ($editar && $editar['dia'] === $d) ? 'selected' : '';
                        echo "<option value=\"".htmlspecialchars($d)."\" $sel>".htmlspecialchars($d)."</option>";
                    }
                    ?>
                </select>
            </div>

            <div><label>Vehículo:</label>
                <select name="id_vehiculo" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($vehiculos as $v): ?>
                        <option
                            value="<?= (int)$v['id_vehiculo']; ?>"
                            <?= ($editar && (int)$editar['id_vehiculo'] === (int)$v['id_vehiculo']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars(
                                $v['marca']." ".$v['modelo']." (".$v['placa'].") — cap: ".(int)$v['capacidad'].", año: ".(int)$v['anno']
                            ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div><label>Costo (₡):</label>
                <input type="number" step="0.01" min="0" name="costo"
                       value="<?= htmlspecialchars($editar['costo'] ?? ''); ?>" required>
            </div>

            <div><label>Espacios:</label>
                <input type="number" name="espacios" min="1" max="5"
                       value="<?= htmlspecialchars($editar['espacios'] ?? ''); ?>" required>
            </div>
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
                        <td>₡<?= number_format((float)$r['costo'], 2); ?></td>
                        <td><?= (int)$r['espacios']; ?></td>
                        <td>
                            <a href="?editar=<?= (int)$r['id_ride']; ?>" class="btn status-btn btn-on">✏️ Editar</a>
                            <a href="?eliminar=<?= (int)$r['id_ride']; ?>" class="btn status-btn btn-off"
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
