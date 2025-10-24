<?php
session_start();
include("../includes/conexion.php");

// Inicializar variables
$origen = isset($_GET['origen']) ? trim($_GET['origen']) : '';
$destino = isset($_GET['destino']) ? trim($_GET['destino']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'fecha';
$direccion = isset($_GET['direccion']) ? $_GET['direccion'] : 'ASC';

// Validar el ordenamiento
$ordenamientosPermitidos = ['fecha', 'origen', 'destino'];
if (!in_array($ordenar, $ordenamientosPermitidos)) {
    $ordenar = 'fecha';
}

// Validar la dirección
if ($direccion !== 'ASC' && $direccion !== 'DESC') {
    $direccion = 'ASC';
}

// Construir la consulta base
$sql = "SELECT r.*, v.marca, v.modelo, v.anno 
        FROM rides r 
        JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo 
        WHERE 1=1";

$params = [];
$types = "";

// Agregar filtros si se proporcionaron
if (!empty($origen)) {
    $sql .= " AND r.inicio LIKE ?";
    $params[] = "%$origen%";
    $types .= "s";
}

if (!empty($destino)) {
    $sql .= " AND r.fin LIKE ?";
    $params[] = "%$destino%";
    $types .= "s";
}

// Agregar ordenamiento
switch ($ordenar) {
    case 'origen':
        $sql .= " ORDER BY r.inicio";
        break;
    case 'destino':
        $sql .= " ORDER BY r.fin";
        break;
    default:
        $sql .= " ORDER BY r.dia, r.hora";
}
$sql .= " " . $direccion;

// Preparar y ejecutar la consulta
$stmt = mysqli_prepare($conexion, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$rides = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);
include("../includes/cerrarConexion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Búsqueda de Rides</title>
    <link rel="stylesheet" href="../assets/Estilos/buscarRides.css">
</head>
<body>
<header>
    <h1>🚗 Búsqueda de Rides</h1>
    <nav>
        <?php if (isset($_SESSION['tipo'])): ?>
            <a href="../views/dashboard.php">Volver al Panel</a> |
            <a href="../logica/cerrarSesion.php">Cerrar Sesión</a>
        <?php else: ?>
            <a href="../views/login.php">Iniciar Sesión</a>
        <?php endif; ?>
    </nav>
</header>

<section class="container">
    <form method="GET" action="buscarRides.php" class="busqueda">
        <label>Origen:</label>
        <input type="text" name="origen" value="<?= htmlspecialchars($origen) ?>">

        <label>Destino:</label>
        <input type="text" name="destino" value="<?= htmlspecialchars($destino) ?>">

        <label>Ordenar por:</label>
        <select name="ordenar">
            <option value="fecha" <?= $ordenar == 'fecha' ? 'selected' : '' ?>>Fecha</option>
            <option value="origen" <?= $ordenar == 'origen' ? 'selected' : '' ?>>Origen</option>
            <option value="destino" <?= $ordenar == 'destino' ? 'selected' : '' ?>>Destino</option>
        </select>

        <select name="direccion">
            <option value="ASC" <?= $direccion == 'ASC' ? 'selected' : '' ?>>Ascendente</option>
            <option value="DESC" <?= $direccion == 'DESC' ? 'selected' : '' ?>>Descendente</option>
        </select>

        <input type="submit" value="Buscar">
    </form>

    <h2>Resultados de Rides</h2>

    <?php if (count($rides) > 0): ?>
        <table>
            <tr>
                <th>Nombre</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Vehículo</th>
                <th>Costo</th>
                <th>Espacios</th>
                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'pasajero'): ?>
                    <th>Acción</th>
                <?php endif; ?>
            </tr>
            <?php foreach ($rides as $r): ?>
                <tr>
                    <td><?= $r['nombre']; ?></td>
                    <td><?= $r['inicio']; ?></td>
                    <td><?= $r['fin']; ?></td>
                    <td><?= $r['dia']; ?></td>
                    <td><?= $r['hora']; ?></td>
                    <td><?= $r['marca'] . " " . $r['modelo'] . " (" . $r['anno'] . ")"; ?></td>
                    <td>₡<?= number_format($r['costo'], 2); ?></td>
                    <td><?= $r['espacios']; ?></td>
                    <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'pasajero'): ?>
                        <td><a href="../logica/reservas.php?accion=crear&id_ride=<?= $r['id_ride']; ?>">Reservar</a></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center;">No se encontraron rides con esos criterios.</p>
    <?php endif; ?>
</section>
</body>
</html>
