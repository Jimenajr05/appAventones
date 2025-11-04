<?php
// =====================================================
// Lógica: buscarRide.php
// Descripción: Clase para buscar rides según filtros y orden.
// Creado por: Jimena Jara y Fernanda Sibaja.
// =====================================================

class BuscarRide
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    // Buscar rides según filtros y orden
    public function buscar($origen, $destino, $ordenar, $direccion)
    {
        // Validar columnas permitidas para ordenar
        $permitidos = ['fecha', 'origen', 'destino', 'costo', 'espacios'];
        if (!in_array($ordenar, $permitidos, true)) $ordenar = 'fecha';
        // Validar dirección
        $direccion = strtoupper($direccion) === 'DESC' ? 'DESC' : 'ASC';

        // Consulta base: rides + vehículo + chofer
        $sql = "SELECT r.*, 
                       v.marca, v.modelo, v.anno, v.placa, v.fotografia AS foto_vehiculo,
                       u.nombre AS nombre_chofer, u.fotografia AS foto_chofer
                FROM rides r
                JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
                JOIN usuarios u ON r.id_chofer = u.id_usuario
                WHERE 1=1";
        // Parámetros para consulta preparada
        $params = [];
        $types  = "";

        // Filtro por origen si viene texto
        if ($origen !== "") {
            $sql .= " AND r.inicio LIKE ?";
            $params[] = "%$origen%";
            $types   .= "s";
        }

        // Filtro por destino si viene texto
        if ($destino !== "") {
            $sql .= " AND r.fin LIKE ?";
            $params[] = "%$destino%";
            $types   .= "s";
        }

        // Orden personalizado por día (para lista lógica Lunes–Domingo)
        $ordenDia = "FIELD(r.dia,
            'Lunes','Martes','Miércoles','Miercoles','Jueves','Viernes','Sábado','Sabado','Domingo'
        )";

        // Convertir hora a formato tiempo real para ordenar
        $ordenHora = "STR_TO_DATE(r.hora, '%H:%i:%s')";

        // Definir orden final según columna solicitada
        switch ($ordenar) {
            case 'origen': 
                $sql .= " ORDER BY r.inicio $direccion"; 
                break;
            case 'destino': 
                $sql .= " ORDER BY r.fin $direccion"; 
                break;
            case 'costo': 
                $sql .= " ORDER BY r.costo $direccion"; 
                break;
            case 'espacios': 
                $sql .= " ORDER BY r.espacios $direccion"; 
                break;
            default:
                $sql .= " ORDER BY $ordenDia $direccion, $ordenHora $direccion";
                break;
        }

        // Preparar y ejecutar consulta segura
        $stmt = $this->conexion->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        // Ejecutar y obtener resultados
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}