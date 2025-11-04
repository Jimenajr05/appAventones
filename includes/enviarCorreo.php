<?php
    // =====================================================
    // Script: enviarCorreo.php
    // Descripción: Contiene funciones para el envío de correos
    // electrónicos, utilizando la librería PHPMailer y el
    // servidor SMTP de Gmail. Actualmente incluye la función
    // para enviar correos de activación de cuenta.
    // Creado por: Jimena y Fernanda.
    // =====================================================

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require __DIR__ . '/../PHPMailer/src/Exception.php';
    require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/../PHPMailer/src/SMTP.php';

    // Función para enviar correo de activación de cuenta
    function enviarCorreoActivacion($correoDestino, $nombre, $token) {
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'mariajimenajr14@gmail.com';
            $mail->Password   = 'xwhw dkjw uuki qmpm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Habilitar debug SMTP si hay problemas
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log'; 

            // Remitente y destinatario
            $mail->setFrom('mariajimenajr14@gmail.com', 'Aventones');
            $mail->addAddress($correoDestino, $nombre);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8'; 
            $mail->Subject = 'Bienvenido a Aventones - Activa tu cuenta';
            
            // URL para el entorno de desarrollo
            $baseURL = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            
            // Construir la URL completa usando la información del servidor actual
            $enlace = "{$protocol}://{$baseURL}/Proyecto1/appAventones/logica/activar.php?token=" . urlencode($token);
            
            // Log para debugging
            error_log("URL de activación generada: " . $enlace);

            // Cuerpo del correo
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; text-align: center;'>
                    <h2>¡Bienvenido a Aventones, $nombre!</h2>
                    <p>Gracias por registrarte. Para activar tu cuenta, haz clic en el siguiente botón:</p>
                    <a href='$enlace'
                        style='background-color: #28a745; color: white; padding: 10px 20px;
                            border-radius: 5px; text-decoration: none;'>
                        Activar mi cuenta
                    </a>
                    <p style='margin-top: 15px; color: #555;'>Si no te registraste, ignora este mensaje.</p>
                </div>
            ";

            // Enviar
            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Error enviando correo: {$mail->ErrorInfo}");
            return false;
        }
    }
?>