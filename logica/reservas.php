<?php
session_start();
include("../includes/conexion.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/login.php");
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$tipo = $_SESSION['tipo'];
$accion = $_GET['accion'] ?? '';

// Función para verificar si el ride pertenece al chofer
function esRideDelChofer($conexion, $idReserva, $idChofer) {
    $sql = "SELECT 1 FROM reservas r 
            JOIN rides ri ON r.id_ride = ri.id_ride 
            WHERE r.id_reserva = ? AND ri.id_chofer = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $idReserva, $idChofer);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

// Función para verificar espacios disponibles
function hayEspaciosDisponibles($conexion, $idRide) {
    // Calcular la cantidad de espacios disponibles restando las reservas
    // pendientes/aceptadas. Usamos SUM(CASE ...) y COALESCE para que cuando
    // no haya filas de reservas el cálculo devuelva el total de espacios.
    $sql = "SELECT r.espacios - COALESCE(SUM(CASE WHEN res.estado IN ('pendiente','aceptada') THEN 1 ELSE 0 END), 0) AS disponibles
            FROM rides r
            LEFT JOIN reservas res ON r.id_ride = res.id_ride
            WHERE r.id_ride = ?
            GROUP BY r.id_ride";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idRide);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return ($row && intval($row['disponibles']) > 0);
}

switch ($accion) {
    // 🧩 CREAR RESERVA
    case "crear":
        if ($tipo !== 'pasajero') {
            header("Location: ../views/dashboard.php?error=No autorizado");
            exit;
        }
        
        $idRide = $_GET['id_ride'] ?? 0;
        
        // Verificar si ya existe una reserva pendiente o aceptada
        $stmt = mysqli_prepare($conexion, 
            "SELECT 1 FROM reservas 
             WHERE id_ride = ? AND id_pasajero = ? 
             AND estado IN ('pendiente', 'aceptada')");
        mysqli_stmt_bind_param($stmt, "ii", $idRide, $idUsuario);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            header("Location: ../views/buscarRides.php?error=Ya tienes una reserva para este ride");
            exit;
        }
        
        // Verificar espacios disponibles
        if (!hayEspaciosDisponibles($conexion, $idRide)) {
            header("Location: ../views/buscarRides.php?error=No hay espacios disponibles");
            exit;
        }
        
        // Crear la reserva
        $stmt = mysqli_prepare($conexion, 
            "INSERT INTO reservas (id_ride, id_pasajero, estado) VALUES (?, ?, 'pendiente')");
        mysqli_stmt_bind_param($stmt, "ii", $idRide, $idUsuario);
        mysqli_stmt_execute($stmt);
        header("Location: ../views/pasajero.php?msg=Reserva enviada con éxito");
        break;

    // ❌ CANCELAR RESERVA (solo pasajero)
    case "cancelar":
        if ($tipo !== 'pasajero') {
            header("Location: ../views/dashboard.php?error=No autorizado");
            exit;
        }
        
        $idReserva = $_GET['id'] ?? 0;
        $stmt = mysqli_prepare($conexion, 
            "UPDATE reservas 
             SET estado = 'cancelada'
             WHERE id_reserva = ? 
             AND id_pasajero = ? 
             AND estado IN ('pendiente','aceptada')");
        mysqli_stmt_bind_param($stmt, "ii", $idReserva, $idUsuario);
        mysqli_stmt_execute($stmt);
        header("Location: ../views/pasajero.php?msg=Reserva cancelada");
        break;

    // ✅ ACEPTAR RESERVA (solo chofer)
    case "aceptar":
        if ($tipo !== 'chofer') {
            header("Location: ../views/dashboard.php?error=No autorizado");
            exit;
        }
        
        $idReserva = $_GET['id'] ?? 0;
        
        // Verificar que el ride pertenece al chofer
        if (!esRideDelChofer($conexion, $idReserva, $idUsuario)) {
            header("Location: ../views/chofer_reservas.php?error=No autorizado");
            exit;
        }
        
        $stmt = mysqli_prepare($conexion, 
            "UPDATE reservas 
             SET estado = 'aceptada'
             WHERE id_reserva = ? AND estado = 'pendiente'");
        mysqli_stmt_bind_param($stmt, "i", $idReserva);
        mysqli_stmt_execute($stmt);
        header("Location: ../views/chofer_reservas.php?msg=Reserva aceptada");
        break;

    // 🚫 RECHAZAR RESERVA (solo chofer)
    case "rechazar":
        if ($tipo !== 'chofer') {
            header("Location: ../views/dashboard.php?error=No autorizado");
            exit;
        }
        
        $idReserva = $_GET['id'] ?? 0;
        
        // Verificar que el ride pertenece al chofer
        if (!esRideDelChofer($conexion, $idReserva, $idUsuario)) {
            header("Location: ../views/chofer_reservas.php?error=No autorizado");
            exit;
        }
        
        $stmt = mysqli_prepare($conexion, 
            "UPDATE reservas 
             SET estado = 'rechazada'
             WHERE id_reserva = ? AND estado = 'pendiente'");
        mysqli_stmt_bind_param($stmt, "i", $idReserva);
        mysqli_stmt_execute($stmt);
        header("Location: ../views/chofer_reservas.php?msg=Reserva rechazada");
        break;

    default:
        header("Location: ../views/dashboard.php");
}
?>