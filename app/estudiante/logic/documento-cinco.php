<?php
session_start();
require '../../config/config.php'; // Ajusta si tu config está en otra ruta

// 1. Verificar la sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// 2. Verificar que el usuario_id enviado en el formulario coincida con el de la sesión
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);

if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-cinco.php?status=invalid_user");
    exit();
}

// 3. Recibir y validar los datos del formulario
$nombre_entidad = trim($_POST['nombre_entidad'] ?? '');
$ruc = trim($_POST['ruc'] ?? '');
$direccion_entidad = trim($_POST['direccion-entidad'] ?? '');
$nombre_ciudad = trim($_POST['ciudad'] ?? '');
$nombre_representante = trim($_POST['nombres-representante-rrhh'] ?? '');
$correo_representante = trim($_POST['correo-entidad'] ?? '');
$numero_representante_rrhh = trim($_POST['numero_representante_rrhh'] ?? '');
$estado = 'Pendiente';

$nombre_representante = mb_strtolower($nombre_representante, 'UTF-8');
$nombre_representante = mb_convert_case($nombre_representante, MB_CASE_TITLE, "UTF-8");

// Validaciones básicas
if (
    empty($nombre_entidad) ||
    empty($ruc) ||
    empty($direccion_entidad) ||
    empty($nombre_ciudad) ||
    empty($nombre_representante) ||
    empty($correo_representante) ||
    empty($numero_representante_rrhh)
) {
    header("Location: ../for-cinco.php?status=missing_data");
    exit();
}

// Validación del RUC: debe tener exactamente 13 dígitos
if (!preg_match('/^\d{13}$/', $ruc)) {
    header("Location: ../for-cinco.php?status=invalid_ruc");
    exit();
}

// Validación del correo electrónico
if (!filter_var($correo_representante, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../for-cinco.php?status=invalid_email");
    exit();
}

// Validación del número de teléfono
if (!preg_match('/^\d{10}$/', $numero_representante_rrhh)) {
    header("Location: ../for-cinco.php?status=invalid_phone");
    exit();
}

// 4. Manejo del archivo logo de la entidad
if (isset($_FILES['logo-entidad']) && $_FILES['logo-entidad']['error'] === UPLOAD_ERR_OK) {

    $archivo_tmp = $_FILES['logo-entidad']['tmp_name'];
    $nombre_original = $_FILES['logo-entidad']['name'];

    // Extensión del archivo
    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
    $extension = strtolower($extension);

    // Extensiones permitidas
    $extensiones_permitidas = ['png', 'jpg', 'jpeg', 'gif'];

    if (!in_array($extension, $extensiones_permitidas)) {
        header("Location: ../for-cinco.php?status=invalid_extension");
        exit();
    }

    // Ruta de guardado
    $ruta_destino = '../../uploads/logo-entidad/';
    
    // Crear el directorio si no existe
    if (!is_dir($ruta_destino)) {
        mkdir($ruta_destino, 0775, true);
    }

    // Nombre final del archivo (ejemplo: logo-1234567890123.png)
    $nombre_archivo_logo = 'logo-' . $ruc . '.' . $extension;
    $ruta_completa_logo = $ruta_destino . $nombre_archivo_logo;

    // Mover el archivo
    if (!move_uploaded_file($archivo_tmp, $ruta_completa_logo)) {
        header("Location: ../for-cinco.php?status=upload_error");
        exit();
    }

} else {
    header("Location: ../for-cinco.php?status=no_logo_file");
    exit();
}

// 5. Insertar los datos en la tabla documento_cinco
$sql_insert = "INSERT INTO documento_cinco (
    usuario_id, 
    nombre_entidad_receptora, 
    ruc, 
    direccion_entidad_receptora, 
    logo_entidad_receptora, 
    nombre_ciudad, 
    nombre_representante_rrhh, 
    numero_representante_rrhh, 
    correo_representante, 
    estado
) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt_insert = $conn->prepare($sql_insert);

if (!$stmt_insert) {
    header("Location: ../for-cinco.php?status=prepare_error&error=" . urlencode($conn->error));
    exit();
}

// Datos que vas a insertar
$nombre_doc_default = '5 CARTA DE COMPROMISO.pdf';

$stmt_insert->bind_param(
    "isssssssss",
    $usuario_id_session,
    $nombre_entidad,
    $ruc,
    $direccion_entidad,
    $nombre_archivo_logo,
    $nombre_ciudad,
    $nombre_representante,
    $numero_representante_rrhh,
    $correo_representante,
    $estado
);

if ($stmt_insert->execute()) {
    header("Location: ../for-cinco.php?status=success");
    exit();
} else {
    header("Location: ../for-cinco.php?status=db_error&error=" . urlencode($stmt_insert->error));
    exit();
}

$stmt_insert->close();
$conn->close();

?>
