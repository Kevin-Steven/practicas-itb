<?php
require '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = intval($_POST['id']);
    $nuevo_rol = $_POST['nuevo_rol'];

    if (!$usuario_id || empty($nuevo_rol)) {
        header("Location: ../modificar-usuarios.php?status=error");
        exit();
    }

    $sql = "UPDATE usuarios SET rol = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_rol, $usuario_id);

    if ($stmt->execute()) {
        header("Location: ../modificar-usuarios.php?status=success");
    } else {
        header("Location: ../modificar-usuarios.php?status=db_error");
    }

    $stmt->close();
    $conn->close();
}
