<?php
// =====================================================
// VISTA: buscarRides.php (Pasajero)
// Muestra filtros, tabla de rides y mapa Leaflet
// Llama a la lógica /logica/BuscarRide.php
// Creado por: Fernanda y Jimena.
// =====================================================

session_start();
include("../includes/conexion.php");
include("../logica/buscarRides.php");

// Foto usuario
if (!empty($_SESSION['foto'])) {
    $fotoUsuario = (str_starts_with($_SESSION['foto'], 'uploads/'))
        ? "../" . $_SESSION['foto'] . "?v=" . time()
        : "../assets/Estilos/Imagenes/" . $_SESSION['foto'] . "?v=" . time();
} else {
    $fotoUsuario = "../assets/Estilos/Imagenes/default-user.png";
}

// Parámetros GET
$origen     = trim($_GET['origen'] ?? '');
$destino    = trim($_GET['destino'] ?? '');
$ordenar    = $_GET['ordenar'] ?? 'fecha';
$direccion  = strtoupper($_GET['direccion'] ?? 'ASC');

// Ejecutar la lógica
$buscador = new BuscarRide($conexion);
$rides = $buscador->buscar($origen, $destino, $ordenar, $direccion);

// Coordenadas default para el mapa
$inicioLat = 9.934739;
$inicioLng = -84.087502;
$finLat    = 9.934739;
$finLng    = -84.087502;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Rides | Aventones</title>
    <link rel="stylesheet" href="../assets/Estilos/buscarRides.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body>

<header class="header-pasajero">
    <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" class="logo-hero">
    <h1>Bienvenido a <span class="resaltado">Aventones.com</span></h1>
    <h2>Encuentra tu viaje de forma rápida y segura</h2>
</header>

<nav class="toolbar">
    <div class="toolbar-left">
        <a href="pasajero.php" class="nav-link">Panel de Pasajero</a>
        <a href="buscarRides.php" class="nav-link active">Buscar Rides</a>
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

    <!-- Formulario de búsqueda -->
    <form method="GET" action="buscarRides.php" class="busqueda">

        <label>Origen:</label>
        <input type="text" name="origen" id="origen"
               value="<?= htmlspecialchars($origen) ?>"
               placeholder="Selecciona en el mapa o escribe...">

        <label>Destino:</label>
        <input type="text" name="destino" id="destino"
               value="<?= htmlspecialchars($destino) ?>"
               placeholder="Selecciona en el mapa o escribe...">

        <label>Ordenar por:</label>
        <select name="ordenar">
            <option value="fecha"   <?= $ordenar=='fecha'?'selected':'' ?>>Día y Hora</option>
            <option value="origen"  <?= $ordenar=='origen'?'selected':'' ?>>Origen</option>
            <option value="destino" <?= $ordenar=='destino'?'selected':'' ?>>Destino</option>
            <option value="costo"   <?= $ordenar=='costo'?'selected':'' ?>>Costo</option>
            <option value="espacios"<?= $ordenar=='espacios'?'selected':'' ?>>Espacios</option>
        </select>

        <select name="direccion">
            <option value="ASC"  <?= $direccion=='ASC'?'selected':''  ?>>Ascendente</option>
            <option value="DESC" <?= $direccion=='DESC'?'selected':'' ?>>Descendente</option>
        </select>

        <input type="submit" value="Buscar">
        <input type="button" value="Limpiar" onclick="window.location.href='buscarRides.php'">

    </form>

    <div id="map-hint">🗺️ Haz clic en el mapa para elegir <b>origen</b> y <b>destino</b>.</div>
    <div id="map"></div>

    <!-- Mensaje de error -->
    <?php 
        $error_msg = $_GET['error'] ?? '';
        if ($error_msg): 
    ?>
        <p style="color:red;text-align:center;border:1px solid red;padding:10px;margin-bottom:20px;background:#ffe0e0;border-radius:5px;">
            ❌ <?= htmlspecialchars($error_msg); ?>
        </p>
    <?php endif; ?>

    <h2>Resultados de Rides</h2>

    <?php if (count($rides) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>Chofer</th>
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
                </thead>

                <tbody>
                <?php foreach ($rides as $r): ?>
                    <tr>
                        <td>
                            <img src="<?= '../' . ($r['foto_chofer'] ?? 'uploads/usuarios/default-user.png'); ?>"
                                 style="width:45px;height:45px;border-radius:50%;object-fit:cover;border:2px solid #0c6f63;"><br>
                            <?= htmlspecialchars($r['nombre_chofer']); ?>
                        </td>

                        <td><?= htmlspecialchars($r['inicio']); ?></td>
                        <td><?= htmlspecialchars($r['fin']); ?></td>
                        <td><?= htmlspecialchars($r['dia']); ?></td>
                        <td><?= date("h:i A", strtotime($r['hora'])); ?></td>

                        <td>
                            <img src="<?= '../' . ($r['foto_vehiculo'] ?? 'uploads/vehiculos/default-car.png'); ?>"
                                 style="width:65px;height:45px;border-radius:6px;object-fit:cover;"><br>
                            <?= htmlspecialchars($r['marca'] . ' ' . $r['modelo'] . ' ' . $r['placa']); ?>
                        </td>

                        <td>₡<?= number_format($r['costo'], 2); ?></td>
                        <td><?= htmlspecialchars($r['espacios']); ?></td>

                        <?php if ($_SESSION['tipo'] === 'pasajero'): ?>
                            <td>
                                <a href="../logica/reservas.php?accion=crear&id_ride=<?= $r['id_ride']; ?>" class="btn-reservar">
                                    Reservar
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    <?php else: ?>
        <p style="text-align:center;">No se encontraron rides con esos criterios.</p>
    <?php endif; ?>

</section>

<footer>
    <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const mapDiv = document.getElementById("map");
    mapDiv.style.height = "400px";

    const map = L.map('map').setView([10.01625, -84.21163], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    let marcadorOrigen = null, marcadorDestino = null;
    let seleccion = "origen";
    const hint = document.getElementById("map-hint");

    function esAlajuela(texto) {
        return texto.toLowerCase().includes("alajuela");
    }

    async function getLugar(lat, lng) {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
        const data = await res.json();
        return data.display_name || `${lat}, ${lng}`;
    }

    map.on("click", async e => {
        const { lat, lng } = e.latlng;
        const lugar = await getLugar(lat, lng);

        if (!esAlajuela(lugar)) {
            hint.classList.add("map-error");
            hint.innerHTML = "❌ Solo se permiten ubicaciones dentro de <b>Alajuela</b>";
            return;
        } else {
            hint.classList.remove("map-error");
        }

        if (seleccion === "origen") {
            if (marcadorOrigen) map.removeLayer(marcadorOrigen);
            marcadorOrigen = L.marker([lat, lng]).addTo(map).bindPopup("📍 Origen").openPopup();
            document.getElementById("origen").value = lugar;
            seleccion = "destino";
            hint.innerHTML = "📍 Ahora selecciona el <b>destino</b>.";
        } else {
            if (marcadorDestino) map.removeLayer(marcadorDestino);
            marcadorDestino = L.marker([lat, lng]).addTo(map).bindPopup("🏁 Destino").openPopup();
            document.getElementById("destino").value = lugar;
            seleccion = "origen";
            hint.innerHTML = "✅ Origen y destino listos. Puedes buscar.";
        }

        if (marcadorOrigen && marcadorDestino) {
            const puntos = [marcadorOrigen.getLatLng(), marcadorDestino.getLatLng()];
            L.polyline(puntos, { color: "blue" }).addTo(map);
        }
    });
});
</script>

</body>
</html>