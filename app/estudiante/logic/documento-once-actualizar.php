<?php
require '../../config/config.php';
session_start();

// Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Validar que todas las preguntas estén presentes
$campos = ['pregunta1', 'pregunta2', 'pregunta3', 'pregunta4', 'pregunta5', 'pregunta6'];
foreach ($campos as $campo) {
    if (!isset($_POST[$campo])) {
        header("Location: ../for-once-edit.php?status=missing_data");
        exit();
    }
}

// Convertir respuestas a enteros
$p1 = intval($_POST['pregunta1']);
$p2 = intval($_POST['pregunta2']);
$p3 = intval($_POST['pregunta3']);
$p4 = intval($_POST['pregunta4']);
$p5 = intval($_POST['pregunta5']);
$p6 = intval($_POST['pregunta6']);

// Obtener el ID del documento existente más reciente para este usuario
$sql_get_id = "SELECT id FROM documento_once WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_get_id = $conn->prepare($sql_get_id);
$stmt_get_id->bind_param("i", $usuario_id);
$stmt_get_id->execute();
$result = $stmt_get_id->get_result();

if ($row = $result->fetch_assoc()) {
    $documento_id = $row['id'];
} else {
    // No existe documento para actualizar
    header("Location: ../for-once-edit.php?status=db_error");
    exit();
}
$stmt_get_id->close();

// Actualizar el documento existente
$sql_update = "UPDATE documento_once SET
    opcion_uno = ?,
    opcion_dos = ?,
    opcion_tres = ?,
    opcion_cuatro = ?,
    opcion_cinco = ?,
    opcion_seis = ?,
    estado = 'Pendiente',
    motivo_rechazo = NULL
WHERE id = ? AND usuario_id = ?";

$stmt_update = $conn->prepare($sql_update);

if (!$stmt_update) {
    header("Location: ../for-once-edit.php?status=db_error");
    exit();
}

$stmt_update->bind_param("iiiiiiii", $p1, $p2, $p3, $p4, $p5, $p6, $documento_id, $usuario_id);

if ($stmt_update->execute()) {
    header("Location: ../for-once.php?status=update");
} else {
    header("Location: ../for-once-edit.php?status=db_error");
}
exit();
