<?php
require '../config/config.php';
require '../email/enviar-correos.php'; // Tu función enviarCorreo()

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = mysqli_real_escape_string($conn, $_POST['recuperar-clave']);

    // 1. Verificar si el correo existe en la base de datos
    $sql = "SELECT id, email FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 2. El correo existe
        $usuario = $result->fetch_assoc();

        // 3. Generar un token y su tiempo de expiración
        $token = bin2hex(random_bytes(50)); // 100 caracteres hex
        $expira = date("Y-m-d H:i:s", strtotime("+10 minutes")); // Expira en 10 minutos

        // 4. Insertar token en la tabla recuperacion_clave
        $sql = "INSERT INTO recuperacion_clave (usuario_id, token, expira) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $usuario['id'], $token, $expira);
        $stmt->execute();

        // 5. Crear el link de recuperación
        $link = "https://institutobolivariano.online/app/registrar/restablecer-clave.php?token=" . $token;

        // 6. Datos del correo
        $correo_destino = $correo; // Destinatario
        $asunto = 'Recupera tus credenciales de acceso';
        $mensaje = "
            <h3>Hola.</h3>
            <p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
            <br>
            <p><a href='$link' style='color: blue; text-decoration: underline;'>Restablecer contraseña</a></p>
            <br>
            <p><strong>Nota:</strong> El enlace estará disponible por 10 minutos.</p>
            <p>Si no solicitaste el restablecimiento de tu contraseña, puedes ignorar este mensaje.</p>
            <p>Saludos cordiales,<br>&copy; 2025 Gestoría de Prácticas Profesionales - Instituto Superior Tecnológico Bolivariano de Tecnología.</p>
            <hr>
            <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a este correo.</p>
        ";

        // 7. Enviar correo con la función reutilizable
        $envio = enviarCorreo($correo_destino, $asunto, $mensaje);

        // 8. Validar el resultado del envío
        if ($envio['success']) {
            header("Location: recuperar-cuenta.php?mensaje=Correo de recuperación enviado. Revisa tu bandeja de entrada.&tipo=success");
            exit();
        } else {
            // Mensaje de error con depuración
            $error = urlencode($envio['debug']);
            header("Location: recuperar-cuenta.php?mensaje=Error al enviar el correo.&debug=$error&tipo=danger");
            exit();
        }

    } else {
        // 9. Correo no encontrado en base de datos
        header("Location: recuperar-cuenta.php?mensaje=El correo no está registrado.&tipo=danger");
        exit();
    }

} else {
    // 10. Acceso no permitido
    header("Location: ../../index.php");
    exit();
}
?>
