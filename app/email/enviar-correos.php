<?php
// Aumentar el tiempo de ejecución y mostrar errores (opcional, quítalo en producción)
set_time_limit(300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Importar las clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar PHPMailer
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

/**
 * Función para enviar correos electrónicos
 * @param string $to Correo del destinatario
 * @param string $subject Asunto del correo
 * @param string $mensaje Contenido del mensaje
 * @return array Resultado del envío
 */
function enviarCorreo($to, $subject, $mensaje)
{
    $status = [
        'success' => false,
        'message' => '',
        'debug'   => ''
    ];

    try {
        $mail = new PHPMailer(true);

        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = 'mail.daule.gob.ec';
        $mail->SMTPAuth = true;
        $mail->Username = 'sistemas.tramites@daule.gob.ec';
        $mail->Password = 'S1st3m4s$';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Opciones SSL
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // Configuración de depuración (debug)
        $mail->SMTPDebug = 0; // 2 para depuración detallada
        $debugOutput = '';
        $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
            $debugOutput .= "Nivel $level: $str\n";
        };

        // Remitente y destinatario
        $mail->setFrom('sistemas.tramites@daule.gob.ec', 'Sistema de Trámites Daule');
        $mail->addAddress($to);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = '
            <div style="font-family: Arial, sans-serif;">
                <h2>' . htmlspecialchars($subject) . '</h2>
                <p>' . nl2br(htmlspecialchars($mensaje)) . '</p>
                <br/>
                <p>Saludos cordiales,</p>
                <p>Municipalidad de Daule</p>
            </div>';
        $mail->AltBody = $mensaje;

        // Enviar correo
        $mail->send();

        $status['success'] = true;
        $status['message'] = "Correo enviado exitosamente a $to";
        $status['debug']   = $debugOutput;

    } catch (Exception $e) {
        $status['success'] = false;
        $status['message'] = 'Error al enviar el correo.';
        $status['debug']   = $mail->ErrorInfo ?: $e->getMessage();
    }

    return $status;
}
?>
