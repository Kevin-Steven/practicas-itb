<?php
session_start();
require '../../config/config.php';

// 1. Validación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// 2. Recibir los datos del formulario (con null coalescing operator para manejar valores vacíos)
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);
$actividad_economica = trim($_POST['actividad_economica'] ?? '');
$provincia = trim($_POST['provincia'] ?? '');
$horario_practica = trim($_POST['horario_practica'] ?? '');
$jornada_laboral = trim($_POST['jornada_laboral'] ?? '');
$nombres_representante = trim($_POST['nombres-representante'] ?? '');
$cargo_tutor = trim($_POST['cargo_tutor'] ?? '');
$numero_practicas = trim($_POST['numero_practicas'] ?? '');
$numero_telefono = trim($_POST['numero_telefono'] ?? '');
$numero_institucional = trim($_POST['numero_institucional'] ?? '');

// 3. Validación de datos básicos (que no estén vacíos)
if (
    empty($actividad_economica) ||
    empty($provincia) ||
    empty($horario_practica) ||
    empty($jornada_laboral) ||
    empty($nombres_representante) ||
    empty($cargo_tutor) ||
    empty($numero_practicas) ||
    empty($numero_telefono) 
) {
    header("Location: ../for-seis.php?status=missing_data");
    exit();
}

// Validar el teléfono (debe tener solo números y longitud adecuada)
if (!preg_match('/^[0-9]{10}$/', $numero_telefono)) {
    header("Location: ../for-seis.php?status=invalid_phone");
    exit();
}

// 5. Validar el usuario_id del formulario contra el de sesión
if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-seis.php?status=invalid_user");
    exit();
}

// 6. Insertar datos en documento_seis
$sql_insert = "INSERT INTO documento_seis (
    usuario_id,
    actividad_economica,
    provincia,
    horario_practica,
    jornada_laboral,
    nombres_representante,
    cargo_tutor,
    numero_practicas,
    numero_telefono,
    numero_institucional,
    estado
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')";

$stmt_insert = $conn->prepare($sql_insert);

if (!$stmt_insert) {
    header("Location: ../for-seis.php?status=prepare_error");
    exit();
}

$stmt_insert->bind_param(
    "isssssssss", // 1 entero + 9 strings
    $usuario_id_session,       // i
    $actividad_economica,      // s
    $provincia,                // s
    $horario_practica,         // s
    $jornada_laboral,          // s
    $nombres_representante,    // s
    $cargo_tutor,              // s
    $numero_practicas,         // s
    $numero_telefono,          // s
    $numero_institucional      // s
);

if ($stmt_insert->execute()) {
    header("Location: ../for-seis.php?status=success");
    exit();
} else {
    header("Location: ../for-seis.php?status=db_error");
    exit();
}

$stmt_insert->close();
$conn->close();
?>
