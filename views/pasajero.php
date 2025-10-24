<?php
session_start();
include("../includes/conexion.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'pasajero') {
    header("Location: login.php");
    exit;
}

$idPasajero = $_SESSION['id_usuario'];
$mensaje = $_GET['msg'] ?? "";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Pasajero</title>
    <link rel="stylesheet" href="../assets/Estilos/pasajero.css">
</head>
<body>
<header>
    <h1>Panel de Pasajero 🚗</h1>
    <nav>
        <a href="../logica/cerrarSesion.php">Cerrar Sesión</a>
    </nav>
</header>

<section class="container">
    <?php if ($mensaje): ?>
        <p class="alert"><?= htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <div class="opciones">
        <div class="opcion">
            <h2>🔍 Buscar Rides</h2>
            <p>Encuentra y reserva rides disponibles</p>
            <a href="buscarRides.php" class="btn">Buscar Rides</a>
        </div>

        <div class="opcion">
            <h2>🎟️ Mis Reservas</h2>
            <p>Ver y gestionar tus reservas</p>
            <a href="misReservas.php" class="btn">Ver Reservas</a>
        </div>
    </div>
</section>
</body>
</html>
