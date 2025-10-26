<?php
include_once("../includes/conexion.php");

class Administrador {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // 📋 Obtener usuarios
    public function obtenerUsuarios() {
        $sql = "SELECT id_usuario, nombre, apellido, correo, tipo, estado, fecha_registro 
                FROM usuarios ORDER BY fecha_registro DESC";
        $resultado = mysqli_query($this->conexion, $sql);
        $usuarios = [];
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $usuarios[] = $fila;
            }
        }
        return $usuarios;
    }

    // 🔄 Cambiar estado (activar/desactivar)
    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE usuarios SET estado='$nuevoEstado' WHERE id_usuario='$id'";
        return mysqli_query($this->conexion, $sql);
    }

    // 🧍 Crear nuevo administrador
    public function crearAdministrador($nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $contrasena, $fotografia = null) {
        // Validar duplicado
        $verificar = "SELECT id_usuario FROM usuarios WHERE correo='$correo' OR cedula='$cedula'";
        $resultado = mysqli_query($this->conexion, $verificar);
        if (mysqli_num_rows($resultado) > 0) {
            return false;
        }

        // 🔒 Encriptar contraseña correctamente
        $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios 
                (nombre, apellido, cedula, fecha_nacimiento, correo, telefono, fotografia, contrasena, tipo, estado)
                VALUES 
                ('$nombre', '$apellido', '$cedula', '$fecha_nacimiento', '$correo', '$telefono', '$fotografia', '$contrasenaHash', 'administrador', 'activo')";
        return mysqli_query($this->conexion, $sql);
    }

    // 📦 Procesar formulario del nuevo administrador
    public function procesarNuevoAdministrador($post, $files) {
        session_start();

        $nombre = trim($post['nombre']);
        $apellido = trim($post['apellido']);
        $cedula = trim($post['cedula']);
        $fecha_nacimiento = $post['fecha_nacimiento'];
        $correo = trim($post['correo']);
        $telefono = trim($post['telefono']);
        $contrasena = $post['contrasena'];
        $confirmar = $post['confirmar_contrasena'];

        if ($contrasena !== $confirmar) {
            $_SESSION['mensaje'] = "❌ Las contraseñas no coinciden.";
            header("Location: ../views/administrador.php");
            exit;
        }

        // 📸 Manejo de fotografía
        $rutaFoto = null;
        if (isset($files['fotografia']) && $files['fotografia']['error'] === 0) {
            $directorio = "../assets/imagenesUsuarios/";
            if (!is_dir($directorio)) mkdir($directorio, 0777, true);
            $nombreArchivo = time() . "_" . basename($files['fotografia']['name']);
            $rutaDestino = $directorio . $nombreArchivo;
            if (move_uploaded_file($files['fotografia']['tmp_name'], $rutaDestino)) {
                $rutaFoto = "assets/imagenesUsuarios/" . $nombreArchivo;
            }
        }

        // Crear en base de datos
        if ($this->crearAdministrador($nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $contrasena, $rutaFoto)) {
            $_SESSION['mensaje'] = "✅ Administrador creado correctamente (contraseña encriptada).";
        } else {
            $_SESSION['mensaje'] = "⚠️ Error: el correo o la cédula ya existen.";
        }

        header("Location: ../views/administrador.php");
        exit;
    }
    
}
?>
