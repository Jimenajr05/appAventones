<?php
include_once("../includes/conexion.php");

class Ride {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    private function obtenerVehiculoDelChofer($idVehiculo, $idChofer) {
        $sql = "SELECT id_vehiculo, capacidad, anno FROM vehiculos WHERE id_vehiculo=? AND id_chofer=?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $idVehiculo, $idChofer);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function validarCosto($costo, $dia, $hora) {
        // Reglas de ejemplo (ajústalas si deseas):
        // - Costo mínimo base: ₡500
        // - Fines de semana (Sábado/Domingo): mínimo ₡700
        // - Horario nocturno (22:00 - 05:59): mínimo ₡800
        $min = 500;
        $dia = trim($dia);

        if (in_array($dia, ['Sábado','Sabado','Domingo'], true)) {
            $min = max($min, 700);
        }

        $h = (int)substr($hora, 0, 2);
        if ($h >= 22 || $h <= 5) {
            $min = max($min, 800);
        }

        if ($costo < $min) {
            throw new Exception("El costo ingresado (₡{$costo}) es menor al mínimo permitido para ese día/horario (₡{$min}).");
        }

        // También puedes limitar máximos razonables, p.ej. ₡10 000
        if ($costo > 10000) {
            throw new Exception("El costo ingresado excede el máximo permitido (₡10 000).");
        }
    }

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

    public function guardarRide($data, $idChofer) {
        $idVehiculo = (int)$data['id_vehiculo'];
        $espacios = (int)$data['espacios'];
        $costo = (float)$data['costo'];
        $dia = $data['dia'];
        $hora = $data['hora'];

        // 1) Validar vehículo y capacidad
        $veh = $this->obtenerVehiculoDelChofer($idVehiculo, $idChofer);
        if (!$veh) {
            throw new Exception("Vehículo inválido.");
        }
        if ($veh['anno'] < 2010) {
            throw new Exception("Este vehículo no cumple el año mínimo (2010).");
        }
        if ($espacios < 1) {
            throw new Exception("Debe indicar al menos 1 espacio.");
        }
        if ($espacios > 5) {
            throw new Exception("No se permiten más de 5 espacios por ride.");
        }
        if ($espacios > (int)$veh['capacidad']) {
            throw new Exception("Los espacios del ride ($espacios) no pueden exceder la capacidad del vehículo ({$veh['capacidad']}).");
        }

        // 2) Validar costo con reglas
        $this->validarCosto($costo, $dia, $hora);

        if (empty($data['id_ride'])) {
            $sql = "INSERT INTO rides (id_chofer, id_vehiculo, nombre, inicio, fin, hora, dia, costo, espacios)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param(
                "iisssssdi",
                $idChofer,
                $idVehiculo,
                $data['nombre'],
                $data['inicio'],
                $data['fin'],
                $hora,
                $dia,
                $costo,
                $espacios
            );
        } else {
            $sql = "UPDATE rides 
                    SET id_vehiculo=?, nombre=?, inicio=?, fin=?, hora=?, dia=?, costo=?, espacios=?
                    WHERE id_ride=? AND id_chofer=?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param(
                "isssssdiii",
                $idVehiculo,
                $data['nombre'],
                $data['inicio'],
                $data['fin'],
                $hora,
                $dia,
                $costo,
                $espacios,
                $data['id_ride'],
                $idChofer
            );
        }
        return $stmt->execute();
    }

    public function obtenerRide($idRide, $idChofer) {
        $sql = "SELECT * FROM rides WHERE id_ride=? AND id_chofer=?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $idRide, $idChofer);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function eliminarRide($idRide, $idChofer) {
        $sql = "DELETE FROM rides WHERE id_ride=? AND id_chofer=?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $idRide, $idChofer);
        return $stmt->execute();
    }

    public function obtenerVehiculos($idChofer) {
        $sql = "SELECT id_vehiculo, marca, modelo, placa, capacidad, anno 
                FROM vehiculos WHERE id_chofer=?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idChofer);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
