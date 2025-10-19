<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/Estilos/login.css">
</head>
<body>
<header>
    <img src="Imagenes/logo.jpg" width="150" alt="Logo">
    <h1>Bienvenido a Aventones</h1>
    <h2>Su mejor opción para viajar seguros</h2>
</header>

<section class="container">
    <h3>Inicio de Sesión</h3>

    <form action="../logica/login.php" method="POST">
        <label>Correo electrónico:</label>
        <input type="email" name="correo" required>

        <label>Contraseña:</label>
        <input type="password" name="contrasena" required>

        <input type="submit" value="Iniciar Sesión">

        <p>¿Aún no tienes cuenta? <a href="Registro.php">Regístrate aquí</a></p>
    </form>
</section>
</body>
</html>