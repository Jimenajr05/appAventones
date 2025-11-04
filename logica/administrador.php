<?php
    // =====================================================
    // Lógica: administrador.php
    // Descripción: Funciones para administrar usuarios
    // (listar, activar/desactivar y crear administradores).
    // Creado por: Jimena Jara y Fernanda Sibaja.
    // =====================================================

    include_once("../includes/conexion.php");

    class Administrador {
        private $conexion;

        public function __construct($conexion) {
            $this->conexion = $conexion;
        }

        // Obtener todos los usuarios registrados
        public function obtenerUsuarios() {
            $sql = "SELECT id_usuario, nombre, apellido, correo, tipo, estado, fecha_registro 
                    FROM usuarios ORDER BY fecha_registro DESC";
            $resultado = mysqli_query($this->conexion, $sql);
            $usuarios = [];
            // Guardar resultados en un array
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    $usuarios[] = $fila;
                }
            }
            return $usuarios;
        }

        // Activar o desactivar usuario según su ID
        public function cambiarEstado($id, $nuevoEstado) {
            $sql = "UPDATE usuarios SET estado='$nuevoEstado' WHERE id_usuario='$id'";
            return mysqli_query($this->conexion, $sql);
        }

        // Crear nuevo administrador
        public function crearAdministrador($nombre, $apellido, $cedula, $fecha_nacimiento, $correo, $telefono, $contrasena, $fotografia = null) {
            // Verificar si correo o cédula ya existen
            $verificar = "SELECT id_usuario FROM usuarios WHERE correo='$correo' OR cedula='$cedula'";
            $resultado = mysqli_query($this->conexion, $verificar);
            if (mysqli_num_rows($resultado) > 0) {
                return false;
            }

            // Encriptar contraseña 
            $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

            // Insertar nuevo administrador
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

            // Validar confirmación de contraseña
            if ($contrasena !== $confirmar) {
                $_SESSION['mensaje'] = "❌ Las contraseñas no coinciden.";
                header("Location: ../views/administrador.php");
                exit;
            }

             // Procesar fotografía si viene una imagen
            $rutaFoto = "";
            if (isset($files['fotografia']) && $files['fotografia']['error'] === 0) {
                $ext = strtolower(pathinfo($files['fotografia']['name'], PATHINFO_EXTENSION));
                // Validar extensión
                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $_SESSION['mensaje'] = "❌ Solo se permiten imágenes JPG o PNG.";
                    header("Location: ../views/administrador.php");
                    exit;
                }
                // Validar tamaño (2 MB máx)
                if ($files['fotografia']['size'] > 2 * 1024 * 1024) {
                    $_SESSION['mensaje'] = "❌ La imagen no debe superar los 2 MB.";
                    header("Location: ../views/administrador.php");
                    exit;
                }

                // Crear carpeta si no existe
                $directorio = "../uploads/usuarios/";
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0777, true);
                }
                // Guardar imagen
                $nuevoNombre = uniqid("user_") . ".$ext";
                $rutaDestino = $directorio . $nuevoNombre;

                // Mover archivo al servidor
                if (move_uploaded_file($files['fotografia']['tmp_name'], $rutaDestino)) {
                    $rutaFoto = "uploads/usuarios/" . $nuevoNombre;
                } else {
                    $_SESSION['mensaje'] = "❌ Error al subir la imagen.";
                    header("Location: ../views/administrador.php");
                    exit;
                }
            }

            // Insertar nuevo administrador
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