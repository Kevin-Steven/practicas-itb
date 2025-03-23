<?php
session_start();
require '../../config/config.php';

// 1. Validación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// 2. Recibir los datos del formulario
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);
$actividad_economica = trim($_POST['actividad_economica'] ?? '');
$provincia = trim($_POST['provincia'] ?? '');
$hora_inicio = $_POST['horario_practica_inicio'] ?? '';
$hora_fin = $_POST['horario_practica_fin'] ?? '';
$jornada_laboral = trim($_POST['jornada_laboral'] ?? '');
$numero_practicas = trim($_POST['numero_practicas'] ?? '');
$numero_telefono = trim($_POST['numero_telefono'] ?? '');
$numero_institucional = trim($_POST['numero_institucional'] ?? 'NO APLICA');

// 3. Validación de campos obligatorios
if (
    empty($actividad_economica) ||
    empty($provincia) ||
    empty($hora_inicio) ||
    empty($hora_fin) ||
    empty($jornada_laboral) ||
    empty($numero_practicas) ||
    empty($numero_telefono)
) {
    header("Location: ../for-seis.php?status=missing_data");
    exit();
}

// 4. Validación de número de teléfono celular
if (!preg_match('/^[0-9]{10}$/', $numero_telefono)) {
    header("Location: ../for-seis.php?status=invalid_phone");
    exit();
}

// 5. Validación de número institucional si lo ingresaron
if (!empty($numero_institucional) && !preg_match('/^[0-9]{7,20}$/', $numero_institucional)) {
    header("Location: ../for-seis.php?status=invalid_institutional_phone");
    exit();
}

// 6. Verificación de usuario_id del formulario
if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-seis.php?status=invalid_user");
    exit();
}

// 7. Insertar en documento_seis
$sql_insert = "INSERT INTO documento_seis (
    usuario_id,
    actividad_economica,
    provincia,
    jornada_laboral,
    numero_practicas,
    numero_telefono,
    numero_institucional,
    hora_inicio,
    hora_fin,
    estado
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')";

$stmt_insert = $conn->prepare($sql_insert);

if (!$stmt_insert) {
    header("Location: ../for-seis.php?status=prepare_error");
    exit();
}

// 8. Vincular parámetros (9 campos en total)
$stmt_insert->bind_param(
    "issssssss", // i = int, s = string
    $usuario_id_session,     // i
    $actividad_economica,    // s
    $provincia,              // s
    $jornada_laboral,        // s
    $numero_practicas,       // s
    $numero_telefono,        // s
    $numero_institucional,   // s
    $hora_inicio,            // s (TIME en formato hh:mm)
    $hora_fin                // s (TIME en formato hh:mm)
);

// 9. Ejecutar el insert
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
