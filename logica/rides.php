<?php
    // =====================================================
    // Lógica: rides.php
    // Descripción: Manejo de rides para choferes.
    // Creado por: Jimena Jara y Fernanda Sibaja.
    // ===================================================== 

    include_once("../includes/conexion.php");

    // Clase para manejar rides
    class Ride {
        private $conexion;

        public function __construct($conexion) {
            $this->conexion = $conexion;
        }

        // Obtener vehículo del chofer para validaciones
        private function obtenerVehiculoDelChofer($idVehiculo, $idChofer) {
            $sql = "SELECT id_vehiculo, capacidad, anno FROM vehiculos WHERE id_vehiculo=? AND id_chofer=?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ii", $idVehiculo, $idChofer);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        // Función para validar el costo según las reglas
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

        // Función para validar rides duplicados
        private function validarRideDuplicado($idChofer, $idVehiculo, $dia, $hora, $idRide = null) {
            if ($idRide) {
                // Validación cuando EDITA
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

        // Obtener todos los rides de un chofer
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

        // Obtener un ride específico
        public function obtenerRide($idRide, $idChofer) {
            $sql = "SELECT * FROM rides WHERE id_ride=? AND id_chofer=?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ii", $idRide, $idChofer);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }

        // Guardar (crear o editar) un ride
        public function guardarRide($data, $idChofer) {
            $idVehiculo = (int)$data['id_vehiculo'];
            $espacios = (int)$data['espacios'];
            $costo = (float)$data['costo'];
            $dia = $data['dia'];
            $hora = $data['hora'];
            $idRide = $data['id_ride'] ?? null; // Obtener el ID del ride si existe

            // Validar vehículo
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

            // Lógica para condicionar la validación de duplicado 
            $debeValidarDuplicado = true;

            // Si es edición, verificamos si los campos clave cambiaron
            if ($idRide) {
                $rideOriginal = $this->obtenerRide((int)$idRide, $idChofer);
                
                // Comprobamos si los campos clave (vehículo, día, hora) son IGUALES
                if ($rideOriginal && 
                    (int)$rideOriginal['id_vehiculo'] === $idVehiculo &&
                    $rideOriginal['dia'] === $dia && 
                    $rideOriginal['hora'] === $hora
                ) {
                    // Si todos los campos clave son iguales, no es necesario validar duplicado.
                    $debeValidarDuplicado = false;
                }
            }
            
            // Solo validamos duplicado si es un ride nuevo O si cambió algún campo clave
            if ($debeValidarDuplicado) {
                $this->validarRideDuplicado($idChofer, $idVehiculo, $dia, $hora, $idRide);
            }

            // Preparar y ejecutar la consulta
            if (empty($idRide)) {
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
                
                // Verificación de error 
                if ($stmt === false) {
                    throw new Exception("Error interno: Falló la preparación de la consulta de actualización. Revise los nombres de sus columnas en la tabla 'rides'.");
                }

                // Vincular parámetros para actualización
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
                    $idRide,
                    $idChofer
                );
            }
            
            // Manejo de ejecución y errores MySQL
            if (!$stmt->execute()) {
                if (empty($idRide) && $stmt->errno == 1062) {
                    throw new Exception("⚠ Ya existe un ride con este vehículo el mismo día y a la misma hora.");
                }
                throw new Exception("Error al guardar el ride: " . $stmt->error);
            }

            return true;
        }

        // Eliminar un ride
        public function eliminarRide($idRide, $idChofer) {
            $sql = "DELETE FROM rides WHERE id_ride=? AND id_chofer=?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ii", $idRide, $idChofer);
            return $stmt->execute();
        }

        // Obtener vehículos de un chofer
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