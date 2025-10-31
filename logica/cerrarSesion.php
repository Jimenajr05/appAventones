<?php
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
