<?php
// =====================================================
// Lógica: pasajeroReservas.php
// Descripción: Maneja la lógica relacionada con las reservas de los pasajeros.
// Creado por: Jimena Jara y Fernanda Sibaja.
// =====================================================

// Clase PasajeroReservas
class PasajeroReservas {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Obtener reservas de un pasajero
    public function obtenerReservas($idPasajero) {
        $sql = "SELECT r.id_reserva, r.estado, r.fecha_reserva,
                       d.nombre AS ride, d.inicio, d.fin, d.dia, d.hora, d.costo,
                       u.nombre AS chofer
                FROM reservas r
                JOIN rides d ON r.id_ride = d.id_ride
                JOIN usuarios u ON d.id_chofer = u.id_usuario
                WHERE r.id_pasajero = ?
                ORDER BY r.fecha_reserva DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idPasajero);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}