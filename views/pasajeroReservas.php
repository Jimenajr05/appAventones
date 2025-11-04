<?php
// =====================================================
// Script: pasajeroReservas.php (Vista/Controlador).
// Descripción: Muestra las reservas de rides realizadas por el pasajero.
// Creado por: Jimena Jara y Fernanda Sibaja.
// =====================================================

session_start();
include("../includes/conexion.php");
include("../logica/PasajeroReservas.php");

// Verificar sesión y tipo de usuario
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'pasajero') {
    header("Location: login.php");
    exit;
}

// ID del pasajero
$idPasajero = $_SESSION['id_usuario'];

// Foto usuario
if (!empty($_SESSION['foto'])) {
    $fotoUsuario = str_starts_with($_SESSION['foto'], 'uploads/')
        ? "../" . $_SESSION['foto'] . "?v=" . time()
        : "../assets/Estilos/Imagenes/" . $_SESSION['foto'] . "?v=" . time();
} else {
    $fotoUsuario = "../assets/Estilos/Imagenes/default-user.png";
}

// Mensajes
$mensaje = $_GET['msg'] ?? "";
$error   = $_GET['error'] ?? "";

// Obtener reservas
$model    = new PasajeroReservas($conexion);
$reservas = $model->obtenerReservas($idPasajero);

// Particiones para pintar tablas
$activas = array_filter($reservas, function($r) {
    return in_array($r['estado'], ['pendiente', 'aceptada'], true);
});

$historial = array_filter($reservas, function($r) {
    return in_array($r['estado'], ['cancelada', 'rechazada'], true);
});
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pasajero Reservas | Aventones</title>
    <link rel="stylesheet" href="../assets/Estilos/pasajeroReservas.css">
</head>

<body>

<header class="header-pasajero">
    <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" class="logo-hero">
    <h1>Bienvenido a <span class="resaltado">Aventones.com</span></h1>
    <h2>Tu mejor opción para viajar seguros</h2>
</header>

<nav class="toolbar">
    <div class="toolbar-left">
        <a href="pasajero.php" class="nav-link">Panel de Pasajero</a>
        <a href="buscarRides.php" class="nav-link">Buscar Rides</a>
        <a href="pasajeroReservas.php" class="nav-link active">Mis Reservas</a>
    </div>
    <div class="toolbar-right">
        <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
        <img src="<?= htmlspecialchars($fotoUsuario); ?>" class="user-photo" alt="Usuario">
        <a href="editarPerfil.php" class="edit-btn">Editar Perfil</a>
        <a href="../logica/cerrarSesion.php" class="logout-btn">Salir</a>
    </div>
</nav>

<section class="container">

    <?php if ($mensaje): ?>
        <p class="alert success"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="alert error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <h2>Reservas Activas</h2>

    <div class="table-container">
        <table>
            <thead>
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
            </thead>

            <tbody>
            <?php if (count($activas) === 0): ?>
                <tr><td colspan="9" style="text-align:center">No hay reservas activas.</td></tr>
            <?php else: ?>
                <?php foreach ($activas as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['ride']) ?></td>
                        <td><?= htmlspecialchars($r['inicio']) ?></td>
                        <td><?= htmlspecialchars($r['fin']) ?></td>
                        <td><?= htmlspecialchars($r['dia']) ?></td>
                        <td><?= date("h:i A", strtotime($r['hora'])); ?></td>
                        <td><?= htmlspecialchars($r['chofer']) ?></td>
                        <td><?= $r['estado'] === 'pendiente' ? '⏳ Pendiente' : '✅ Aceptada' ?></td>
                        <td>₡<?= number_format((float)$r['costo'], 2) ?></td>
                        <td>
                            <a 
                                href="../logica/reservas.php?accion=cancelar&id=<?= (int)$r['id_reserva'] ?>" 
                                class="btn-cancelar"
                                onclick="return confirm('¿Estás seguro de cancelar esta reserva?')">
                                Cancelar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>

        </table>
    </div>

    <h2>Historial de Reservas</h2>

    <div class="table-container">
        <table>
            <thead>
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
            </thead>

            <tbody>
            <?php if (count($historial) === 0): ?>
                <tr><td colspan="8" style="text-align:center">Sin historial.</td></tr>
            <?php else: ?>
                <?php foreach ($historial as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['ride']) ?></td>
                        <td><?= htmlspecialchars($r['inicio']) ?></td>
                        <td><?= htmlspecialchars($r['fin']) ?></td>
                        <td><?= htmlspecialchars($r['dia']) ?></td>
                        <td><?= date("h:i A", strtotime($r['hora'])); ?></td>
                        <td><?= htmlspecialchars($r['chofer']) ?></td>
                        <td><?= $r['estado'] === 'cancelada' ? '❌ Cancelada' : '🚫 Rechazada' ?></td>
                        <td>₡<?= number_format((float)$r['costo'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>

        </table>
    </div>

</section>

<footer>
    <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
</footer>

</body>
</html>