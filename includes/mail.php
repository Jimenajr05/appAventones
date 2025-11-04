<?php
    // =====================================================
    // Script: mail.php
    // Descripción: Define la clase EmailService para gestionar
    // y enviar correos electrónicos (como notificaciones de
    // reserva) utilizando la librería PHPMailer. Incluye la
    // configuración de conexión SMTP.
    // Creado por: Jimena y Fernanda
    // ===================================================== 

    require __DIR__ . '/../phpmailer/src/PHPMailer.php';
    require __DIR__ . '/../phpmailer/src/SMTP.php';
    require __DIR__ . '/../phpmailer/src/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    class EmailService {
        private $mailer;

        public function __construct() {
            $this->mailer = new PHPMailer(true);

            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'mariajimenajr14@gmail.com'; 
            $this->mailer->Password = 'xwhw dkjw uuki qmpm'; 
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->isHTML(true);
        }

        public function enviarNotificacionReserva($correo, $nombre, $detallesReserva) {
            try {
                $this->mailer->setFrom('noreply@aventones.com', 'Aventones');
                $this->mailer->addAddress($correo, $nombre);
                $this->mailer->Subject = 'Tienes reservas pendientes';

                $this->mailer->Body = "
                    <h2>Hola {$nombre},</h2>
                    <p>Tienes las siguientes reservas pendientes de revisión:</p>
                    <ul>{$detallesReserva}</ul>
                    <p>Por favor, ingresa a tu cuenta para gestionarlas.</p>
                ";

                return $this->mailer->send();
            } catch (Exception $e) {
                error_log("Error al enviar correo: " . $e->getMessage());
                return false;
            }
        }
    }
?>