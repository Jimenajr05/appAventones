<?php
    // =====================================================
    // Script: cerrarConexion.php
    // Descripción: Cierra la conexión a la base de datos.
    // Creado por: Jimena Jara y Fernanda Sibaja.
    // =====================================================
    
    if (isset($conexion)) {
        mysqli_close($conexion);
    }
?>