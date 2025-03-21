<?php
require '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = intval($_POST['id']);

    if (!$usuario_id) {
        header("Location: ../modificar-usuarios.php?status=error");
        exit();
    }

    $sql = "UPDATE usuarios SET estado = 'inactivo' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);

    if ($stmt->execute()) {
        header("Location: ../modificar-usuarios.php?status=inhabilitado");
    } else {
        header("Location: ../modificar-usuarios.php?status=db_error");
    }

    $stmt->close();
    $conn->close();
}
