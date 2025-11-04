<!-- 
    // =====================================================
    // Script: registro.php (Vista/Controlador).
    // Descripción: Vista para el registro de nuevos usuarios 
    // en el sistema Aventones.
    // Creado por: Jimena Jara y Fernanda Sibaja.
    // =====================================================
 -->
    
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registro | Aventones</title>
        <link rel="stylesheet" href="../assets/Estilos/registro.css">
    </head>
    <body>
        <header>
            <a href="../index.php" class="btn-volver-header">⟵ Volver al inicio</a>
            <img src="../assets/Estilos/Imagenes/logo.png" alt="Logo Aventones" width="170">
            <h1>Bienvenido a <span class="resaltado">Aventones.com</span></h1>
            <h2>Tu mejor opción para viajar seguros</h2>
        </header>

        <section class="container">
            <h3>Formulario de Registro</h3>

            <form action="../logica/registrarUsuario.php" method="POST" enctype="multipart/form-data">
                <label>Tipo de usuario:</label>
                <select name="tipo" required>
                    <option value="" disabled selected>-- Seleccione tipo de usuario --</option>
                    <option value="pasajero">Pasajero</option>
                    <option value="chofer">Chofer</option>
                </select>
                
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

                <label>Contraseña:</label>
                <input type="password" name="contrasena" required>

                <label>Confirmar contraseña:</label>
                <input type="password" name="confirmar" required>

                <label>Fotografía:</label>
                <input type="file" name="fotografia" accept=".jpg,.jpeg,.png">

                <input type="submit" value="Registrarme" class="btn">

                <?php
                    session_start();
                    if(isset($_SESSION['mensaje'])) {
                        echo "<div class='mensaje'>" . $_SESSION['mensaje'] . "</div>";
                        unset($_SESSION['mensaje']);
                    }
                ?>

                <p>¿Ya eres usuario? <a href="login.php">Inicia sesión aquí</a></p>
            </form>
            
        </section>

    <footer>
        <p>© <?= date("Y") ?> Aventones | Universidad Técnica Nacional</p>
    </footer>

</body>
</html>