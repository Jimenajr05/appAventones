<!--
    // =====================================================
    // Script: rides.php
    // Descripción: Panel de **CRUD de Rides** (Aventones)
    // para el Chofer. Muestra el formulario de edición/registro
    // y la tabla de rides activos.
    // Creado por: Jimena y Fernanda.
    // =====================================================
-->
<?php
    session_start();
    include("../includes/conexion.php");
    include("../logica/rides.php");

    // Verificar acceso
    if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
        header("Location: login.php");
        exit;
    }

    $rideObj = new Ride($conexion);
    $idChofer = (int)$_SESSION['id_usuario'];

    // ✅ Eliminar
    if (isset($_GET['eliminar'])) {
        $rideObj->eliminarRide((int)$_GET['eliminar'], $idChofer);
        header("Location: rides.php");
        exit;
    }

    // ✅ Guardar (crear o editar)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $rideObj->guardarRide($_POST, $idChofer); // valida año ≥ 2010, espacios ≤ 5 y ≤ capacidad, reglas de costo
            header("Location: rides.php");
            exit;
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
        }
    }

    // ✅ Editar
    $rideEditar = null;
    if (isset($_GET['editar'])) {
        $rideEditar = $rideObj->obtenerRide((int)$_GET['editar'], $idChofer);
    }

    // ✅ Listas
    $rides = $rideObj->obtenerRides($idChofer);
    $vehiculos = $rideObj->obtenerVehiculos($idChofer); // debe traer capacidad y anno
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
                <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
                <img src="<?= htmlspecialchars($fotoUsuario); ?>" class="user-photo" alt="Usuario">
                <a href="../logica/cerrarSesion.php" class="logout-link">Salir</a>
            </div>
        </nav>

        <section class="container">
            <h2><?= $rideEditar ? "Editar Ride" : "Registrar Ride"; ?></h2>

            <?php if (!empty($errorMsg)): ?>
                <p class="alert" style="color:#b91c1c;background:#fee2e2;border:1px solid #fecaca;padding:.75rem;border-radius:.5rem;">
                    <?= htmlspecialchars($errorMsg) ?>
                </p>
            <?php endif; ?>

            <form method="POST" class="formulario" id="formRide">
                <?php if ($rideEditar): ?>
                    <input type="hidden" name="id_ride" value="<?= (int)$rideEditar['id_ride']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div>
                        <label>Nombre:</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($rideEditar['nombre'] ?? ''); ?>" required>
                    </div>

                    <div>
                        <label>Inicio:</label>
                        <input type="text" name="inicio" value="<?= htmlspecialchars($rideEditar['inicio'] ?? ''); ?>" required>
                    </div>

                    <div>
                        <label>Destino:</label>
                        <input type="text" name="fin" value="<?= htmlspecialchars($rideEditar['fin'] ?? ''); ?>" required>
                    </div>

                    <div>
                        <label>Hora:</label>
                        <input type="time" name="hora" value="<?= htmlspecialchars($rideEditar['hora'] ?? ''); ?>" required>
                    </div>

                    <div>
                        <label>Día:</label>
                        <select name="dia" required>
                            <?php
                            $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
                            foreach ($dias as $d) {
                                $sel = ($rideEditar && $rideEditar['dia'] === $d) ? 'selected' : '';
                                echo "<option value=\"".htmlspecialchars($d)."\" $sel>".htmlspecialchars($d)."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label>Vehículo:</label>
                        <select name="id_vehiculo" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($vehiculos as $v): ?>
                                <option
                                    value="<?= (int)$v['id_vehiculo']; ?>"
                                    <?= ($rideEditar && (int)$rideEditar['id_vehiculo'] === (int)$v['id_vehiculo']) ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars(
                                        $v['marca'] . " " . $v['modelo'] . " (" . $v['placa'] . ") — cap: " .
                                        (int)$v['capacidad'] . ", año: " . (int)$v['anno']
                                    ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>El sistema impide guardar si los espacios superan la capacidad o si el vehículo es < 2010.</small>
                    </div>

                    <div>
                        <label>Costo (₡):</label>
                        <input type="number" name="costo" step="0.01" min="0"
                            value="<?= htmlspecialchars($rideEditar['costo'] ?? ''); ?>" required>
                        <small>Mínimos: ₡500 base, ₡700 fin de semana, ₡800 nocturno (22:00–05:59). Máx: ₡10 000.</small>
                    </div>

                    <div>
                        <label>Espacios:</label>
                        <input type="number" name="espacios" min="1" max="5"
                            value="<?= htmlspecialchars($rideEditar['espacios'] ?? ''); ?>" required>
                        <small>Máximo 5; si supera la capacidad del vehículo, no se guardará.</small>
                    </div>
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
                            <td>₡<?= number_format((float)$r['costo'], 2); ?></td>
                            <td><?= (int)$r['espacios']; ?></td>
                            <td>
                                <a href="?editar=<?= (int)$r['id_ride']; ?>" class="btn status-btn btn-on">✏️ Editar</a>
                                <a href="?eliminar=<?= (int)$r['id_ride']; ?>" class="btn status-btn btn-off"
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
