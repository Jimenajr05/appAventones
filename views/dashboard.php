<!--
    // =====================================================
    // Script: dashboard.php
    // Descripción: **Redirecciona** al usuario a su panel
    // específico (`administrador.php`, `chofer.php`, `pasajero.php`)
    // basándose en el tipo de sesión activa.
    // Creado por: Jimena y Fernanda.
    // =====================================================
-->
<?php
    session_start();
    if (!isset($_SESSION['tipo'])) {
        header("Location: login.php");
        exit;
    }

    $redireccion = '';
    switch ($_SESSION['tipo']) {
        case 'administrador':
            $redireccion = 'administrador.php';
            break;
        case 'chofer':
            $redireccion = 'chofer.php';
            break;
        case 'pasajero':
            $redireccion = 'pasajero.php';
            break;
        default:
            $redireccion = 'login.php';
    }

    if (!empty($redireccion)) {
        header("Location: $redireccion");
        exit;
    }
?>