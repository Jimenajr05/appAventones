<?php
include_once("../includes/conexion.php");

class Pasajero {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Buscar rides por origen o destino
    public function buscarRides($busqueda) {
        $sql = "SELECT r.id_ride, r.inicio, r.fin, r.hora, r.dia, r.costo, r.espacios,
                       v.placa, u.nombre AS chofer
                FROM rides r
                INNER JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
                INNER JOIN usuarios u ON v.id_chofer = u.id_usuario
                WHERE r.inicio LIKE ? OR r.fin LIKE ?";
        $stmt = $this->conexion->prepare($sql);
        $like = "%" . $busqueda . "%";
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Reservar un ride
    public function reservarRide($idRide, $idPasajero) {
        $sql = "INSERT INTO reservas (id_ride, id_pasajero, estado) VALUES (?, ?, 'pendiente')";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $idRide, $idPasajero);
        return $stmt->execute();
    }

    // Mostrar reservas del pasajero
    public function obtenerReservas($idPasajero) {
        $sql = "SELECT r.id_reserva, r.estado, r.fecha_reserva, d.inicio, d.fin, d.hora, d.dia
                FROM reservas r
                INNER JOIN rides d ON r.id_ride = d.id_ride
                WHERE r.id_pasajero = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idPasajero);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Cancelar una reserva
    public function cancelarReserva($idReserva, $idPasajero) {
        $sql = "UPDATE reservas SET estado='cancelada'
                WHERE id_reserva=? AND id_pasajero=? AND estado IN ('pendiente', 'aceptada')";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $idReserva, $idPasajero);
        return $stmt->execute();
    }
}
?>