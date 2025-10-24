<?php
session_start();
include("../includes/conexion.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
    header("Location: login.php");
    exit;
}

$idChofer = $_SESSION['id_usuario'];
$mensaje = $_GET['msg'] ?? "";

$sql = "SELECT r.id_reserva, r.estado, u.nombre AS pasajero, d.nombre AS ride, d.inicio, d.fin, d.dia, d.hora
        FROM reservas r
        JOIN rides d ON r.id_ride = d.id_ride
        JOIN usuarios u ON r.id_pasajero = u.id_usuario
        WHERE d.id_chofer = '$idChofer'
        ORDER BY r.fecha_reserva DESC";
$reservas = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas de Mis Rides</title>
    <link rel="stylesheet" href="../assets/Estilos/reservas.css">
</head>
<body>
<header>
    <h1>Reservas de Mis Rides 🚘</h1>
    <nav>
        <a href="chofer.php">Mis Rides</a> |
        <a href="../logica/cerrarSesion.php">Cerrar Sesión</a>
    </nav>
</header>

<section class="container">
    <?php if ($mensaje): ?>
        <p class="alert"><?= htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <table>
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
        <?php while ($r = mysqli_fetch_assoc($reservas)): ?>
        <tr>
            <td><?= $r['pasajero']; ?></td>
            <td><?= $r['ride']; ?></td>
            <td><?= $r['inicio']; ?></td>
            <td><?= $r['fin']; ?></td>
            <td><?= $r['dia']; ?></td>
            <td><?= $r['hora']; ?></td>
            <td><?= $r['estado']; ?></td>
            <td>
                <?php if ($r['estado'] === 'pendiente'): ?>
                    <a href="../logica/reservas.php?accion=aceptar&id=<?= $r['id_reserva']; ?>">Aceptar</a> |
                    <a href="../logica/reservas.php?accion=rechazar&id=<?= $r['id_reserva']; ?>">Rechazar</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>
</body>
</html>
