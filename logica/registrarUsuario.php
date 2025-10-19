<?php
include("../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $contrasena = $_POST['contrasena'];
    $confirmar = $_POST['confirmar'];

    if ($contrasena !== $confirmar) {
        echo "<p style='color:red; text-align:center;'>❌ Las contraseñas no coinciden.</p>";
        echo "<p style='text-align:center;'><a href='../views/Registro.php'>Volver</a></p>";
        exit;
    }

    // --- Subir la imagen ---
    $foto_ruta = "";

    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == 0) {
        $nombreArchivo = $_FILES['fotografia']['name'];
        $tmp = $_FILES['fotografia']['tmp_name'];
        $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        // Validar tipo
        $permitidas = ['jpg', 'jpeg', 'png'];
        if (in_array($ext, $permitidas)) {
            // Generar nombre único
            $nuevoNombre = uniqid("user_") . "." . $ext;
            $destino = "../uploads/usuarios/" . $nuevoNombre;

            if (move_uploaded_file($tmp, $destino)) {
                $foto_ruta = "uploads/usuarios/" . $nuevoNombre;
            } else {
                echo "<p style='color:red; text-align:center;'>⚠️ Error al subir la imagen.</p>";
            }
        } else {
            echo "<p style='color:red; text-align:center;'>⚠️ Solo se permiten imágenes JPG o PNG.</p>";
        }
    }

    // --- Guardar en base de datos ---
    $sql = "INSERT INTO usuarios 
            (nombre, apellido, cedula, fecha_nacimiento, correo, telefono, fotografia, contrasena, tipo, estado)
            VALUES 
            ('$nombre', '$apellido', '$cedula', '$fecha_nacimiento', '$correo', '$telefono', '$foto_ruta', '$contrasena', 'pasajero', 'pendiente')";

    if (mysqli_query($conexion, $sql)) {
        echo "<p style='color:green; text-align:center;'>✅ Registro exitoso. Espera la activación del administrador.</p>";
        echo "<p style='text-align:center;'><a href='../views/Autenticacion.php'>Iniciar sesión</a></p>";
    } else {
        echo "<p style='color:red; text-align:center;'>❌ Error al registrar: " . mysqli_error($conexion) . "</p>";
        echo "<p style='text-align:center;'><a href='../views/Registro.php'>Volver</a></p>";
    }
}

include("../includes/cerrarConexion.php");
?>
