<?php
require '../../config/config.php'; // Tu conexión a la base de datos

// Validamos que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitizamos y validamos el id del usuario recibido
    $usuario_id = intval($_POST['id'] ?? 0);

    if ($usuario_id <= 0) {
        // Redirige si no llega un ID válido
        header("Location: ../modificar-usuarios.php?status=error");
        exit();
    }

    // Preparamos la consulta para habilitar al usuario
    $sql = "UPDATE usuarios SET estado = 'activo' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);

    // Ejecutamos la consulta y manejamos el resultado
    if ($stmt->execute()) {
        header("Location: ../modificar-usuarios.php?status=habilitado");
    } else {
        header("Location: ../modificar-usuarios.php?status=db_error");
    }

    // Cerramos conexiones
    $stmt->close();
    $conn->close();
    
} else {
    // Si no es un método POST, redirige con error
    header("Location: ../modificar-usuarios.php?status=invalid_request");
    exit();
}
