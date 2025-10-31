<!--
  // =====================================================
  // Script: pasajeroReservas.php
  // Descripción: Muestra las **Reservas Activas** y el
  // **Historial** del pasajero. Permite **cancelar** reservas.
  // Creado por: Jimena y Fernanda.
  // =====================================================
-->
<?php
  session_start();
  include("../includes/conexion.php");

  if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'pasajero') {
      header("Location: login.php");
      exit;
  }

  $idPasajero = $_SESSION['id_usuario'];
  $fotoUsuario = $_SESSION['foto'] ?? "../assets/Estilos/Imagenes/default-user.png";
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
    <link rel="stylesheet" href="../assets/Estilos/pasajeroReservas.css">
  </head>
  <body>

    <!-- 🟢 HEADER PRINCIPAL -->
    <header class="header-pasajero">
      <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" class="logo-hero">
      <h1>Bienvenido a <span class="resaltado">Aventones.com</span></h1>
      <h2>Tu mejor opción para viajar seguros</h2>
    </header>

    <!-- ⚪ TOOLBAR -->
    <nav class="toolbar">
      <div class="toolbar-left">
          <a href="pasajero.php" class="nav-link">Panel de Pasajero</a>
          <a href="buscarRides.php" class="nav-link">Buscar Rides</a>
          <a href="pasajeroReservas.php" class="nav-link active">Mis Reservas</a>
      </div>
      <div class="toolbar-right">
          <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
          <img src="<?= htmlspecialchars($fotoUsuario); ?>" class="user-photo" alt="Usuario">
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
                <a href="../logica/reservas.php?accion=cancelar&id=<?= $r['id_reserva'] ?>" 
                  class="btn-cancelar"
                  onclick="return confirm('¿Estás seguro de cancelar esta reserva?')">
                  Cancelar
                </a>
              </td>
            </tr>
            <?php 
                endif;
            endwhile; 
            ?>
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
          </tbody>
        </table>
      </div>
    </section>

    <!-- 🟢 FOOTER -->
    <footer>
      <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
    </footer>

  </body>
</html>

<?php
mysqli_free_result($resultado);
include("../includes/cerrarConexion.php");
?>
