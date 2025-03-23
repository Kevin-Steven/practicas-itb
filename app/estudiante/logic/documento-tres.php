<?php
session_start();
require '../../config/config.php';

// Verificación de sesión (opcional pero recomendado)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Verificación de conexión a base de datos
if (!$conn) {
    header("Location: ../for-tres.php?status=db_error");
    exit();
}

// Recoger los datos del formulario
$usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;
$nombre_entidad_receptora = trim($_POST['nombre_entidad_receptora']);
$nombres_tutor_receptor = trim($_POST['nombres_tutor_receptor']);
$cargo_tutor_receptor = trim($_POST['cargo_tutor_receptor']);
$departamento_entidad_receptora = trim($_POST['departamento_entidad_receptora']);
$numero_telefono_tutor_receptor = trim($_POST['numero_telefono_tutor_receptor']);
$ciudad_entidad_receptora = trim($_POST['ciudad_entidad_receptora']);

$ciudad_entidad_receptora = mb_strtolower($ciudad_entidad_receptora, 'UTF-8');
$ciudad_entidad_receptora = mb_convert_case($ciudad_entidad_receptora, MB_CASE_TITLE, "UTF-8");
// Validar que los campos no estén vacíos
if (
    empty($usuario_id) ||
    empty($nombre_entidad_receptora) ||
    empty($nombres_tutor_receptor) ||
    empty($cargo_tutor_receptor) ||
    empty($departamento_entidad_receptora) ||
    empty($numero_telefono_tutor_receptor) ||
    empty($ciudad_entidad_receptora)
) {
    header("Location: ../for-tres.php?status=missing_data");
    exit();
}

// Validación de número de teléfono (solo números, 10 dígitos)
if (!preg_match('/^[0-9]{10}$/', $numero_telefono_tutor_receptor)) {
    header("Location: ../for-tres.php?status=form_error");
    exit();
}

// Preparar INSERT
$sql_insert = "INSERT INTO documento_tres (
    usuario_id,
    nombre_doc,
    nombre_entidad_receptora,
    departamento_entidad_receptora,
    nombres_tutor_receptor,
    cargo_tutor_receptor,
    numero_telefono_tutor_receptor,
    ciudad_entidad_receptora,
    estado,
    fecha_subida
) VALUES (?, '3 CARTA DE ASIGNACIÓN DE ESTUDIANTE DE DESRROLLO DE SOFTWARE', ?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";

$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param(
    "issssss",
    $usuario_id,
    $nombre_entidad_receptora,
    $departamento_entidad_receptora,
    $nombres_tutor_receptor,
    $cargo_tutor_receptor,
    $numero_telefono_tutor_receptor,
    $ciudad_entidad_receptora
);

if ($stmt_insert->execute()) {
    // Insert exitoso
    header("Location: ../for-tres.php?status=success");
    exit();
} else {
    // Error al insertar en la base de datos
    header("Location: ../for-tres.php?status=db_error");
    exit();
}

// Cierre de conexiones
$stmt_insert->close();
$conn->close();
?>
