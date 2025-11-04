<?php
    // =====================================================
    // Script: cerrarConexion.php
    // Descripción: Cierra la conexión activa a la base de datos
    // si la variable $conexion existe.
    // Creado por: Jimena y Fernanda 
    // =====================================================
    
    if (isset($conexion)) {
        mysqli_close($conexion);
    }
?>