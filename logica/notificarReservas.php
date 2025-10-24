<?php
// =====================================================
// Script: notificarReservas.php
// Descripción: Revisa las reservas pendientes en la BD
// y notifica (por consola o log) a los choferes si
// tienen solicitudes sin responder por más de X minutos.
// Llamar con: C:\xampp\php\php.exe logica\notificarReservas.php 15
// =====================================================

// ---------------- CONFIGURACIÓN ----------------
date_default_timezone_set('America/Costa_Rica'); // ✅ Fija la hora local correcta

$servidor = "localhost";
$usuario  = "root";
$clave    = "";
$base     = "aventones";

// Minutos por defecto si no se especifica en la consola
$minutos = isset($argv[1]) ? intval($argv[1]) : 5;

echo "🔍 Buscando reservas pendientes mayores a $minutos minutos...\n";
echo "🕓 Hora actual del servidor: " . date('Y-m-d H:i:s') . " (America/Costa_Rica)\n\n";

// ---------------- CONEXIÓN A LA BASE DE DATOS ----------------
$conexion = new mysqli($servidor, $usuario, $clave, $base);

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error . "\n");
}

// ---------------- CONSULTA DE RESERVAS ----------------
$sql = "SELECT 
            r.id_reserva, r.id_ride, r.fecha_reserva,
            TIMESTAMPDIFF(MINUTE, r.fecha_reserva, NOW()) AS minutos_pendiente,
            rd.nombre AS ride, rd.id_chofer,
            c.nombre AS chofer, c.correo AS correo_chofer
        FROM reservas r
        INNER JOIN rides rd ON r.id_ride = rd.id_ride
        INNER JOIN usuarios c ON rd.id_chofer = c.id_usuario
        WHERE r.estado = 'pendiente'
        ORDER BY r.fecha_reserva ASC";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("❌ Error en la consulta: " . $conexion->error . "\n");
}

// ---------------- PROCESAR RESULTADOS ----------------
$reservasPendientes = [];

while ($fila = $resultado->fetch_assoc()) {
    if ($fila['minutos_pendiente'] > $minutos) {
        $reservasPendientes[$fila['id_chofer']][] = $fila;
    }
}

if (empty($reservasPendientes)) {
    echo "✅ No hay reservas que superen los $minutos minutos.\n";
    $conexion->close();
    exit;
}

// ---------------- NOTIFICAR A CHOFERES ----------------
require_once __DIR__ . '/../includes/mail.php';
$emailService = new EmailService();

// Ruta del archivo de log
$log = __DIR__ . '/../logs/notificaciones.log';
if (!is_dir(dirname($log))) {
    mkdir(dirname($log), 0755, true);
}

foreach ($reservasPendientes as $idChofer => $reservas) {
    $chofer = $reservas[0]['chofer'];
    $correo = $reservas[0]['correo_chofer'];

    // Preparar detalles de reservas para el correo
    $detallesHTML = "";
    foreach ($reservas as $r) {
        $detallesHTML .= "<li>Reserva #{$r['id_reserva']} para {$r['ride']} (pendiente hace {$r['minutos_pendiente']} minutos)</li>";
        echo " - Reserva #{$r['id_reserva']} ({$r['ride']}) hace {$r['minutos_pendiente']} min\n";
    }

    // Enviar correo electrónico
    if ($emailService->enviarNotificacionReserva($correo, $chofer, $detallesHTML)) {
        echo "📧 Correo enviado a $chofer ($correo)\n";
    } else {
        echo "❌ Error al enviar correo a $chofer ($correo)\n";
    }

    // Guardar en archivo de log con hora local y UTC
    $horaLocal = date('Y-m-d H:i:s');
    $horaUTC = gmdate('Y-m-d H:i:s');
    $mensaje = "[$horaLocal CST / $horaUTC UTC] $chofer ($correo) - " . count($reservas) . " reservas pendientes\n";

    file_put_contents($log, $mensaje, FILE_APPEND);
}

echo "\n✅ Proceso completado. Notificaciones registradas en: $log\n";

$conexion->close();
