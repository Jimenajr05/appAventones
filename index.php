<?php
session_start();
include("includes/conexion.php");

// --- Redirección si ya hay sesión activa ---
if (isset($_SESSION['tipo'])) {
    switch ($_SESSION['tipo']) {
        case 'administrador':
            header("Location: views/administrador.php");
            exit;
        case 'chofer':
            header("Location: views/chofer.php");
            exit;
        case 'pasajero':
            header("Location: views/pasajero.php");
            exit;
    }
}

// --- Búsqueda pública ---
$origen = trim($_GET['origen'] ?? '');
$destino = trim($_GET['destino'] ?? '');

// Consulta base con placeholders
$sql = "SELECT 
            r.id_ride, r.nombre, r.inicio, r.fin, r.hora, r.dia, r.costo, r.espacios,
            v.marca, v.modelo, v.anno
        FROM rides r
        INNER JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
        WHERE 1=1";

$params = [];
$types = "";

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

$sql .= " ORDER BY r.dia ASC, r.hora ASC";

$stmt = $conexion->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aventones - Inicio</title>
    <link rel="stylesheet" href="assets/Estilos/index.css">
</head>
<body>
<header>
    <img src="assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" width="170">
    <h1>Bienvenido a <span class="marca">Aventones.com</span></h1>
    <h2>Tu mejor opción para viajar seguros</h2>
</header>

<main class="container">
    <section class="intro">
        <p>Con Aventones puedes compartir viajes de manera cómoda, económica y segura.</p>
    </section>

    <!-- 🔍 Buscador público -->
    <section class="busqueda">
        <h2>Buscar Rides Disponibles</h2>
        <form method="GET" action="index.php" class="form-busqueda">
            <div class="campo">
                <label for="origen">Origen:</label>
                <input type="text" id="origen" name="origen" value="<?= htmlspecialchars($origen); ?>" placeholder="Ej: San Carlos">
            </div>

            <div class="campo">
                <label for="destino">Destino:</label>
                <input type="text" id="destino" name="destino" value="<?= htmlspecialchars($destino); ?>" placeholder="Ej: Alajuela">
            </div>

            <div class="acciones-form">
                <button type="submit" class="btn">Buscar</button>
                <a href="index.php" class="btn btn-secundario">Limpiar</a>
            </div>
        </form>

        <h3>Resultados:</h3>
        <?php if ($resultado && $resultado->num_rows > 0): ?>
        <div class="tabla-container">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Vehículo</th>
                        <th>Costo</th>
                        <th>Espacios</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nombre']); ?></td>
                        <td><?= htmlspecialchars($r['inicio']); ?></td>
                        <td><?= htmlspecialchars($r['fin']); ?></td>
                        <td><?= htmlspecialchars($r['dia']); ?></td>
                        <td><?= htmlspecialchars($r['hora']); ?></td>
                        <td><?= htmlspecialchars("{$r['marca']} {$r['modelo']} ({$r['anno']})"); ?></td>
                        <td>₡<?= number_format($r['costo'], 2); ?></td>
                        <td><?= htmlspecialchars($r['espacios']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="no-resultados">🚗 No se encontraron rides disponibles con esos criterios.</p>
        <?php endif; ?>
    </section>

    <!-- 🔐 Acciones -->
    <section class="acciones">
        <a href="views/login.php" class="btn">Iniciar Sesión</a>
        <a href="views/Registro.php" class="btn btn-secundario">Registrarse</a>
    </section>

    <section class="info-admin">
        <h3>¿Eres administrador?</h3>
        <p>Accede con tus credenciales asignadas.</p>
        <a href="views/login.php" class="link-admin">Ir al panel administrativo</a>
    </section>
    
</main>

<footer>
    <p>© <?= date('Y'); ?> Aventones | Universidad Técnica Nacional</p>
</footer>
</body>
</html>
