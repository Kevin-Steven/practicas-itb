<?php
session_start();
require '../../config/config.php';

// 1. Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// 2. Verificar el usuario_id recibido desde el formulario
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);

if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-cinco.php?status=invalid_user");
    exit();
}

// 3. Recibir y limpiar datos del formulario
$ruc = trim($_POST['ruc'] ?? '');
$direccion_entidad_receptora = trim($_POST['direccion-entidad'] ?? '');
$nombre_representante = trim($_POST['nombres-representante-rrhh'] ?? '');
$correo_institucional = trim($_POST['correo-institucional'] ?? '');
$numero_institucional = trim($_POST['numero_institucional'] ?? '');

// Formatear el nombre del representante (opcional para consistencia)
$nombre_representante = mb_convert_case(mb_strtolower($nombre_representante, 'UTF-8'), MB_CASE_TITLE, "UTF-8");

// 4. Validaciones básicas
if (
    empty($ruc) ||
    empty($direccion_entidad_receptora) ||
    empty($nombre_representante) ||
    empty($correo_institucional) ||
    empty($numero_institucional)
) {
    header("Location: ../for-cinco.php?status=missing_data");
    exit();
}

// Validaciones específicas
if (!preg_match('/^\d{13}$/', $ruc)) {
    header("Location: ../for-cinco.php?status=invalid_ruc");
    exit();
}

if (!filter_var($correo_institucional, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../for-cinco.php?status=invalid_email");
    exit();
}

if (!preg_match('/^\d{10}$/', $numero_institucional)) {
    header("Location: ../for-cinco.php?status=invalid_phone");
    exit();
}

// 5. Obtener el último registro actual de documento_cinco para este usuario
$sql_actual = "SELECT 
    ruc, 
    direccion_entidad_receptora, 
    nombre_representante_rrhh, 
    correo_institucional, 
    numero_institucional,
    logo_entidad_receptora
FROM documento_cinco
WHERE usuario_id = ?
ORDER BY id DESC
LIMIT 1";

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

// 6. Procesar el logo (si se subió uno nuevo)
$logo_entidad_receptora_actual = $datos_actuales['logo_entidad_receptora'];
$nombre_archivo_logo = $logo_entidad_receptora_actual; // Si no cambia el logo, sigue el mismo archivo

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

    $nombre_archivo_logo = 'logo-' . $ruc . '.' . $extension;
    $ruta_completa_logo = $ruta_destino . $nombre_archivo_logo;

    if (!move_uploaded_file($archivo_tmp, $ruta_completa_logo)) {
        header("Location: ../for-cinco.php?status=upload_error");
        exit();
    }
}

// 7. Verificar si hubo cambios para evitar actualizaciones innecesarias
$hubo_cambios = false;

if (
    $ruc !== $datos_actuales['ruc'] ||
    $direccion_entidad_receptora !== $datos_actuales['direccion_entidad_receptora'] ||
    $nombre_representante !== $datos_actuales['nombre_representante_rrhh'] ||
    $correo_institucional !== $datos_actuales['correo_institucional'] ||
    $numero_institucional !== $datos_actuales['numero_institucional'] ||
    $nombre_archivo_logo !== $datos_actuales['logo_entidad_receptora']
) {
    $hubo_cambios = true;
}

// 8. Si no hubo cambios, regresar sin actualizar
if (!$hubo_cambios) {
    header("Location: ../for-cinco.php");
    exit();
}

// 9. Ejecutar el UPDATE si hubo cambios
$sql_update = "UPDATE documento_cinco SET
    ruc = ?,
    direccion_entidad_receptora = ?,
    logo_entidad_receptora = ?,
    nombre_representante_rrhh = ?,
    numero_institucional = ?,
    correo_institucional = ?,
    estado = 'Pendiente'
WHERE usuario_id = ?";

$stmt_update = $conn->prepare($sql_update);

if (!$stmt_update) {
    header("Location: ../for-cinco.php?status=prepare_error");
    exit();
}

$stmt_update->bind_param(
    'ssssssi',
    $ruc,
    $direccion_entidad_receptora,
    $nombre_archivo_logo,
    $nombre_representante,
    $numero_institucional,
    $correo_institucional,
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
?>
