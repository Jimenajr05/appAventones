<?php
    // =====================================================
    // Lógica: cerrarSesion.php
    // Descripción: Este archivo cierra la sesión del usuario y lo redirige al login.php.
    // Creado por: Jimena Jara y Fernanda Sibaja.
    // =====================================================

    // Iniciar sesión para poder destruirla
    session_start();

    // Borrar todas las variables de sesión
    session_unset();

    // Destruir la sesión por completo
    session_destroy();

    // Redirigir al login
    header("Location: ../views/login.php");
    exit;
?>