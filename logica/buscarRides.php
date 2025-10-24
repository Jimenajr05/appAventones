<?php
include("../includes/conexion.php");

// Variables para búsqueda
$origen = isset($_GET['origen']) ? $_GET['origen'] : '';
$destino = isset($_GET['destino']) ? $_GET['destino'] : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'fecha';
$direccion = isset($_GET['direccion']) ? $_GET['direccion'] : 'ASC';

// Mapeo de columnas seguras para ordenar
$columnas = [
    'fecha' => 'r.dia',
    'origen' => 'r.inicio',
    'destino' => 'r.fin'
];

$columnaOrden = isset($columnas[$ordenar]) ? $columnas[$ordenar] : 'r.dia';
$direccionOrden = ($direccion === 'DESC') ? 'DESC' : 'ASC';

// Base de la consulta
$sql = "SELECT r.id_ride, r.nombre, r.inicio, r.fin, r.hora, r.dia, r.costo, r.espacios,
               v.marca, v.modelo, v.anno
        FROM rides r
        INNER JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
        WHERE 1=1";

// Filtros de búsqueda
if (!empty($origen)) {
    $sql .= " AND r.inicio LIKE '%" . mysqli_real_escape_string($conexion, $origen) . "%'";
}
if (!empty($destino)) {
    $sql .= " AND r.fin LIKE '%" . mysqli_real_escape_string($conexion, $destino) . "%'";
}

// Ordenamiento
$sql .= " ORDER BY $columnaOrden $direccionOrden";

$resultado = mysqli_query($conexion, $sql);

$rides = [];
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $rides[] = $row;
    }
}

include("../includes/cerrarConexion.php");
?>
