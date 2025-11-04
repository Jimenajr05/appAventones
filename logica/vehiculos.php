<?php
    // =====================================================
    // Script: vehiculos.php (Lógica)
    // Descripción: **Controlador CRUD de Vehículos**. Gestiona
    // la **Creación, Edición y Eliminación** de vehículos
    // de un Chofer, incluyendo el manejo de la **fotografía**.
    // Creado por: Fernanda y Jimena.
    // =====================================================

    session_start();
    include("../includes/conexion.php");

    // Seguridad: solo chofer
    if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
        header("Location: ../views/login.php");
        exit;
    }

    $idChofer = $_SESSION['id_usuario'];
    $accion = $_GET['accion'] ?? '';
    $mensaje = "";

    // Crear o editar Vehículo
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        // Sanitización básica
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $placa = trim($_POST['placa'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $anno = (int)($_POST['anno'] ?? 0);
        $capacidad = (int)($_POST['capacidad'] ?? 0);
        
        // Validar color permitido (asumo que tienes esta lógica)
        $coloresPermitidos = [
            "Blanco","Negro","Gris","Plata","Azul","Rojo","Verde",
            "Amarillo","Naranja","Café","Beige","Vino","Turquesa","Morado"
        ];
        if (!in_array($color, $coloresPermitidos)) {
            $mensaje = "Error: El color seleccionado no es válido.";
            header("Location: ../views/vehiculos.php?msg=" . urlencode($mensaje));
            exit;
        }

        // MANEJO DE ARCHIVO: Generar la ruta para la base de datos
        $foto_ruta = null;
        $directorio_destino = "../uploads/vehiculos/"; 
        
        // Crear carpeta si no existe
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = uniqid("vehiculo-") . "." . $extension;
            $ruta_completa_servidor = $directorio_destino . $nombre_archivo;
            
            if (move_uploaded_file($_FILES['fotografia']['tmp_name'], $ruta_completa_servidor)) {
                // RUTA A GUARDAR EN LA BASE DE DATOS (relativa desde la raíz del proyecto)
                $foto_ruta = "uploads/vehiculos/" . $nombre_archivo; 
            } else {
                $mensaje = "Error al mover el archivo subido.";
                header("Location: ../views/vehiculos.php?msg=" . urlencode($mensaje));
                exit;
            }
        }

        // Insertar o actualizar
        if (!empty($_POST['id_vehiculo'])) {
            // Actualizar vehículo existente
            $idVehiculo = (int)$_POST['id_vehiculo'];
            $ok = false;

            if ($foto_ruta) {
                // Actualizar con nueva foto
                $sql = "UPDATE vehiculos 
                        SET marca=?, modelo=?, placa=?, color=?, anno=?, capacidad=?, fotografia=? 
                        WHERE id_vehiculo=? AND id_chofer=?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssssiisii",
                    $marca, $modelo, $placa, $color, $anno, $capacidad, $foto_ruta, $idVehiculo, $idChofer
                );
            } else {
                // Si no hay nueva foto, NO incluir la columna fotografia en el UPDATE
                $sql = "UPDATE vehiculos 
                        SET marca=?, modelo=?, placa=?, color=?, anno=?, capacidad=? 
                        WHERE id_vehiculo=? AND id_chofer=?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssssiiii",
                    $marca, $modelo, $placa, $color, $anno, $capacidad, $idVehiculo, $idChofer
                );
            }

            if ($stmt) {
                $ok = $stmt->execute();
            }
            $mensaje = $ok ? "Vehículo actualizado correctamente." : "Error al actualizar vehículo: " . $conexion->error;

        } else {
            // Insertar nuevo vehículo
            $sql = "INSERT INTO vehiculos (id_chofer, marca, modelo, placa, color, anno, capacidad, fotografia)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("issssiss", 
                $idChofer, $marca, $modelo, $placa, $color, $anno, $capacidad, $foto_ruta
            );

            $ok = $stmt->execute();
            $mensaje = $ok ? "Vehículo agregado correctamente." : "Error al agregar vehículo: " . $conexion->error;
        }

        header("Location: ../views/vehiculos.php?msg=" . urlencode($mensaje));
        exit;
    }

    // Eliminar vehículo
    if ($accion === "eliminar" && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        // Opcional: Obtener la ruta de la imagen para eliminarla del servidor
        $sqlSelect = "SELECT fotografia FROM vehiculos WHERE id_vehiculo=? AND id_chofer=?";
        $stmtSelect = $conexion->prepare($sqlSelect);
        $stmtSelect->bind_param("ii", $id, $idChofer);
        $stmtSelect->execute();
        $foto_a_eliminar = $stmtSelect->get_result()->fetch_assoc()['fotografia'] ?? null;
        
        // Eliminar registro de la DB
        $sql = "DELETE FROM vehiculos WHERE id_vehiculo=? AND id_chofer=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id, $idChofer);
        $ok = $stmt->execute();
        
        if ($ok) {
            $mensaje = "Vehículo eliminado correctamente.";
            // Eliminar archivo físico si existe
            if ($foto_a_eliminar && file_exists("../" . $foto_a_eliminar)) {
                unlink("../" . $foto_a_eliminar);
            }
        } else {
            $mensaje = "Error al eliminar el vehículo.";
        }
        
        header("Location: ../views/vehiculos.php?msg=" . urlencode($mensaje));
        exit;
    }

    // Si llega aquí sin POST o GET válido, redirige
    header("Location: ../views/chofer.php");
?>