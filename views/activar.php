<?php 
$status = $_GET['status'] ?? 'error';

$messages = [
    'ok'      => ['✅ ¡Cuenta activada con éxito!', 'success', 'Tu cuenta ha sido activada correctamente. Ya puedes iniciar sesión.'],
    'invalid' => ['❌ Token inválido', 'error', 'El enlace de activación no es válido.'],
    'used'    => ['❌ Token inválido o ya usado', 'error', 'Este enlace ya fue utilizado o no existe.'],
    'missing' => ['⚠️ Token no proporcionado', 'error', 'No recibimos un token válido.'],
    'error'   => ['⚠️ Error en la activación', 'error', 'Hubo un problema, intenta de nuevo.']
];

[$title, $type, $text] = $messages[$status];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Cuenta | Aventones</title>
    <link rel="stylesheet" href="../assets/Estilos/activar.css">
</head>
<body>

<header>
    <img src="../assets/Estilos/Imagenes/logo.png" width="170">
    <h1>Bienvenido a <span class="marca">Aventones.com</span></h1>
    <h2>Tu mejor opción para viajar seguros</h2>
</header>

<main>
    <div class="card <?= $type ?>">
        <h2><?= $title ?></h2>
        <p><?= $text ?></p>

        <?php if ($status === 'ok'): ?>
            <a href="login.php" class="btn">Iniciar Sesión</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-rojo">Volver al Login</a>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>© <?= date('Y'); ?> Aventones | Universidad Técnica Nacional</p>
</footer>

</body>
</html>
