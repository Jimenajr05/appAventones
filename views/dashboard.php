<?php
    // =====================================================
    // Script: dashboard.php
    // Descripción: Redirige a los usuarios a sus paneles 
    // correspondientes según su tipo de cuenta.
    // Creado por: Jimena Jara y Fernanda Sibaja.
    // =====================================================

    session_start();
    if (!isset($_SESSION['tipo'])) {
        header("Location: login.php");
        exit;
    }

    // Redirigir según el tipo de usuario
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

    // Realizar la redirección
    if (!empty($redireccion)) {
        header("Location: $redireccion");
        exit;
    }
?>