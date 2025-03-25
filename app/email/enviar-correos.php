<?php
set_time_limit(300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Importar clases
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 🔧 Ruta ajustada: solo subir dos niveles
$phpmailer_base = realpath(__DIR__ . '/../../PHPMailer/src');

if (!$phpmailer_base) {
    die("No se pudo encontrar la carpeta de PHPMailer. Verifica la ruta.");
}

require_once $phpmailer_base . '/Exception.php';
require_once $phpmailer_base . '/PHPMailer.php';
require_once $phpmailer_base . '/SMTP.php';

/**
 * Función para enviar correos electrónicos
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

        $mail->isSMTP();
        $mail->Host = 'mail.daule.gob.ec';
        $mail->SMTPAuth = true;
        $mail->Username = 'sistemas.tramites@daule.gob.ec';
        $mail->Password = 'S1st3m4s$';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->SMTPDebug = 0;
        $debugOutput = '';
        $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
            $debugOutput .= "Nivel $level: $str\n";
        };

        $mail->setFrom('sistemas.tramites@daule.gob.ec', 'Sistema de Trámites Daule');
        $mail->addAddress($to);

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
