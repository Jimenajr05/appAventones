<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Aventones</title>
  <link rel="stylesheet" href="../assets/Estilos/login.css">
</head>
<body>
<header>
  <a href="../index.php" class="btn-volver-header">⟵ Volver al inicio</a>
  <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" width="170">
  <h1>Bienvenido a <span class="resaltado">Aventones.com</span></h1>
  <h2>Tu mejor opción para viajar seguros</h2>
</header>

<main class="container">
  <section class="intro">
    <p>Accede a tu cuenta para gestionar tus viajes y reservas.</p>
  </section>

  <section class="login-box">
    <h2>Inicio de Sesión</h2>

    <form action="../logica/login.php" method="POST">
      <label for="correo">Correo electrónico:</label>
      <input type="email" id="correo" name="correo" placeholder="" required>

      <label for="contrasena">Contraseña:</label>
      <input type="password" id="contrasena" name="contrasena" placeholder="" required>

      <?php
      session_start();
      if (isset($_SESSION['error_login'])) {
          echo "<div class='mensaje-error'>{$_SESSION['error_login']}</div>";
          unset($_SESSION['error_login']); // 🔄 eliminar el mensaje después de mostrarlo
      }
      ?>

      <div class="acciones">
        <input type="submit" value="Iniciar Sesión" class="btn">
        <p>¿Aún no tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
      </div>

    </form>
  </section>
</main>

<footer>
  <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
</footer>
</body>
</html>
