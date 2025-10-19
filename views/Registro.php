<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../assets/Estilos/registro.css">
</head>
<body class="body-registro">

<header>
    <img src="../assets/Estilos/Imagenes/logo.jpg" alt="Logo TicoRides" width="150">
    <h1>Bienvenido a Aventones.com</h1>
    <h2>Su mejor opción para viajar seguros</h2>
</header>

<section class="container">
    <h3>Formulario de Registro</h3>

    <form action="../logica/registrar_usuario.php" method="POST" enctype="multipart/form-data">
        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <label>Apellidos:</label>
        <input type="text" name="apellido" required>

        <label>Cédula:</label>
        <input type="text" name="cedula" required>

        <label>Fecha de nacimiento:</label>
        <input type="date" name="fecha_nacimiento" required>

        <label>Correo electrónico:</label>
        <input type="email" name="correo" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" required>

        <label>Fotografía:</label>
        <input type="file" name="fotografia" accept=".jpg,.jpeg,.png">

        <label>Contraseña:</label>
        <input type="password" name="contrasena" required>

        <label>Confirmar contraseña:</label>
        <input type="password" name="confirmar" required>

        <input type="submit" value="Registrarme">

        <p>¿Ya eres usuario? <a href="Autenticacion.php">Inicia sesión aquí</a></p>
    </form>
</section>

</body>
</html>
