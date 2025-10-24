<?php
session_start();
include("../includes/conexion.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
    header("Location: ../views/login.php");
    exit;
}

$idChofer = $_SESSION['id_usuario'];
$accion = $_GET['accion'] ?? '';
$mensaje = "";

// 🧩 CREAR O ACTUALIZAR VEHÍCULO
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $placa = $_POST['placa'];
    $color = $_POST['color'];
    $anno = $_POST['anno'];
    $capacidad = $_POST['capacidad'];

    // Subida de fotografía
    $foto_ruta = "";
    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == 0) {
        $nombreArchivo = $_FILES['fotografia']['name'];
        $tmp = $_FILES['fotografia']['tmp_name'];
        $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png'])) {
            $nuevoNombre = uniqid("vehiculo_") . "." . $ext;
            $destino = "../uploads/vehiculos/" . $nuevoNombre;
            if (move_uploaded_file($tmp, $destino)) {
                $foto_ruta = "uploads/vehiculos/" . $nuevoNombre;
            }
        }
    }

    if (!empty($_POST['id_vehiculo'])) {
        // 🧰 ACTUALIZAR
        $idVehiculo = $_POST['id_vehiculo'];
        $foto_sql = $foto_ruta ? ", fotografia='$foto_ruta'" : "";
        $sql = "UPDATE vehiculos 
                SET marca='$marca', modelo='$modelo', placa='$placa', color='$color', anno='$anno', capacidad='$capacidad' $foto_sql
                WHERE id_vehiculo='$idVehiculo' AND id_chofer='$idChofer'";
        $mensaje = mysqli_query($conexion, $sql) ? "Vehículo actualizado correctamente." : "Error al actualizar.";
    } else {
        // ➕ CREAR
        $sql = "INSERT INTO vehiculos (id_chofer, marca, modelo, placa, color, anno, capacidad, fotografia)
                VALUES ('$idChofer', '$marca', '$modelo', '$placa', '$color', '$anno', '$capacidad', '$foto_ruta')";
        $mensaje = mysqli_query($conexion, $sql) ? "Vehículo agregado correctamente." : "Error al agregar vehículo.";
    }

    header("Location: ../views/vehiculos.php?msg=" . urlencode($mensaje));
    exit;
}

// ❌ ELIMINAR VEHÍCULO
if ($accion === "eliminar" && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM vehiculos WHERE id_vehiculo='$id' AND id_chofer='$idChofer'";
    $mensaje = mysqli_query($conexion, $sql) ? "Vehículo eliminado correctamente." : "Error al eliminar.";
    header("Location: ../views/vehiculos.php?msg=" . urlencode($mensaje));
    exit;
}

include("../includes/cerrarConexion.php");
?>
