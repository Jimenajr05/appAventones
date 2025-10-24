<?php
include_once("../includes/conexion.php");

class Administrador {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Mostrar todos los usuarios
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

    // Cambiar estado (activar / desactivar)
    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE usuarios SET estado='$nuevoEstado' WHERE id_usuario='$id'";
        return mysqli_query($this->conexion, $sql);
    }

    // Crear nuevo administrador
    public function crearAdministrador($nombre, $apellido, $correo, $contrasena) {
        $sql = "INSERT INTO usuarios (nombre, apellido, correo, contrasena, tipo, estado)
                VALUES ('$nombre', '$apellido', '$correo', '$contrasena', 'administrador', 'activo')";
        return mysqli_query($this->conexion, $sql);
    }
}
?>
