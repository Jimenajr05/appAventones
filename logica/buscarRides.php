<!--
    // =====================================================
    // Script: buscarRides.php (Lógica)
    // Descripción: **Controlador de Búsqueda**. Procesa filtros
    // (GET), ejecuta la consulta principal de rides y guarda
    // los **resultados en sesión** para la vista.
    // Creado por: Fernanda y Jimena.
    // =====================================================
-->
<?php
session_start();
include("../includes/conexion.php");

// Variables para búsqueda
$origen = isset($_GET['origen']) ? trim($_GET['origen']) : '';
$destino = isset($_GET['destino']) ? trim($_GET['destino']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'fecha';
$direccion = isset($_GET['direccion']) ? $_GET['direccion'] : 'ASC';

// Columnas válidas para ordenar
$columnas = [
    'fecha' => 'r.dia',
    'origen' => 'r.inicio',
    'destino' => 'r.fin'
];

$columnaOrden = isset($columnas[$ordenar]) ? $columnas[$ordenar] : 'r.dia';
$direccionOrden = ($direccion === 'DESC') ? 'DESC' : 'ASC';

// Consulta principal con JOIN
$sql = "SELECT 
            r.id_ride,
            r.inicio,
            r.fin,
            r.dia,
            r.hora,
            r.costo,
            r.espacios,

            v.marca,
            v.modelo,
            v.placa,
            v.anno,
            v.fotografia AS foto_vehiculo,

            u.nombre AS nombre_chofer,
            u.apellido AS apellido_chofer,
            u.fotografia AS foto_chofer
        FROM rides r
        JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
        JOIN usuarios u ON r.id_chofer = u.id_usuario
        WHERE 1=1";


// Filtros
if (!empty($origen)) {
    $sql .= " AND r.inicio LIKE '%" . mysqli_real_escape_string($conexion, $origen) . "%'";
}
if (!empty($destino)) {
    $sql .= " AND r.fin LIKE '%" . mysqli_real_escape_string($conexion, $destino) . "%'";
}

$sql .= " ORDER BY $columnaOrden $direccionOrden";

$resultado = mysqli_query($conexion, $sql);
$rides = [];

if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        // Asegurar rutas de imagen
        if (empty($row['foto_chofer'])) {
            $row['foto_chofer'] = "uploads/usuarios/default-user.png";
        }
        if (empty($row['foto_vehiculo'])) {
            $row['foto_vehiculo'] = "uploads/vehiculos/default-car.png";
        }

        $rides[] = $row;
    }
}

include("../includes/cerrarConexion.php");

// Enviar array de rides a la vista
$_SESSION['rides_data'] = $rides;

// Redirigir de vuelta a la vista
header("Location: ../views/buscarRides.php");
exit;
?>