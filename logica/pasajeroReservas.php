<?php
// =====================================================
// LOGICA: PasajeroReservas.php
// Maneja reservas del pasajero: listar y cancelar
// Creado por: Jimena y Fernanda.
// =====================================================

class PasajeroReservas {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

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