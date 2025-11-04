<?php 
    // =====================================================
    // Script: pasajero.php (Vista/Controlador).
    // Descripción: Panel principal para usuarios tipo pasajero.
    // Creado por: Jimena Jara y Fernanda Sibaja.
    // =====================================================

    session_start();
    include("../includes/conexion.php");

    // Verificar que el usuario es un pasajero
    if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'pasajero') {
        header("Location: login.php");
        exit;
    }

    // Obtener datos del pasajero
    $idPasajero = $_SESSION['id_usuario'];
    $mensaje = $_GET['msg'] ?? "";
    
     // Foto de usuario del sistema
    if (!empty($_SESSION['foto'])) {
        if (str_starts_with($_SESSION['foto'], 'uploads/')) {
            $fotoUsuario = "../" . $_SESSION['foto'] . "?v=" . time();
        } else {
            // Compatibilidad con fotos antiguas
            $fotoUsuario = "../assets/Estilos/Imagenes/" . $_SESSION['foto'] . "?v=" . time();
        }
    } else {
        // Default
        $fotoUsuario = "../assets/Estilos/Imagenes/default-user.png";
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Panel del Pasajero | Aventones</title>
        <link rel="stylesheet" href="../assets/Estilos/pasajero.css">
    </head>
    <body>
        <header class="header-pasajero">
        <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" class="logo-hero">
        <h1>Bienvenido a <span class="resaltado">Aventones.com</span></h1>
        <h2>Tu mejor opción para viajar seguros</h2>
        </header>

        <nav class="toolbar">
            <div class="toolbar-left">
                <a href="pasajero.php" class="nav-link active">Panel de Pasajero</a>
                <a href="buscarRides.php" class="nav-link">Buscar Rides</a>
                <a href="pasajeroReservas.php" class="nav-link">Mis Reservas</a>
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
                <p class="alert"><?= htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <div class="opciones">
                <div class="opcion">
                    <h2>🔍 Buscar Rides</h2>
                    <p>Encuentra viajes disponibles cerca de ti y reserva fácilmente.</p>
                    <a href="buscarRides.php" class="btn btn-blue">Buscar Rides</a>
                </div>

                <div class="opcion">
                    <h2>🎟️ Mis Reservas</h2>
                    <p>Consulta y gestiona las reservas que hayas realizado.</p>
                    <a href="pasajeroReservas.php" class="btn btn-green">Ver Reservas</a>
                </div>
            </div>
        </section>

        <footer>
            <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
        </footer>

    </body>
</html>