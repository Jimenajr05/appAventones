<!--
    // =====================================================
    // Script: buscarRides.php (Vista Pasajero)
    // Descripción: Panel de **Búsqueda de Aventones**. Procesa
    // filtros GET y consulta la base de datos. Muestra
    // resultados en tabla e integra un **Mapa Leaflet**
    // para seleccionar origen/destino.
    // Creado por: Jimena y Fernanda.
    // =====================================================
-->
<?php
    session_start();
    include("../includes/conexion.php");

    $fotoUsuario = $_SESSION['foto'] ?? "../assets/Estilos/Imagenes/default-user.png";

    $origen = isset($_GET['origen']) ? trim($_GET['origen']) : '';
    $destino = isset($_GET['destino']) ? trim($_GET['destino']) : '';
    $ordenar = $_GET['ordenar'] ?? 'fecha';
    $direccion = $_GET['direccion'] ?? 'ASC';

    // Validaciones
    $permitidos = ['fecha', 'origen', 'destino'];
    if (!in_array($ordenar, $permitidos)) $ordenar = 'fecha';
    if ($direccion !== 'ASC' && $direccion !== 'DESC') $direccion = 'ASC';

    // Consulta
    $sql = "SELECT r.*, v.marca, v.modelo, v.anno 
            FROM rides r 
            JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo 
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

    // Ordenamiento
    switch ($ordenar) {
        case 'origen': $sql .= " ORDER BY r.inicio"; break;
        case 'destino': $sql .= " ORDER BY r.fin"; break;
        default: $sql .= " ORDER BY r.dia, r.hora";
    }
    $sql .= " $direccion";

    $stmt = $conexion->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $rides = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    include("../includes/cerrarConexion.php");

    // Coordenadas por defecto (San José)
    $inicioLat = 9.934739;
    $inicioLng = -84.087502;
    $finLat = 9.934739;
    $finLng = -84.087502;
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Buscar Rides | Aventones</title>
        <link rel="stylesheet" href="../assets/Estilos/buscarRides.css">

        <!-- 🌍 Leaflet -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    </head>
    <body>

        <!-- 🟢 HEADER -->
        <header class="header-pasajero">
            <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" class="logo-hero">
            <h1>Bienvenido a <span class="resaltado">Aventones.com</span></h1>
            <h2>Encuentra tu viaje de forma rápida y segura</h2>
        </header>

        <!-- ⚪ TOOLBAR -->
        <nav class="toolbar">
            <div class="toolbar-left">
                <a href="pasajero.php" class="nav-link">Panel de Pasajero</a>
                <a href="buscarRides.php" class="nav-link active">Buscar Rides</a>
                <a href="pasajeroReservas.php" class="nav-link">Mis Reservas</a>
            </div>
            <div class="toolbar-right">
                <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
                <img src="<?= htmlspecialchars($fotoUsuario); ?>" class="user-photo" alt="Usuario">
                <a href="../logica/cerrarSesion.php" class="logout-btn">Salir</a>
            </div>
        </nav>

        <!-- 🔍 FORMULARIO -->
        <section class="container">
            <form method="GET" action="buscarRides.php" class="busqueda">
                <label>Origen:</label>
                <input type="text" name="origen" id="origen" value="<?= htmlspecialchars($origen) ?>" placeholder="Selecciona en el mapa o escribe...">

                <label>Destino:</label>
                <input type="text" name="destino" id="destino" value="<?= htmlspecialchars($destino) ?>" placeholder="Selecciona en el mapa o escribe...">

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
                <input type="button" value="Limpiar" onclick="window.location.href='buscarRides.php'">
            </form>

            <!-- 🗺️ Mapa -->
            <div id="map-hint">🗺️ Haz clic en el mapa para elegir <b>origen</b> y <b>destino</b>.</div>
            <div id="map"></div>

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
                                    <!-- ✅ Foto + nombre chofer -->
                                    <td>
                                        <img src="<?= '../' . ($r['foto_chofer'] ?? 'uploads/usuarios/default-user.png'); ?>"
                                            style="width:45px;height:45px;border-radius:50%;object-fit:cover;border:2px solid #0c6f63;">

                                        <br>
                                        <?= htmlspecialchars($r['nombre_chofer']); ?>
                                    </td>

                                    <td><?= htmlspecialchars($r['inicio']); ?></td>
                                    <td><?= htmlspecialchars($r['fin']); ?></td>
                                    <td><?= htmlspecialchars($r['dia']); ?></td>
                                    <td><?= htmlspecialchars($r['hora']); ?></td>

                                    <!-- ✅ Vehículo + foto + placa -->
                                    <td>
                                        <img src="<?= '../' . ($r['foto_vehiculo'] ?? 'uploads/vehiculos/default-car.png'); ?>"
                                             style="width:65px;height:45px;border-radius:6px;object-fit:cover;">

                                        <br>
                                        <?= htmlspecialchars($r['marca'] . ' ' . $r['modelo'] . ' ' . $r['placa']); ?>

                                    </td>

                                    <td>₡<?= number_format($r['costo'], 2); ?></td>
                                    <td><?= htmlspecialchars($r['espacios']); ?></td>

                                    <?php if ($_SESSION['tipo'] === 'pasajero'): ?>
                                        <td><a href="../logica/reservas.php?accion=crear&id_ride=<?= $r['id_ride']; ?>" class="btn-reservar">Reservar</a></td>
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

        <!-- 🌍 SCRIPT MAPA LEAFLET -->
  <script>
document.addEventListener("DOMContentLoaded", () => {
    const mapDiv = document.getElementById("map");
    mapDiv.style.height = "400px";
    
    const map = L.map('map').setView([10.01625, -84.21163], 9); // Zona UTN Alajuela
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

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

    map.on("click", async (e) => {
        const { lat, lng } = e.latlng;
        const lugar = await getLugar(lat, lng);

        // ❗ Validar que la ubicación sea Alajuela
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
