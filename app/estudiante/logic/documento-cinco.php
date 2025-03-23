<?php
session_start();
require '../../config/config.php';

// 1. Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// 2. Verificar que el usuario_id del formulario coincida con el de sesión
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);
if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-cinco.php?status=invalid_user");
    exit();
}

// 3. Recibir los datos del formulario
$ruc = trim($_POST['ruc'] ?? '');
$direccion_entidad = trim($_POST['direccion-entidad'] ?? '');
$nombre_representante_rrhh = trim($_POST['nombres-representante-rrhh'] ?? '');
$correo_institucional = trim($_POST['correo-institucional'] ?? '');
$numero_institucional = trim($_POST['numero_institucional'] ?? '');
$estado = 'Pendiente';

$nombre_representante_rrhh = mb_strtolower($nombre_representante_rrhh, 'UTF-8');
$nombre_representante_rrhh = mb_convert_case($nombre_representante_rrhh, MB_CASE_TITLE, "UTF-8");

// 4. Validaciones básicas
if (
    empty($ruc) ||
    empty($direccion_entidad) ||
    empty($nombre_representante_rrhh) ||
    empty($correo_institucional) ||
    empty($numero_institucional)
) {
    header("Location: ../for-cinco.php?status=missing_data");
    exit();
}

// Validación de RUC: 13 dígitos numéricos
if (!preg_match('/^\d{13}$/', $ruc)) {
    header("Location: ../for-cinco.php?status=invalid_ruc");
    exit();
}

// Validación de correo electrónico
if (!filter_var($correo_institucional, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../for-cinco.php?status=invalid_email");
    exit();
}

// Validación de número de teléfono: 10 dígitos numéricos
if (!preg_match('/^\d{10}$/', $numero_institucional)) {
    header("Location: ../for-cinco.php?status=invalid_phone");
    exit();
}

// 5. Manejo del archivo (logo de la entidad)
if (isset($_FILES['logo-entidad']) && $_FILES['logo-entidad']['error'] === UPLOAD_ERR_OK) {
    $archivo_tmp = $_FILES['logo-entidad']['tmp_name'];
    $nombre_original = $_FILES['logo-entidad']['name'];

    // Obtener extensión y convertir a minúscula
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

    // Extensiones permitidas
    $extensiones_permitidas = ['png', 'jpg', 'jpeg', 'gif'];

    if (!in_array($extension, $extensiones_permitidas)) {
        header("Location: ../for-cinco.php?status=invalid_extension");
        exit();
    }

    // Ruta donde se guardará el logo
    $ruta_destino = '../../uploads/logo-entidad/';
    if (!is_dir($ruta_destino)) {
        mkdir($ruta_destino, 0775, true);
    }

    // Nombre final del archivo
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

// 6. Insertar los datos en la tabla documento_cinco
$sql_insert = "INSERT INTO documento_cinco (
    usuario_id, 
    nombre_doc,
    ruc, 
    direccion_entidad_receptora, 
    logo_entidad_receptora, 
    nombre_representante_rrhh, 
    numero_institucional, 
    correo_institucional, 
    estado
) VALUES (?, '5 CARTA DE COMPROMISO', ?, ?, ?, ?, ?, ?, ?)";

$stmt_insert = $conn->prepare($sql_insert);

if (!$stmt_insert) {
    header("Location: ../for-cinco.php?status=prepare_error&error=" . urlencode($conn->error));
    exit();
}

$stmt_insert->bind_param(
    "isssssss",
    $usuario_id_session,
    $ruc,
    $direccion_entidad,
    $nombre_archivo_logo,
    $nombre_representante_rrhh,
    $numero_institucional,
    $correo_institucional,
    $estado
);

// Ejecutar el insert
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
