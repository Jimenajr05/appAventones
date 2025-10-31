<!--
    // =====================================================
    // Script: chofer.php
    // Descripción: Define la clase **Chofer**. Encapsula la lógica
    // principal para la gestión de **Aventones (Rides)** del chofer
    // (CRUD). También obtiene la lista de vehículos del chofer.
    // Creado por: Jimena y Fernanda.
    // =====================================================
-->
<?php
    include_once("../includes/conexion.php");

    class Chofer {
        private $conexion;

        public function __construct($conexion) {
            $this->conexion = $conexion;
        }

        // ✅ Crear Ride
        public function crearRide($idChofer, $idVehiculo, $nombre, $inicio, $fin, $hora, $dia, $costo, $espacios) {
            $sql = "INSERT INTO rides (id_chofer, id_vehiculo, nombre, inicio, fin, hora, dia, costo, espacios)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("iisssssdi", $idChofer, $idVehiculo, $nombre, $inicio, $fin, $hora, $dia, $costo, $espacios);
            return $stmt->execute();
        }

        // ✅ Obtener rides del chofer
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

        // ✅ Obtener ride específico
        public function obtenerRide($idRide, $idChofer) {
            $sql = "SELECT * FROM rides WHERE id_ride=? AND id_chofer=?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ii", $idRide, $idChofer);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        // ✅ Editar Ride
        public function editarRide($idRide, $idChofer, $nombre, $inicio, $fin, $hora, $dia, $costo, $espacios) {
            $sql = "UPDATE rides 
                    SET nombre=?, inicio=?, fin=?, hora=?, dia=?, costo=?, espacios=?
                    WHERE id_ride=? AND id_chofer=?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssssdi ii", $nombre, $inicio, $fin, $hora, $dia, $costo, $espacios, $idRide, $idChofer);
            return $stmt->execute();
        }

        // ✅ Eliminar Ride
        public function eliminarRide($idRide, $idChofer) {
            $sql = "DELETE FROM rides WHERE id_ride=? AND id_chofer=?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ii", $idRide, $idChofer);
            return $stmt->execute();
        }

        // ✅ Obtener vehículos disponibles del chofer
        public function obtenerVehiculos($idChofer) {
            $sql = "SELECT id_vehiculo, marca, modelo, placa FROM vehiculos WHERE id_chofer=?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $idChofer);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
?>