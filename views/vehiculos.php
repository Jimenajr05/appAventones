<!--
    // =====================================================
    // Script: vehiculos.php
    // Descripción: Panel de **CRUD de Vehículos** del Chofer.
    // Muestra el formulario de registro/edición y la lista
    // de vehículos. La lógica es `../logica/vehiculos.php`.
    // Creado por: Jimena y Fernanda.
    // =====================================================
-->
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

    // 🔹 Obtener lista de vehículos
    $sql = "SELECT * FROM vehiculos WHERE id_chofer = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idChofer);
    $stmt->execute();
    $vehiculos = $stmt->get_result();

    // 🔹 Modo edición
    $vehiculoEdit = null;
    if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id'])) {
        $idVehiculo = $_GET['id'];
        $stmt = $conexion->prepare("SELECT * FROM vehiculos WHERE id_vehiculo=? AND id_chofer=?");
        $stmt->bind_param("ii", $idVehiculo, $idChofer);
        $stmt->execute();
        $vehiculoEdit = $stmt->get_result()->fetch_assoc();
    }
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Gestión de Vehículos | Aventones</title>
        <link rel="stylesheet" href="../assets/Estilos/vehiculos.css">
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
                <a href="chofer.php" class="nav-link">Rides</a>
                <a href="vehiculos.php" class="nav-link active">Vehículos</a>
                <a href="choferReservas.php" class="nav-link">Reservas</a>
            </div>
            <div class="toolbar-right">
                <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
                <img src="<?= htmlspecialchars($fotoUsuario); ?>" alt="Usuario" class="user-photo">
                <a href="../logica/cerrarSesion.php" class="logout-btn">Salir</a>
            </div>
        </nav>

        <!-- 🧾 CONTENIDO PRINCIPAL -->
        <section class="container">
            <?php if ($mensaje): ?>
                <p class="alert"><?= htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <h2><?= $vehiculoEdit ? "Editar Vehículo" : "Registrar Nuevo Vehículo"; ?></h2>

            <form action="../logica/vehiculos.php" method="POST" enctype="multipart/form-data" class="formulario">
                <?php if ($vehiculoEdit): ?>
                    <input type="hidden" name="id_vehiculo" value="<?= $vehiculoEdit['id_vehiculo']; ?>">
                <?php endif; ?>

                <?php
                $opcionesColor = [
                    "Blanco","Negro","Gris","Plata","Azul","Rojo","Verde",
                    "Amarillo","Naranja","Café","Beige","Vino","Turquesa","Morado"
                ];
                $colorActual = $vehiculoEdit['color'] ?? '';
                ?>

                <div class="form-grid">
                    <div>
                        <label>Marca:</label>
                        <input type="text" name="marca" required value="<?= $vehiculoEdit['marca'] ?? ''; ?>">
                    </div>

                    <div>
                        <label>Modelo:</label>
                        <input type="text" name="modelo" required value="<?= $vehiculoEdit['modelo'] ?? ''; ?>">
                    </div>

                    <div>
                        <label>Placa:</label>
                        <input type="text" name="placa" required value="<?= $vehiculoEdit['placa'] ?? ''; ?>">
                    </div>

                    <div>
                        <label>Color:</label>
                        <select name="color" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($opcionesColor as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($opt === $colorActual ? 'selected' : '') ?>>
                                    <?= $opt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Año:</label>
                        <input type="number" name="anno" min="2010" max="<?= date('Y') ?>"
                            value="<?= htmlspecialchars($vehiculoEdit['anno'] ?? '') ?>" required>
                        <small>Solo modelos 2010 o más nuevos.</small>
                    </div>

                    <div>
                        <label>Capacidad:</label>
                        <input type="number" name="capacidad" min="1" max="5"
                            value="<?= htmlspecialchars($vehiculoEdit['capacidad'] ?? '') ?>" required>
                        <small>Máximo 5 asientos.</small>
                    </div>

                    <div>
                        <label>Fotografía:</label>
                        <input type="file" name="fotografia" accept=".jpg,.jpeg,.png">
                    </div>
                </div>

                <div class="center">
                    <input type="submit"
                        value="<?= $vehiculoEdit ? 'Actualizar Vehículo' : 'Guardar Vehículo'; ?>"
                        class="btn btn-blue">
                </div>
            </form>

            <h2>Mis Vehículos Registrados</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Placa</th>
                            <th>Color</th>
                            <th>Año</th>
                            <th>Capacidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($v = $vehiculos->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($v['fotografia']): ?>
                                    <img src="../<?= htmlspecialchars($v['fotografia']); ?>" width="70">
                                <?php else: ?>
                                    <small>Sin imagen</small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($v['marca']); ?></td>
                            <td><?= htmlspecialchars($v['modelo']); ?></td>
                            <td><?= htmlspecialchars($v['placa']); ?></td>
                            <td><?= htmlspecialchars($v['color']); ?></td>
                            <td><?= htmlspecialchars($v['anno']); ?></td>
                            <td><?= htmlspecialchars($v['capacidad']); ?></td>
                            <td>
                                <a href="?accion=editar&id=<?= $v['id_vehiculo']; ?>" class="btn status-btn btn-on">✏️ Editar</a>
                                <a href="../logica/vehiculos.php?accion=eliminar&id=<?= $v['id_vehiculo']; ?>"
                                class="btn status-btn btn-off"
                                onclick="return confirm('¿Eliminar este vehículo?');">🗑️ Eliminar</a>
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
