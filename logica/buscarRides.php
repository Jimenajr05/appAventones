<?php
// =====================================================
// Lógica: BuscarRide.php
// Retorna lista de rides filtrada para el pasajero
// =====================================================

class BuscarRide
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function buscar($origen, $destino, $ordenar, $direccion)
    {
        $permitidos = ['fecha', 'origen', 'destino', 'costo', 'espacios'];
        if (!in_array($ordenar, $permitidos, true)) $ordenar = 'fecha';
        $direccion = strtoupper($direccion) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT r.*, 
                       v.marca, v.modelo, v.anno, v.placa, v.fotografia AS foto_vehiculo,
                       u.nombre AS nombre_chofer, u.fotografia AS foto_chofer
                FROM rides r
                JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
                JOIN usuarios u ON r.id_chofer = u.id_usuario
                WHERE 1=1";

        $params = [];
        $types  = "";

        if ($origen !== "") {
            $sql .= " AND r.inicio LIKE ?";
            $params[] = "%$origen%";
            $types   .= "s";
        }
        if ($destino !== "") {
            $sql .= " AND r.fin LIKE ?";
            $params[] = "%$destino%";
            $types   .= "s";
        }

        $ordenDia = "FIELD(r.dia,
            'Lunes','Martes','Miércoles','Miercoles','Jueves','Viernes','Sábado','Sabado','Domingo'
        )";

        $ordenHora = "STR_TO_DATE(r.hora, '%H:%i:%s')";

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

        $stmt = $this->conexion->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}