<?php
    // =====================================================
    // Script: rides.php (Lógica de Negocio)
    // Descripción: Define la clase **Ride**. Gestiona las
    // operaciones CRUD (Crear, Obtener, Actualizar, Eliminar)
    // de los aventones. Incluye validaciones de negocio como
    // capacidad del vehículo y reglas de costo/horario.
    // Creado por: Jimena y Fernanda.
    // ===================================================== 

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
            if ($costo > 30000) {
                throw new Exception("El costo ingresado excede el máximo permitido (₡30 000).");
            }
        }

        private function validarRideDuplicado($idChofer, $idVehiculo, $dia, $hora, $idRide = null) {
            if ($idRide) {
                // Validación cuando EDITA (excluye su propio ID)
                $sql = "SELECT id_ride FROM rides 
                        WHERE id_chofer=? AND id_vehiculo=? AND dia=? AND hora=? AND id_ride <> ?";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param("iissi", $idChofer, $idVehiculo, $dia, $hora, $idRide);
            } else {
                // Validación cuando CREA
                $sql = "SELECT id_ride FROM rides 
                        WHERE id_chofer=? AND id_vehiculo=? AND dia=? AND hora=?";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param("iiss", $idChofer, $idVehiculo, $dia, $hora);
            }

            $stmt->execute();
            $existe = $stmt->get_result()->fetch_assoc();

            if ($existe) {
                throw new Exception("⚠️ Ya existe un ride con este vehículo el mismo día y a la misma hora.");
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

            // Validar vehículo y capacidad
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
                throw new Exception("No puedes solicitar $espacios espacios para este ride, ya que la capacidad máxima del vehículo es de {$veh['capacidad']} espacios.");
            }

            // Validar costo con reglas
            $this->validarCosto($costo, $dia, $hora);

            // Validar ride duplicado ANTES DE GUARDAR
            $idRide = $data['id_ride'] ?? null;
            $this->validarRideDuplicado($idChofer, $idVehiculo, $dia, $hora, $idRide);

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
            // Manejo de ejecución y errores MySQL
            if (!$stmt->execute()) {
                if ($stmt->errno == 1062) {
                    throw new Exception("⚠️ Ya existe un ride con este vehículo el mismo día y a la misma hora.");
                }
                throw new Exception("Error al guardar el ride: " . $stmt->error);
            }

            return true;
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
?>