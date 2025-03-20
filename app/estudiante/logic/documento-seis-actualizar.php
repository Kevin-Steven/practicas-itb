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
$horario_practica = trim($_POST['horario_practica'] ?? '');
$jornada_laboral = trim($_POST['jornada_laboral'] ?? '');
$nombres_representante = trim($_POST['nombres-representante'] ?? '');
$cargo_tutor = trim($_POST['cargo_tutor'] ?? '');
$numero_practicas = trim($_POST['numero_practicas'] ?? '');
$numero_telefono = trim($_POST['numero_telefono'] ?? '');
$numero_institucional = trim($_POST['numero_institucional'] ?? '');

// 3. Validaciones básicas (puedes extenderlas según sea necesario)
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
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=missing_data");
    exit();
}

// Validación adicional (número de teléfono)
if (!preg_match('/^[0-9]{10}$/', $numero_telefono)) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=invalid_phone");
    exit();
}

// 4. Verificar que el usuario_id del form coincida con el de la sesión
if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=invalid_user");
    exit();
}

// 5. Obtener los datos actuales de documento_seis
$sql_select = "SELECT 
    actividad_economica,
    provincia,
    horario_practica,
    jornada_laboral,
    nombres_representante,
    cargo_tutor,
    numero_practicas,
    numero_telefono,
    numero_institucional
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
$stmt_select->close();

// 6. Comprobar si hubo cambios
$hubo_cambios = false;

if (
    $actividad_economica !== $datos_actuales['actividad_economica'] ||
    $provincia !== $datos_actuales['provincia'] ||
    $horario_practica !== $datos_actuales['horario_practica'] ||
    $jornada_laboral !== $datos_actuales['jornada_laboral'] ||
    $nombres_representante !== $datos_actuales['nombres_representante'] ||
    $cargo_tutor !== $datos_actuales['cargo_tutor'] ||
    $numero_practicas !== $datos_actuales['numero_practicas'] ||
    $numero_telefono !== $datos_actuales['numero_telefono'] ||
    $numero_institucional !== $datos_actuales['numero_institucional']
) {
    $hubo_cambios = true;
}

// 7. Si no hubo cambios, redirigir sin status
if (!$hubo_cambios) {
    header("Location: ../for-seis.php");
    exit();
}

// 8. Actualizar el registro (estado vuelve a 'Pendiente')
$sql_update = "UPDATE documento_seis SET
    actividad_economica = ?,
    provincia = ?,
    horario_practica = ?,
    jornada_laboral = ?,
    nombres_representante = ?,
    cargo_tutor = ?,
    numero_practicas = ?,
    numero_telefono = ?,
    numero_institucional = ?,
    estado = 'Pendiente'
WHERE usuario_id = ?
ORDER BY id DESC
LIMIT 1"; // Solo actualiza el último

$stmt_update = $conn->prepare($sql_update);

if (!$stmt_update) {
    header("Location: ../for-seis-edit.php?id=$usuario_id_form&status=prepare_error");
    exit();
}

$stmt_update->bind_param(
    "sssssssssi",
    $actividad_economica,
    $provincia,
    $horario_practica,
    $jornada_laboral,
    $nombres_representante,
    $cargo_tutor,
    $numero_practicas,
    $numero_telefono,
    $numero_institucional,
    $usuario_id_session
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
