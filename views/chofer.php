<?php
// =====================================================
// Script: chofer.php (Vista/Controlador).
// Descripción: Panel principal para choferes: gestionar rides.
// Creado por: Jimena Jara y Fernanda Sibaja.
// =====================================================

session_start();
include("../includes/conexion.php");
include("../logica/rides.php");

// Seguridad: solo chofer
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
    header("Location: login.php");
    exit;
}

// Instanciar clase Ride
$ride = new Ride($conexion);
$idChofer = (int)$_SESSION['id_usuario'];

// Eliminar ride
if (isset($_GET['eliminar'])) {
    $ride->eliminarRide((int)$_GET['eliminar'], $idChofer);
    header("Location: chofer.php");
    exit;
}

// Guardar (crear o editar)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $data = $_POST;
        $ride->guardarRide($data, $idChofer);
        header("Location: chofer.php");
        exit;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// Editar ride
$editar = null;
if (isset($_GET['editar'])) {
    $editar = $ride->obtenerRide((int)$_GET['editar'], $idChofer);
}

// Obtener lista de rides y vehículos
$rides = $ride->obtenerRides($idChofer);
$vehiculos = $ride->obtenerVehiculos($idChofer);

// Foto de usuario del sistema
if (!empty($_SESSION['foto'])) {
    // Si ya trae "uploads/" entonces la ruta es correcta
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

// Coordenadas por defecto (San José)
$inicioLat = $editar['inicio_lat'] ?? 9.934739;
$inicioLng = $editar['inicio_lng'] ?? -84.087502;
$finLat    = $editar['fin_lat'] ?? 9.934739;
$finLng    = $editar['fin_lng'] ?? -84.087502;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Chofer | Aventones</title>

    <link rel="stylesheet" href="../assets/Estilos/chofer.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body>
    <header class="hero-header">
        <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" class="logo-hero">
        <h1>Bienvenido <span class="resaltado">Aventones.com</span></h1>
        <h2>Tu mejor opción para viajar seguros</h2>
    </header>

    <nav class="toolbar">
        <div class="toolbar-left">
            <a href="chofer.php" class="nav-link active">Rides</a>
            <a href="vehiculos.php" class="nav-link">Vehículos</a>
            <a href="choferReservas.php" class="nav-link">Reservas</a>
        </div>
        <div class="toolbar-right">
            <span class="user-name">Hola, <?= htmlspecialchars($_SESSION['nombre']); ?></span>
            <img src="<?= htmlspecialchars($fotoUsuario); ?>" class="user-photo" alt="Usuario">
            <a href="editarPerfil.php" class="edit-btn">Editar Perfil</a>
            <a href="../logica/cerrarSesion.php" class="logout-btn">Salir</a>
        </div>
    </nav>

    <section class="container">

        <h2><?= $editar ? "Editar Ride" : "Nuevo Ride"; ?></h2>
        <!-- Mensaje de error -->
        <?php if (!empty($errorMsg)): ?>
            <p class="alert" style="color:red;background:#fee;border:1px solid #faa;padding:.7rem;border-radius:6px;">
                <?= htmlspecialchars($errorMsg) ?>
            </p>
        <?php endif; ?>

        <form method="POST" class="formulario" id="formRide">

            <?php if ($editar): ?>
                <input type="hidden" name="id_ride" value="<?= (int)$editar['id_ride']; ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div>
                    <label>Nombre:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($editar['nombre'] ?? ''); ?>" required>
                </div>

                <div>
                    <label>Inicio (Origen):</label>
                    <input type="text" id="inicio" name="inicio" value="<?= htmlspecialchars($editar['inicio'] ?? ''); ?>" readonly required>
                </div>

                <div>
                    <label>Destino:</label>
                    <input type="text" id="fin" name="fin" value="<?= htmlspecialchars($editar['fin'] ?? ''); ?>" readonly required>
                </div>

                <div>
                    <label>Hora:</label>
                    <input type="time" name="hora" value="<?= htmlspecialchars($editar['hora'] ?? ''); ?>" required>
                </div>

                <div>
                    <label>Día:</label>
                    <select name="dia" required>
                        <!-- Opciones de días de la semana -->
                        <?php
                        $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
                        foreach ($dias as $d) {
                            $sel = ($editar && $editar['dia'] === $d) ? 'selected' : '';
                            echo "<option value=\"$d\" $sel>$d</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label>Vehículo:</label>
                    <select name="id_vehiculo" required>
                        <option value="">Seleccione...</option>
                        <!-- Opciones de vehículos -->
                        <?php foreach ($vehiculos as $v): ?>
                        <option value="<?= (int)$v['id_vehiculo']; ?>"
                            <?= ($editar && (int)$editar['id_vehiculo'] === (int)$v['id_vehiculo']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($v['marca']." ".$v['modelo']." (".$v['placa'].")"); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Costo (₡):</label>
                    <input type="number" step="0.01" min="0" name="costo"
                        value="<?= htmlspecialchars($editar['costo'] ?? ''); ?>" required>
                </div>

                <div>
                    <label>Espacios:</label>
                    <input type="number" name="espacios" min="1" max="5"
                        value="<?= htmlspecialchars($editar['espacios'] ?? ''); ?>" required>
                </div>
            </div>

            <!-- Coordenadas ocultas -->
            <input type="hidden" id="inicio_lat" name="inicio_lat" value="<?= $inicioLat ?>">
            <input type="hidden" id="inicio_lng" name="inicio_lng" value="<?= $inicioLng ?>">
            <input type="hidden" id="fin_lat" name="fin_lat" value="<?= $finLat ?>">
            <input type="hidden" id="fin_lng" name="fin_lng" value="<?= $finLng ?>">

            <!-- Mapa con mensaje -->
            <div id="map-hint">🗺️ Haz clic en el mapa para seleccionar el <b>origen</b>.</div>
            <div id="map"></div>

            <div class="center">
                <input type="submit" value="<?= $editar ? 'Actualizar Ride' : 'Guardar Ride'; ?>" class="btn btn-blue">
            </div>

        </form>

        <!-- Listado de rides -->
        <h2>Rides Registrados</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Origen</th>
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
                            <td><?= date("h:i A", strtotime($r['hora'])); ?></td>
                            <td><?= htmlspecialchars($r['dia']); ?></td>
                            <td><?= htmlspecialchars($r['marca']." ".$r['modelo']); ?></td>
                            <td>₡<?= number_format((float)$r['costo'], 2); ?></td>
                            <td><?= (int)$r['espacios']; ?></td>
                            <td>
                                <div class="acciones">
                                    <a href="?editar=<?= (int)$r['id_ride']; ?>" class="action-btn action-edit">
                                        <span>✏️</span> Editar
                                    </a>
                                    <a href="?eliminar=<?= (int)$r['id_ride']; ?>" class="action-btn action-delete"
                                        onclick="return confirm('¿Eliminar este ride?');">
                                        <span>🗑️</span> Eliminar
                                    </a>
                                </div>
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

    <!-- MAPA -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {

        const map = L.map("map").setView([9.934739, -84.087502], 9);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
        }).addTo(map);

        let markerInicio = null;
        let markerFin = null;
        let polyline = null;

        let seleccion = "inicio"; 

        const hint = document.getElementById("map-hint");

        async function reverseGeocode(lat, lng) {
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
            const data = await res.json();
            return data;
        }

        map.on("click", async e => {
            const { lat, lng } = e.latlng;
            const data = await reverseGeocode(lat, lng);

            const direccion = data.display_name || "";
            const provincia = data.address?.state || "";

            // Limitar solo provincia Alajuela
            if (!provincia.toLowerCase().includes("alajuela")) {
                hint.classList.add("map-error");
                hint.innerHTML = "⚠️ Solo se permiten ubicaciones dentro de <b>Alajuela</b>.";
                return;
            }

            // Quitar mensaje error si está dentro de Alajuela
            hint.classList.remove("map-error");

            if (seleccion === "inicio") {

                if (markerInicio) map.removeLayer(markerInicio);
                markerInicio = L.marker([lat, lng]).addTo(map).bindPopup("📍 Origen").openPopup();

                document.getElementById("inicio").value = direccion;
                document.getElementById("inicio_lat").value = lat;
                document.getElementById("inicio_lng").value = lng;

                seleccion = "fin";
                hint.innerHTML = "📍 Ahora haz clic para seleccionar el <b>destino</b> en Alajuela.";

            } else {

                if (markerFin) map.removeLayer(markerFin);
                markerFin = L.marker([lat, lng]).addTo(map).bindPopup("🏁 Destino").openPopup();

                document.getElementById("fin").value = direccion;
                document.getElementById("fin_lat").value = lat;
                document.getElementById("fin_lng").value = lng;

                seleccion = "inicio";
                hint.innerHTML = "✅ Origen y destino listos. Puedes guardar el ride.";
            }

            // Dibujar línea
            if (markerInicio && markerFin) {
                if (polyline) map.removeLayer(polyline);

                polyline = L.polyline([
                    [document.getElementById("inicio_lat").value, document.getElementById("inicio_lng").value],
                    [document.getElementById("fin_lat").value, document.getElementById("fin_lng").value]
                ], { color: "blue" }).addTo(map);
            }

        });
    });
    </script>

</body>
</html>