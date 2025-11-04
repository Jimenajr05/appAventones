<?php
// =====================================================
// Script: choferReservas.php (Vista Chofer)
// Descripción: Muestra las **Reservas** de los aventones
// del chofer y permite **Aceptar/Rechazar** las solicitudes.
// Creado por: Jimena y Fernanda.
// =====================================================

session_start();
include("../includes/conexion.php");
include("../logica/ChoferReservas.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
    header("Location: login.php");
    exit;
}

$idChofer = $_SESSION['id_usuario'];

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

$mensaje = $_GET['msg'] ?? "";

// Llamar la lógica separada 
$model = new ChoferReservas($conexion);
$reservas = $model->obtenerReservas($idChofer);

// Buffer para usar los resultados 2 veces
$rows = $reservas;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chofer Reservas | Aventones</title>
    <link rel="stylesheet" href="../assets/Estilos/choferReservas.css">
</head>
<body>

<header class="hero-header">
    <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" class="logo-hero">
    <h1>Bienvenido <span class="resaltado">Aventones.com</span></h1>
    <h2>Tu mejor opción para viajar seguros</h2>
</header>

<nav class="toolbar">
    <div class="toolbar-left">
        <a href="chofer.php" class="nav-link">Rides</a>
        <a href="vehiculos.php" class="nav-link">Vehículos</a>
        <a href="choferReservas.php" class="nav-link active">Reservas</a>
    </div>
    <div class="toolbar-right">
        <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
        <img src="<?= htmlspecialchars($fotoUsuario); ?>" alt="Usuario" class="user-photo">
        <a href="editarPerfil.php" class="edit-btn">Editar Perfil</a>
        <a href="../logica/cerrarSesion.php" class="logout-btn">Salir</a>
    </div>
</nav>

<section class="container">

<?php if ($mensaje): ?>
    <p class="alert"><?= htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<!-- Reservas activas -->
<h2>Reservas Activas</h2>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Pasajero</th>
                <th>Ride</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($rows as $r): ?>
            <?php if ($r['estado'] === 'pendiente' || $r['estado'] === 'aceptada'): ?>
                <tr>
                    <td><?= htmlspecialchars($r['pasajero']); ?></td>
                    <td><?= htmlspecialchars($r['ride']); ?></td>
                    <td><?= htmlspecialchars($r['inicio']); ?></td>
                    <td><?= htmlspecialchars($r['fin']); ?></td>
                    <td><?= htmlspecialchars($r['dia']); ?></td>
                    <td><?= date("h:i A", strtotime($r['hora'])); ?></td>
                    <td>
                        <span class="estado <?= strtolower($r['estado']); ?>">
                            <?= ucfirst($r['estado']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($r['estado'] === 'pendiente'): ?>
                            <div class="acciones">
                                <a href="../logica/reservas.php?accion=aceptar&id=<?= $r['id_reserva']; ?>" 
                                   class="action-btn action-edit">Aceptar</a>
                                <a href="../logica/reservas.php?accion=rechazar&id=<?= $r['id_reserva']; ?>" 
                                   class="action-btn action-delete">Rechazar</a>
                            </div>
                        <?php else: ?>
                            <span class="sin-accion">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>

        </tbody>
    </table>
</div>

<!-- Historial -->
<h2>Historial de Reservas</h2>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Pasajero</th>
                <th>Ride</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($rows as $r): ?>
            <?php if ($r['estado'] === 'cancelada' || $r['estado'] === 'rechazada'): ?>
                <tr>
                    <td><?= htmlspecialchars($r['pasajero']); ?></td>
                    <td><?= htmlspecialchars($r['ride']); ?></td>
                    <td><?= htmlspecialchars($r['inicio']); ?></td>
                    <td><?= htmlspecialchars($r['fin']); ?></td>
                    <td><?= htmlspecialchars($r['dia']); ?></td>
                    <td><?= date("h:i A", strtotime($r['hora'])); ?></td>
                    <td>
                        <?php
                            switch ($r['estado']) {
                                case 'cancelada': echo '❌ Cancelada'; break;
                                case 'rechazada': echo '🚫 Rechazada'; break;
                            }
                        ?>
                    </td>
                </tr>
            <?php endif; ?>
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