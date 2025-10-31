<!--
    // =====================================================
    // Script: vehiculos.php
    // Descripción: Lógica del Chofer para gestionar sus **Vehículos**
    // (CRUD). Incluye validaciones (año, capacidad) y subida de fotos.
    // Creado por: Jimena y Fernanda.
    // =====================================================
-->
<?php
    session_start();
    include("../includes/conexion.php");

    // 🔒 Seguridad: solo chofer
    if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'chofer') {
        header("Location: ../views/login.php");
        exit;
    }

    $idChofer = $_SESSION['id_usuario'];
    $accion = $_GET['accion'] ?? '';
    $mensaje = "";

    /* ============================================================
    🧩 CREAR O ACTUALIZAR VEHÍCULO
    ============================================================ */
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        // 🔹 Sanitización básica
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $placa = trim($_POST['placa'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $anno = (int)($_POST['anno'] ?? 0);
        $capacidad = (int)($_POST['capacidad'] ?? 0);

        // 🔹 Validar color permitido
        $coloresPermitidos = ["Blanco","Negro","Gris","Plata","Azul","Rojo","Verde","Amarillo","Naranja","Café","Beige","Vino","Turquesa","Morado"];
        if (!in_array($color, $coloresPermitidos, true)) {
            header("Location: ../views/vehiculos.php?msg=" . urlencode("Color inválido."));
            exit;
        }

        // 🔹 Validaciones de negocio
        if ($anno < 2010) {
            header("Location: ../views/vehiculos.php?msg=" . urlencode("No se aceptan vehículos anteriores a 2010."));
            exit;
        }

        if ($capacidad < 1 || $capacidad > 5) {
            header("Location: ../views/vehiculos.php?msg=" . urlencode("La capacidad debe estar entre 1 y 5."));
            exit;
        }

        // ============================================================
        // 📸 Subida de fotografía
        // ============================================================
        $foto_ruta = "";
        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == 0) {
            $nombreArchivo = $_FILES['fotografia']['name'];
            $tmp = $_FILES['fotografia']['tmp_name'];
            $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $nuevoNombre = uniqid("vehiculo_") . "." . $ext;
                $destino = "../uploads/vehiculos/" . $nuevoNombre;

                if (move_uploaded_file($tmp, $destino)) {
                    $foto_ruta = "uploads/vehiculos/" . $nuevoNombre;
                }
            }
        }

        // ============================================================
        // 💾 Insertar o actualizar
        // ============================================================
        if (!empty($_POST['id_vehiculo'])) {
            // 🧾 Actualizar vehículo existente
            $idVehiculo = (int)$_POST['id_vehiculo'];

            if ($foto_ruta) {
                $sql = "UPDATE vehiculos 
                        SET marca=?, modelo=?, placa=?, color=?, anno=?, capacidad=?, fotografia=? 
                        WHERE id_vehiculo=? AND id_chofer=?";
                $stmt = $conexion->prepare($sql);
                // ✅ 9 variables -> 6 strings + 3 enteros
                $stmt->bind_param("sssssisii",
                    $marca, $modelo, $placa, $color, $anno, $capacidad, $foto_ruta, $idVehiculo, $idChofer
                );
            } else {
                $sql = "UPDATE vehiculos 
                        SET marca=?, modelo=?, placa=?, color=?, anno=?, capacidad=? 
                        WHERE id_vehiculo=? AND id_chofer=?";
                $stmt = $conexion->prepare($sql);
                // ✅ 8 variables -> 4 strings + 4 enteros
                $stmt->bind_param("ssssiiii",
                    $marca, $modelo, $placa, $color, $anno, $capacidad, $idVehiculo, $idChofer
                );
            }

            $ok = $stmt->execute();
            $mensaje = $ok ? "Vehículo actualizado correctamente." : "Error al actualizar vehículo.";

        } else {
            // 🆕 Insertar nuevo vehículo
            $sql = "INSERT INTO vehiculos (id_chofer, marca, modelo, placa, color, anno, capacidad, fotografia)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("issssiss", 
                $idChofer, $marca, $modelo, $placa, $color, $anno, $capacidad, $foto_ruta
            );

            $ok = $stmt->execute();
            $mensaje = $ok ? "Vehículo agregado correctamente." : "Error al agregar vehículo.";
        }

        header("Location: ../views/vehiculos.php?msg=" . urlencode($mensaje));
        exit;
    }

    /* ============================================================
    ❌ ELIMINAR VEHÍCULO
    ============================================================ */
    if ($accion === "eliminar" && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $sql = "DELETE FROM vehiculos WHERE id_vehiculo=? AND id_chofer=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id, $idChofer);
        $ok = $stmt->execute();
        $mensaje = $ok ? "Vehículo eliminado correctamente." : "Error al eliminar.";
        header("Location: ../views/vehiculos.php?msg=" . urlencode($mensaje));
        exit;
    }

    include("../includes/cerrarConexion.php");
?>
