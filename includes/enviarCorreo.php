<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

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
        $mail->SMTPDebug = 2; // 0 = off, 1 = client messages, 2 = client and server messages
        $mail->Debugoutput = 'error_log'; // Escribir debug al error_log

        // Remitente y destinatario
        $mail->setFrom('mariajimenajr14@gmail.com', 'Aventones');
        $mail->addAddress($correoDestino, $nombre);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8'; // Importante para caracteres especiales
        $mail->Subject = 'Bienvenido a Aventones - Activa tu cuenta';
        
        // URL para el entorno de desarrollo
        $baseURL = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        
        // Construir la URL completa usando la información del servidor actual
        $enlace = "{$protocol}://{$baseURL}/Proyecto1/appAventones/logica/activar.php?token=" . urlencode($token);
        
        // Log para debugging
        error_log("URL de activación generada: " . $enlace);


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
