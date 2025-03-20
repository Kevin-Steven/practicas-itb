<?php
session_start();
require '../../config/config.php';

// 1. Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// 2. Verificar el usuario_id recibido desde el form
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);

if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-cinco.php?status=invalid_user");
    exit();
}

// 3. Recibir datos del formulario
$nombre_entidad_receptora = trim($_POST['nombre_entidad'] ?? '');
$ruc = trim($_POST['ruc'] ?? '');
$direccion_entidad_receptora = trim($_POST['direccion-entidad'] ?? '');
$nombre_ciudad = trim($_POST['ciudad'] ?? '');
$nombre_representante = trim($_POST['nombres-representante'] ?? '');
$correo_representante = trim($_POST['correo-entidad'] ?? '');
$numero_institucional = trim($_POST['numero-institucional'] ?? '');

$nombre_representante = mb_strtolower($nombre_representante, 'UTF-8');
$nombre_representante = mb_convert_case($nombre_representante, MB_CASE_TITLE, "UTF-8");
// Validaciones básicas (opcional, pero recomendable)
if (
    empty($nombre_entidad_receptora) ||
    empty($ruc) ||
    empty($direccion_entidad_receptora) ||
    empty($nombre_ciudad) ||
    empty($nombre_representante) ||
    empty($correo_representante) ||
    empty($numero_institucional)
) {
    header("Location: ../for-cinco.php?status=missing_data");
    exit();
}

// 4. Obtener los datos actuales de la base de datos
$sql_actual = "SELECT 
    nombre_entidad_receptora, 
    ruc, 
    direccion_entidad_receptora, 
    nombre_ciudad, 
    nombre_representante, 
    correo_representante, 
    numero_institucional,
    logo_entidad_receptora
FROM documento_cinco
WHERE usuario_id = ?";

$stmt_actual = $conn->prepare($sql_actual);
$stmt_actual->bind_param('i', $usuario_id_session);
$stmt_actual->execute();
$result_actual = $stmt_actual->get_result();

if ($result_actual->num_rows === 0) {
    header("Location: ../for-cinco.php?status=not_found");
    exit();
}

$datos_actuales = $result_actual->fetch_assoc();
$stmt_actual->close();

// 5. Manejar la subida del logo (opcional)
$logo_entidad_receptora_actual = $datos_actuales['logo_entidad_receptora'];
$nombre_archivo_logo = $logo_entidad_receptora_actual; // Valor por defecto

if (isset($_FILES['logo-entidad']) && $_FILES['logo-entidad']['error'] === UPLOAD_ERR_OK) {
    $archivo_tmp = $_FILES['logo-entidad']['tmp_name'];
    $nombre_original = $_FILES['logo-entidad']['name'];
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

    $extensiones_permitidas = ['png', 'jpg', 'jpeg', 'gif'];

    if (!in_array($extension, $extensiones_permitidas)) {
        header("Location: ../for-cinco.php?status=invalid_extension");
        exit();
    }

    $ruta_destino = '../../uploads/logo-entidad/';
    if (!is_dir($ruta_destino)) {
        mkdir($ruta_destino, 0775, true);
    }

    // Generar el nombre del archivo nuevo
    $nombre_archivo_logo = 'logo-' . $ruc . '.' . $extension;
    $ruta_completa_logo = $ruta_destino . $nombre_archivo_logo;

    // Mover el archivo subido
    if (!move_uploaded_file($archivo_tmp, $ruta_completa_logo)) {
        header("Location: ../for-cinco.php?status=upload_error");
        exit();
    }
}

// 6. Comparar los datos recibidos con los actuales
$hubo_cambios = false;

if (
    $nombre_entidad_receptora !== $datos_actuales['nombre_entidad_receptora'] ||
    $ruc !== $datos_actuales['ruc'] ||
    $direccion_entidad_receptora !== $datos_actuales['direccion_entidad_receptora'] ||
    $nombre_ciudad !== $datos_actuales['nombre_ciudad'] ||
    $nombre_representante !== $datos_actuales['nombre_representante'] ||
    $correo_representante !== $datos_actuales['correo_representante'] ||
    $numero_institucional !== $datos_actuales['numero_institucional'] ||
    $nombre_archivo_logo !== $datos_actuales['logo_entidad_receptora']
) {
    $hubo_cambios = true;
}

// 7. Si no hubo cambios, salir sin mensaje
if (!$hubo_cambios) {
    header("Location: ../for-cinco.php"); // Sin status
    exit();
}

// 8. Si hubo cambios, actualizar
$sql_update = "UPDATE documento_cinco SET
    nombre_entidad_receptora = ?,
    ruc = ?,
    direccion_entidad_receptora = ?,
    logo_entidad_receptora = ?,
    nombre_ciudad = ?,
    nombre_representante = ?,
    numero_institucional = ?,
    correo_representante = ?,
    estado = 'Pendiente'
WHERE usuario_id = ?";

$stmt_update = $conn->prepare($sql_update);

if (!$stmt_update) {
    header("Location: ../for-cinco.php?status=prepare_error");
    exit();
}

$stmt_update->bind_param(
    'ssssssssi',
    $nombre_entidad_receptora,
    $ruc,
    $direccion_entidad_receptora,
    $nombre_archivo_logo,
    $nombre_ciudad,
    $nombre_representante,
    $numero_institucional,
    $correo_representante,
    $usuario_id_session
);

if ($stmt_update->execute()) {
    header("Location: ../for-cinco.php?status=update");
    exit();
} else {
    header("Location: ../for-cinco.php?status=db_error");
    exit();
}

$stmt_update->close();
$conn->close();
