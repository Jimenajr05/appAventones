<?php
    // =====================================================
    // Lógica: notificarReservas.php
    // Descripción: Notifica a los choferes sobre reservas pendientes
    // mayores a X minutos (por consola).
    // Ejecutar con: C:\xampp\php\php.exe logica\notificarReservas.php 5
    // Creado por: Jimena Jara y Fernanda Sibaja.
    // =====================================================

    date_default_timezone_set('America/Costa_Rica');

    // Configuración
    $servidor = "localhost";
    $usuario  = "root";
    $clave    = "";
    $base     = "aventones";

    // Minutos desde consola (default 5)
    $minutos = isset($argv[1]) ? intval($argv[1]) : 5;

    // Colors para CLI
    $yellow = "\033[33m";
    $green  = "\033[32m";
    $red    = "\033[31m";
    $cyan   = "\033[36m";
    $bold   = "\033[1m";
    $reset  = "\033[0m";

    // Inicio 
    echo "\n{$cyan}{$bold}🔍 Buscando reservas pendientes mayores a $minutos minutos...{$reset}\n";
    echo "🕓 Hora actual: " . date('Y-m-d H:i:s') . " CST\n\n";

    // Conexión BD
    $conexion = new mysqli($servidor, $usuario, $clave, $base);

    if ($conexion->connect_error) {
        die("{$red}❌ Error de conexión: {$conexion->connect_error}{$reset}\n");
    }

    // Consulta
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
        die("{$red}❌ Error en la consulta: {$conexion->error}{$reset}\n");
    }

    // Agrupar pendientes 
    $reservasPendientes = [];

    // Recorrer resultados
    while ($fila = $resultado->fetch_assoc()) {
        if ($fila['minutos_pendiente'] > $minutos) {
            $reservasPendientes[$fila['id_chofer']][] = $fila;
        }
    }

    if (empty($reservasPendientes)) {
        echo "{$green}✅ No hay reservas pendientes mayores a $minutos minutos.\n{$reset}";
        $conexion->close();
        exit;
    }

    // Email y Log
    require_once __DIR__ . '/../includes/correoReservas.php';
    $emailService = new EmailService();

    $log = __DIR__ . '/../logs/notificaciones.log';
    if (!is_dir(dirname($log))) mkdir(dirname($log), 0755, true);

    // Tabla CLI 
    echo "{$yellow}=============================================================\n";
    echo " CHOFER           | RIDE             | MIN ESPERA\n";
    echo "============================================================={$reset}\n";

    // Enviar notificaciones
    foreach ($reservasPendientes as $idChofer => $reservas) {

        $chofer = $reservas[0]['chofer'];
        $correo = $reservas[0]['correo_chofer'];
        $detallesHTML = "";

        foreach ($reservas as $r) {
            $detallesHTML .= "<li>Reserva #{$r['id_reserva']} de {$r['ride']} (pendiente hace {$r['minutos_pendiente']} minutos)</li>";

            $min = $r['minutos_pendiente'];
            $horas = floor($min / 60);
            $mins = $min % 60;

            if ($horas > 0) {
                $tiempoTexto = "{$horas}h {$mins}m";
            } else {
                $tiempoTexto = "{$mins}m";
            }

            printf(
                " %-15s | %-15s | %s\n",
                $r['chofer'],
                $r['ride'],
                $tiempoTexto
            );

        }

        $enviado = $emailService->enviarNotificacionReserva($correo, $chofer, $detallesHTML);

        $horaLocal = date('Y-m-d H:i:s');
        $horaUTC = gmdate('Y-m-d H:i:s');
        $msg = "[$horaLocal CST / $horaUTC UTC] $chofer ($correo) - " . count($reservas) . " reservas pendientes\n";
        file_put_contents($log, $msg, FILE_APPEND);

        if ($enviado) {
            echo "{$green}📧 Notificación enviada a $chofer ($correo){$reset}\n";
        } else {
            echo "{$red}❌ Error al enviar correo a $chofer ($correo){$reset}\n";
        }
    }

    echo "\n{$green}✅ Proceso completado. Log guardado en: {$log}{$reset}\n";

    $conexion->close();
?>