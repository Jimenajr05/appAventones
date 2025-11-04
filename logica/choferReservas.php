<?php
// =====================================================
// LOGICA: ChoferReservas.php
// Lista reservas de los rides del chofer
// Creado por: Jimena y Fernanda.
// =====================================================

class ChoferReservas {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerReservas($idChofer) {
        $sql = "SELECT r.id_reserva, r.estado, r.fecha_reserva,
                       u.nombre AS pasajero,
                       d.nombre AS ride, d.inicio, d.fin, d.dia, d.hora
                FROM reservas r
                JOIN rides d ON r.id_ride = d.id_ride
                JOIN usuarios u ON r.id_pasajero = u.id_usuario
                WHERE d.id_chofer = ?
                ORDER BY r.fecha_reserva DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idChofer);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}