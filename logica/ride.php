<?php
include_once("../includes/conexion.php");

class Ride {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Obtener rides del chofer
    public function obtenerRides($idChofer) {
        $sql = "SELECT r.*, v.marca, v.modelo, v.placa 
                FROM rides r 
                INNER JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
                WHERE r.id_chofer = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idChofer);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Guardar ride (nuevo o editado)
    public function guardarRide($data, $idChofer) {
        if (empty($data['id_ride'])) {
            $sql = "INSERT INTO rides (id_chofer, id_vehiculo, nombre, inicio, fin, hora, dia, costo, espacios)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param(
                "iisssssdi",
                $idChofer,
                $data['id_vehiculo'],
                $data['nombre'],
                $data['inicio'],
                $data['fin'],
                $data['hora'],
                $data['dia'],
                $data['costo'],
                $data['espacios']
            );
        } else {
            $sql = "UPDATE rides 
                    SET id_vehiculo=?, nombre=?, inicio=?, fin=?, hora=?, dia=?, costo=?, espacios=?
                    WHERE id_ride=? AND id_chofer=?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param(
                "isssssdiii",
                $data['id_vehiculo'],
                $data['nombre'],
                $data['inicio'],
                $data['fin'],
                $data['hora'],
                $data['dia'],
                $data['costo'],
                $data['espacios'],
                $data['id_ride'],
                $idChofer
            );
        }
        return $stmt->execute();
    }

    // Obtener ride específico
    public function obtenerRide($idRide, $idChofer) {
        $sql = "SELECT * FROM rides WHERE id_ride=? AND id_chofer=?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $idRide, $idChofer);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Eliminar ride
    public function eliminarRide($idRide, $idChofer) {
        $sql = "DELETE FROM rides WHERE id_ride=? AND id_chofer=?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $idRide, $idChofer);
        return $stmt->execute();
    }

    // Obtener vehículos del chofer
    public function obtenerVehiculos($idChofer) {
        $sql = "SELECT id_vehiculo, marca, modelo, placa FROM vehiculos WHERE id_chofer=?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idChofer);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>