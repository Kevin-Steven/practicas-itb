<?php
session_start();
require '../../config/config.php';

// Validar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// Recibir y limpiar los datos del formulario
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);
$actividad_economica = trim($_POST['actividad_economica'] ?? '');
$provincia = trim($_POST['provincia'] ?? '');
$hora_inicio = trim($_POST['horario_practica_inicio'] ?? '');
$hora_fin = trim($_POST['horario_practica_fin'] ?? '');
$jornada_laboral = trim($_POST['jornada_laboral'] ?? '');
$numero_practicas = trim($_POST['numero_practicas'] ?? '');

// Validar campos requeridos
if (
    empty($actividad_economica) ||
    empty($provincia) ||
    empty($hora_inicio) ||
    empty($hora_fin) ||
    empty($jornada_laboral) ||
    empty($numero_practicas)
) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=missing_data");
    exit();
}

// Verificar que el usuario sea el mismo
if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=invalid_user");
    exit();
}

// Obtener el último documento_seis del usuario
$sql_select = "SELECT id, actividad_economica, provincia, hora_inicio, hora_fin, jornada_laboral, numero_practicas
               FROM documento_seis 
               WHERE usuario_id = ? 
               ORDER BY id DESC 
               LIMIT 1";

$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $usuario_id_session);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($result->num_rows === 0) {
    header("Location: ../for-seis.php?status=not_found");
    exit();
}

$datos = $result->fetch_assoc();
$id_documento_seis = $datos['id'];
$stmt_select->close();

// Verificar si hay cambios reales
$hubo_cambios = (
    $actividad_economica !== $datos['actividad_economica'] ||
    $provincia !== $datos['provincia'] ||
    $hora_inicio !== $datos['hora_inicio'] ||
    $hora_fin !== $datos['hora_fin'] ||
    $jornada_laboral !== $datos['jornada_laboral'] ||
    $numero_practicas !== $datos['numero_practicas']
);

if (!$hubo_cambios) {
    header("Location: ../for-seis.php");
    exit();
}

// Actualizar el documento
$sql_update = "UPDATE documento_seis SET 
    actividad_economica = ?, 
    provincia = ?, 
    hora_inicio = ?, 
    hora_fin = ?, 
    jornada_laboral = ?, 
    numero_practicas = ?, 
    estado = 'Pendiente', 
    motivo_rechazo = NULL
WHERE id = ?";

$stmt_update = $conn->prepare($sql_update);

if (!$stmt_update) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=prepare_error");
    exit();
}

$stmt_update->bind_param(
    "ssssssi",
    $actividad_economica,
    $provincia,
    $hora_inicio,
    $hora_fin,
    $jornada_laboral,
    $numero_practicas,
    $id_documento_seis
);

if ($stmt_update->execute()) {
    header("Location: ../for-seis.php?status=update");
} else {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=db_error");
}

$stmt_update->close();
$conn->close();
exit();
?>
