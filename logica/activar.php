<!--
    // =====================================================
    // Script: activar.php
    // Descripción: Script de la lógica principal para la
    // activación de cuentas. Recibe un 'token' vía GET,
    // verifica su validez en la BD y actualiza el estado
    // del usuario a 'activo'. Muestra una vista de éxito o error.
    // Creado por: Jimena y Fernanda
    // =====================================================
-->
<?php
    include("../includes/conexion.php");

    // Debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Información detallada del servidor
    error_log("=== Información del Servidor ===");
    error_log("HTTP_HOST: " . $_SERVER['HTTP_HOST']);
    error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
    error_log("SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME']);
    error_log("DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT']);
    error_log("PHP_SELF: " . $_SERVER['PHP_SELF']);

    // Verificar la existencia del archivo de conexión
    $conexionPath = realpath("../includes/conexion.php");
    error_log("Ruta del archivo de conexión: " . $conexionPath);
    error_log("¿Existe el archivo de conexión?: " . (file_exists($conexionPath) ? "Sí" : "No"));

    // Registrar todos los detalles de la solicitud
    error_log("=== Intento de activación de cuenta ===");
    error_log("Método: " . $_SERVER['REQUEST_METHOD']);
    error_log("URL completa: " . $_SERVER['REQUEST_URI']);
    error_log("Query String: " . $_SERVER['QUERY_STRING']);
    error_log("Protocolo: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS' : 'HTTP'));

    if (isset($_GET['token'])) {
        // Log para debugging
        error_log("Token recibido: " . $_GET['token']);
        $token = trim($_GET['token']); // Eliminar espacios en blanco

        // Verificar que el token tenga el formato correcto (64 caracteres hexadecimales)
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            die("<h2 style='text-align:center; color:red;'>❌ Token inválido</h2>");
        }

        $stmt = $conexion->prepare("SELECT id_usuario, correo FROM usuarios WHERE token_activacion = ? AND estado = 'pendiente'");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $u = $res->fetch_assoc(); 
            $id = $u['id_usuario'];

            $update = $conexion->prepare("UPDATE usuarios SET estado = 'activo', token_activacion = NULL WHERE id_usuario = ?");
            $update->bind_param("i", $id);
            
            if ($update->execute()) {
                ?>
                    <!DOCTYPE html>
                    <html lang="es">
                        <head>
                            <meta charset="UTF-8">
                            <title>Cuenta Activada</title>
                            <style>
                                body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
                                .success { color: #28a745; }
                                .error { color: #dc3545; }
                                .btn { 
                                    display: inline-block;
                                    padding: 10px 20px;
                                    background-color: #007bff;
                                    color: white;
                                    text-decoration: none;
                                    border-radius: 5px;
                                    margin-top: 20px;
                                }
                                .btn:hover { background-color: #0056b3; }
                            </style>
                        </head>
                        <body>
                            <h2 class="success">✅ ¡Cuenta activada con éxito!</h2>
                            <p>Tu cuenta ha sido activada correctamente. Ya puedes iniciar sesión.</p>
                            <a href="../views/login.php" class="btn">Iniciar sesión</a>
                        </body>
                    </html>
                <?php
            } else {
                error_log("Error al actualizar estado: " . $update->error);
                echo "<h2 style='text-align:center; color:red;'>❌ Error al activar la cuenta. Por favor, intenta más tarde.</h2>";
            }
        } else {
            ?>
                <!DOCTYPE html>
                <html lang="es">
                    <head>
                        <meta charset="UTF-8">
                        <title>Error de Activación</title>
                        <style>
                            body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
                            .error { color: #dc3545; }
                            .btn { 
                                display: inline-block;
                                padding: 10px 20px;
                                background-color: #007bff;
                                color: white;
                                text-decoration: none;
                                border-radius: 5px;
                                margin-top: 20px;
                            }
                            .btn:hover { background-color: #0056b3; }
                        </style>
                    </head>
                    <body>
                        <h2 class="error">❌ Token inválido o cuenta ya activada</h2>
                        <p>El enlace de activación no es válido o la cuenta ya ha sido activada.</p>
                        <a href="../views/login.php" class="btn">Ir al login</a>
                    </body>
                </html>
            <?php
        }
    } else {
        ?>
            <!DOCTYPE html>
            <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <title>Error de Activación</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
                        .error { color: #dc3545; }
                        .btn { 
                            display: inline-block;
                            padding: 10px 20px;
                            background-color: #007bff;
                            color: white;
                            text-decoration: none;
                            border-radius: 5px;
                            margin-top: 20px;
                        }
                        .btn:hover { background-color: #0056b3; }
                    </style>
                </head>
                <body>
                    <h2 class="error">❌ Token no proporcionado</h2>
                    <p>No se ha proporcionado un token de activación válido.</p>
                    <a href="../views/login.php" class="btn">Ir al login</a>
                </body>
            </html>
        <?php
    }
?>
