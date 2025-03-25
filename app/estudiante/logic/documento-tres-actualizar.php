<?php
session_start();
require '../../config/config.php';

// 1. Verificación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

// 2. Verificación de la conexión a la base de datos
if (!$conn) {
    header("Location: ../for-tres.php?status=db_error");
    exit();
}

// 3. Recoger los datos enviados por POST y limpiar
$usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;
$nombre_entidad_receptora = trim($_POST['nombre_entidad_receptora']);
$nombres_tutor_receptor = trim($_POST['nombres_tutor_receptor']);
$cargo_tutor_receptor = trim($_POST['cargo_tutor_receptor']);
$ciudad_entidad_receptora = trim($_POST['ciudad_entidad_receptora']);
$departamento_entidad_receptora = trim($_POST['departamento_entidad_receptora']);
$numero_telefono_tutor_receptor = trim($_POST['numero_telefono_tutor_receptor']);

// 4. Validación básica
if (
    empty($usuario_id) ||
    empty($nombre_entidad_receptora) ||
    empty($nombres_tutor_receptor) ||
    empty($cargo_tutor_receptor) ||
    empty($ciudad_entidad_receptora) ||
    empty($departamento_entidad_receptora) ||
    empty($numero_telefono_tutor_receptor)
) {
    header("Location: ../for-tres.php?status=missing_data");
    exit();
}

// 5. Validación del número de teléfono (opcional)
if (!preg_match('/^[0-9]{10}$/', $numero_telefono_tutor_receptor)) {
    header("Location: ../for-tres.php?status=form_error");
    exit();
}

// 6. Verificar que exista un documento_tres para este usuario
$sql_check = "SELECT id FROM documento_tres WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $usuario_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    // No existe documento tres que actualizar
    header("Location: ../for-tres.php?status=not_found");
    exit();
}

$row = $result_check->fetch_assoc();
$documento_id = $row['id'];

// 7. Realizar el UPDATE
$sql_update = "UPDATE documento_tres SET
    nombre_entidad_receptora = ?,
    departamento_entidad_receptora = ?,
    nombres_tutor_receptor = ?,
    cargo_tutor_receptor = ?,
    numero_telefono_tutor_receptor = ?,
    ciudad_entidad_receptora = ?,
    fecha_subida = NOW()
WHERE id = ?";

$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param(
    "ssssssi",
    $nombre_entidad_receptora,
    $departamento_entidad_receptora,
    $nombres_tutor_receptor,
    $cargo_tutor_receptor,
    $numero_telefono_tutor_receptor,
    $ciudad_entidad_receptora,
    $documento_id
);

if ($stmt_update->execute()) {
    // Actualización exitosa
    header("Location: ../for-tres.php?status=update");
    exit();
} else {
    // Error al actualizar
    header("Location: ../for-tres.php?status=db_error");
    exit();
}

// 8. Cerrar conexiones
$stmt_update->close();
$stmt_check->close();
$conn->close();
?>
