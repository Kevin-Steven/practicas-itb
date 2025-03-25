<?php
session_start();
require '../../config/config.php';

// Verifica sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// Recibir datos del formulario
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);
$actividad_economica = trim($_POST['actividad_economica'] ?? '');
$provincia = trim($_POST['provincia'] ?? '');
$jornada_laboral = trim($_POST['jornada_laboral'] ?? '');
$numero_practicas = trim($_POST['numero_practicas'] ?? '');
$hora_inicio = $_POST['horario_practica_inicio'] ?? '';
$hora_fin = $_POST['horario_practica_fin'] ?? '';

// Validación básica de campos
if (
    !$usuario_id_form || $usuario_id_form !== $usuario_id_session ||
    empty($actividad_economica) ||
    empty($provincia) ||
    empty($jornada_laboral) ||
    empty($numero_practicas) ||
    empty($hora_inicio) ||
    empty($hora_fin)
) {
    header("Location: ../for-seis.php?status=missing_data");
    exit();
}

// Insertar en documento_seis
$sql = "INSERT INTO documento_seis (
    usuario_id,
    actividad_economica,
    provincia,
    jornada_laboral,
    numero_practicas,
    hora_inicio,
    hora_fin
) VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../for-seis.php?status=db_error");
    exit();
}

$stmt->bind_param(
    "issssss",
    $usuario_id_session,
    $actividad_economica,
    $provincia,
    $jornada_laboral,
    $numero_practicas,
    $hora_inicio,
    $hora_fin
);

if ($stmt->execute()) {
    header("Location: ../for-seis.php?status=success");
} else {
    header("Location: ../for-seis.php?status=db_error");
}

$stmt->close();
$conn->close();
exit();
?>
