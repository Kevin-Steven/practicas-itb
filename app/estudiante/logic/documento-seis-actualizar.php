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
$hora_inicio = trim($_POST['horario_practica_inicio'] ?? '');
$hora_fin = trim($_POST['horario_practica_fin'] ?? '');
$jornada_laboral = trim($_POST['jornada_laboral'] ?? '');
$numero_practicas = trim($_POST['numero_practicas'] ?? '');
$numero_telefono = trim($_POST['numero_telefono'] ?? '');
$numero_institucional = trim($_POST['numero_institucional'] ?? '');

// 3. Validaciones básicas
if (
    empty($actividad_economica) ||
    empty($provincia) ||
    empty($hora_inicio) ||
    empty($hora_fin) ||
    empty($jornada_laboral) ||
    empty($numero_practicas) ||
    empty($numero_telefono)
) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=missing_data");
    exit();
}

// 4. Validaciones específicas
if (!preg_match('/^[0-9]{10}$/', $numero_telefono)) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=invalid_phone");
    exit();
}

if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=invalid_user");
    exit();
}

// 5. Obtener el ID del último documento_seis de este usuario
$sql_select = "SELECT id, actividad_economica, provincia, hora_inicio, hora_fin, jornada_laboral, numero_practicas, numero_telefono, numero_institucional 
FROM documento_seis 
WHERE usuario_id = ? 
ORDER BY id DESC 
LIMIT 1";

$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $usuario_id_session);
$stmt_select->execute();
$result_select = $stmt_select->get_result();

if ($result_select->num_rows === 0) {
    header("Location: ../for-seis.php?status=not_found");
    exit();
}

$datos_actuales = $result_select->fetch_assoc();
$id_documento_seis = $datos_actuales['id'];
$stmt_select->close();

// 6. Comprobar si hubo cambios
$hubo_cambios = (
    $actividad_economica !== $datos_actuales['actividad_economica'] ||
    $provincia !== $datos_actuales['provincia'] ||
    $hora_inicio !== $datos_actuales['hora_inicio'] ||
    $hora_fin !== $datos_actuales['hora_fin'] ||
    $jornada_laboral !== $datos_actuales['jornada_laboral'] ||
    $numero_practicas !== $datos_actuales['numero_practicas'] ||
    $numero_telefono !== $datos_actuales['numero_telefono'] ||
    $numero_institucional !== $datos_actuales['numero_institucional']
);

// 7. Si no hubo cambios, redirigir
if (!$hubo_cambios) {
    header("Location: ../for-seis.php");
    exit();
}

// 8. Realizar el UPDATE sobre el registro exacto (por ID)
$sql_update = "UPDATE documento_seis SET 
    actividad_economica = ?, 
    provincia = ?, 
    hora_inicio = ?, 
    hora_fin = ?, 
    jornada_laboral = ?, 
    numero_practicas = ?, 
    numero_telefono = ?, 
    numero_institucional = ?, 
    estado = 'Pendiente' 
WHERE id = ?";

$stmt_update = $conn->prepare($sql_update);

if (!$stmt_update) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=prepare_error");
    exit();
}

$stmt_update->bind_param(
    "ssssssssi",
    $actividad_economica,
    $provincia,
    $hora_inicio,
    $hora_fin,
    $jornada_laboral,
    $numero_practicas,
    $numero_telefono,
    $numero_institucional,
    $id_documento_seis
);

if ($stmt_update->execute()) {
    header("Location: ../for-seis.php?status=update");
    exit();
} else {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=db_error");
    exit();
}

$stmt_update->close();
$conn->close();
?>
