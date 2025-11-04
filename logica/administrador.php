<?php
    // =====================================================
    // Script: administrador.php
    // Descripción: Lógica del **Administrador**. Gestiona usuarios
    // (listar, activar/desactivar) y crea nuevos administradores.
    // Creado por: Jimena y Fernanda.
    // =====================================================

    include_once("../includes/conexion.php");

    class Administrador {
        private $conexion;

        public function __construct($conexion) {
            $this->conexion = $conexion;
        }

        // Obtener usuarios
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

        // Cambiar estado (activar/desactivar)
        public function cambiarEstado($id, $nuevoEstado) {
            $sql = "UPDATE usuarios SET estado='$nuevoEstado' WHERE id_usuario='$id'";
            return mysqli_query($this->conexion, $sql);
        }

        // Crear nuevo administrador
        public function crearAdministrador($nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $contrasena, $fotografia = null) {
            // Validar duplicado
            $verificar = "SELECT id_usuario FROM usuarios WHERE correo='$correo' OR cedula='$cedula'";
            $resultado = mysqli_query($this->conexion, $verificar);
            if (mysqli_num_rows($resultado) > 0) {
                return false;
            }

            // Encriptar contraseña correctamente
            $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios 
                    (nombre, apellido, cedula, fecha_nacimiento, correo, telefono, fotografia, contrasena, tipo, estado)
                    VALUES 
                    ('$nombre', '$apellido', '$cedula', '$fecha_nacimiento', '$correo', '$telefono', '$fotografia', '$contrasenaHash', 'administrador', 'activo')";
            return mysqli_query($this->conexion, $sql);
        }
        
        // Procesar formulario del nuevo administrador
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

            // Manejo de fotografía (igual al registro común)
            $rutaFoto = "";
            if (isset($files['fotografia']) && $files['fotografia']['error'] === 0) {
                $ext = strtolower(pathinfo($files['fotografia']['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $_SESSION['mensaje'] = "❌ Solo se permiten imágenes JPG o PNG.";
                    header("Location: ../views/administrador.php");
                    exit;
                }

                if ($files['fotografia']['size'] > 2 * 1024 * 1024) {
                    $_SESSION['mensaje'] = "❌ La imagen no debe superar los 2 MB.";
                    header("Location: ../views/administrador.php");
                    exit;
                }

                // Carpeta igual que el registro común
                $directorio = "../uploads/usuarios/";
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $nuevoNombre = uniqid("user_") . ".$ext";
                $rutaDestino = $directorio . $nuevoNombre;

                if (move_uploaded_file($files['fotografia']['tmp_name'], $rutaDestino)) {
                    $rutaFoto = "uploads/usuarios/" . $nuevoNombre;
                } else {
                    $_SESSION['mensaje'] = "❌ Error al subir la imagen.";
                    header("Location: ../views/administrador.php");
                    exit;
                }
            }

            // Crear en base de datos
            if ($this->crearAdministrador($nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $contrasena, $rutaFoto)) {
                $_SESSION['mensaje'] = "✅ Administrador creado correctamente.";
            } else {
                $_SESSION['mensaje'] = "⚠️ Error: el correo o la cédula ya existen.";
            }

            header("Location: ../views/administrador.php");
            exit;
        } 
    }
?>