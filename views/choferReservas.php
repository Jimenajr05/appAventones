<?php
session_start();
include("../includes/conexion.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
    header("Location: login.php");
    exit;
}

$idChofer = $_SESSION['id_usuario'];
$fotoUsuario = $_SESSION['foto'] ?? "../assets/Estilos/Imagenes/default-user.png";
$mensaje = $_GET['msg'] ?? "";

// 🔹 Obtener reservas de los rides del chofer
$sql = "SELECT r.id_reserva, r.estado, u.nombre AS pasajero, d.nombre AS ride, 
               d.inicio, d.fin, d.dia, d.hora
        FROM reservas r
        JOIN rides d ON r.id_ride = d.id_ride
        JOIN usuarios u ON r.id_pasajero = u.id_usuario
        WHERE d.id_chofer = ?
        ORDER BY r.fecha_reserva DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idChofer);
$stmt->execute();
$reservas = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas | Aventones</title>
    <link rel="stylesheet" href="../assets/Estilos/choferReservas.css">
</head>
<body>

<!-- 🟢 ENCABEZADO -->
<header class="hero-header">
    <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" class="logo-hero">
    <h1>Bienvenido <span class="resaltado">Aventones.com</span></h1>
    <h2>Tu mejor opción para viajar seguros</h2>
</header>

<!-- ⚪ TOOLBAR -->
<nav class="toolbar">
    <div class="toolbar-left">
        <a href="chofer.php" class="nav-link">Rides</a>
        <a href="vehiculos.php" class="nav-link">Vehículos</a>
        <a href="choferReservas.php" class="nav-link active">Reservas</a>
    </div>
    <div class="toolbar-right">
        <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
        <img src="<?= htmlspecialchars($fotoUsuario); ?>" alt="Usuario" class="user-photo">
        <a href="../logica/cerrarSesion.php" class="logout-btn">Salir</a>
    </div>
</nav>

<!-- 🧾 CONTENIDO -->
<section class="container">
    <?php if ($mensaje): ?>
        <p class="alert"><?= htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <h2>Reservas de Mis Rides</h2>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Pasajero</th>
                    <th>Ride</th>
                    <th>Inicio</th>
                    <th>Destino</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $reservas->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($r['pasajero']); ?></td>
                    <td><?= htmlspecialchars($r['ride']); ?></td>
                    <td><?= htmlspecialchars($r['inicio']); ?></td>
                    <td><?= htmlspecialchars($r['fin']); ?></td>
                    <td><?= htmlspecialchars($r['dia']); ?></td>
                    <td><?= htmlspecialchars($r['hora']); ?></td>
                    <td>
                        <span class="estado <?= strtolower($r['estado']); ?>">
                            <?= ucfirst($r['estado']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($r['estado'] === 'pendiente'): ?>
                            <a href="../logica/reservas.php?accion=aceptar&id=<?= $r['id_reserva']; ?>" class="btn btn-on">Aceptar</a>
                            <a href="../logica/reservas.php?accion=rechazar&id=<?= $r['id_reserva']; ?>" class="btn btn-off">Rechazar</a>
                        <?php else: ?>
                            <span class="sin-accion">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<footer>
    <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
</footer>

</body>
</html>
