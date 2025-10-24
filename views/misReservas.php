<?php
session_start();
include("../includes/conexion.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'pasajero') {
    header("Location: login.php");
    exit;
}

$idPasajero = $_SESSION['id_usuario'];
$mensaje = $_GET['msg'] ?? "";
$error = $_GET['error'] ?? "";

// Obtener todas las reservas del pasajero
$sql = "SELECT r.id_reserva, r.estado, r.fecha_reserva,
               d.nombre AS ride, d.inicio, d.fin, d.dia, d.hora, d.costo,
               u.nombre AS chofer
        FROM reservas r
        JOIN rides d ON r.id_ride = d.id_ride
        JOIN usuarios u ON d.id_chofer = u.id_usuario
        WHERE r.id_pasajero = ?
        ORDER BY r.fecha_reserva DESC";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idPasajero);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas</title>
    <link rel="stylesheet" href="../assets/Estilos/reservas.css">
</head>
<body>
<header>
    <h1>Mis Reservas 🎟️</h1>
    <nav>
        <a href="dashboard.php">Panel</a> |
        <a href="buscarRides.php">Buscar Rides</a> |
        <a href="../logica/cerrarSesion.php">Cerrar Sesión</a>
    </nav>
</header>

<section class="container">
    <?php if ($mensaje): ?>
        <p class="alert success"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <p class="alert error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <h2>Reservas Activas</h2>
    <table>
        <tr>
            <th>Ride</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Chofer</th>
            <th>Estado</th>
            <th>Costo</th>
            <th>Acción</th>
        </tr>
        <?php 
        mysqli_data_seek($resultado, 0);
        while ($r = mysqli_fetch_assoc($resultado)): 
            if ($r['estado'] === 'pendiente' || $r['estado'] === 'aceptada'):
        ?>
        <tr>
            <td><?= htmlspecialchars($r['ride']) ?></td>
            <td><?= htmlspecialchars($r['inicio']) ?></td>
            <td><?= htmlspecialchars($r['fin']) ?></td>
            <td><?= htmlspecialchars($r['dia']) ?></td>
            <td><?= htmlspecialchars($r['hora']) ?></td>
            <td><?= htmlspecialchars($r['chofer']) ?></td>
            <td>
                <?php
                switch($r['estado']) {
                    case 'pendiente':
                        echo '⏳ Pendiente';
                        break;
                    case 'aceptada':
                        echo '✅ Aceptada';
                        break;
                }
                ?>
            </td>
            <td>₡<?= number_format($r['costo'], 2) ?></td>
            <td>
                <?php if ($r['estado'] === 'pendiente' || $r['estado'] === 'aceptada'): ?>
                    <a href="../logica/reservas.php?accion=cancelar&id=<?= $r['id_reserva'] ?>" 
                       onclick="return confirm('¿Estás seguro de cancelar esta reserva?')">
                        Cancelar
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php 
            endif;
        endwhile; 
        ?>
    </table>

    <h2>Historial de Reservas</h2>
    <table>
        <tr>
            <th>Ride</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Chofer</th>
            <th>Estado</th>
            <th>Costo</th>
        </tr>
        <?php 
        mysqli_data_seek($resultado, 0);
        while ($r = mysqli_fetch_assoc($resultado)): 
            if ($r['estado'] === 'cancelada' || $r['estado'] === 'rechazada'):
        ?>
        <tr>
            <td><?= htmlspecialchars($r['ride']) ?></td>
            <td><?= htmlspecialchars($r['inicio']) ?></td>
            <td><?= htmlspecialchars($r['fin']) ?></td>
            <td><?= htmlspecialchars($r['dia']) ?></td>
            <td><?= htmlspecialchars($r['hora']) ?></td>
            <td><?= htmlspecialchars($r['chofer']) ?></td>
            <td>
                <?php
                switch($r['estado']) {
                    case 'cancelada':
                        echo '❌ Cancelada';
                        break;
                    case 'rechazada':
                        echo '🚫 Rechazada';
                        break;
                }
                ?>
            </td>
            <td>₡<?= number_format($r['costo'], 2) ?></td>
        </tr>
        <?php 
            endif;
        endwhile; 
        ?>
    </table>
</section>
</body>
</html>
<?php
mysqli_free_result($resultado);
include("../includes/cerrarConexion.php");
?>