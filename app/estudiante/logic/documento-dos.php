<?php
session_start();
require '../../config/config.php';

// 1. Verificar la sesión activa (seguridad)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// 2. Verificar que el usuario_id enviado en el form coincida con la sesión
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);

if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-dos.php?status=invalid_user");
    exit();
}

// 3. Obtener la cédula del usuario para nombrar el archivo
$sql_cedula = "SELECT cedula FROM usuarios WHERE id = ?";
$stmt_cedula = $conn->prepare($sql_cedula);
$stmt_cedula->bind_param("i", $usuario_id_session);
$stmt_cedula->execute();
$result = $stmt_cedula->get_result();

if ($result->num_rows === 0) {
    header("Location: ../for-dos.php?status=not_found");
    exit();
}

$row = $result->fetch_assoc();
$cedula = $row['cedula'];
$stmt_cedula->close();

// 4. Recibir y validar los datos del formulario
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$hora_fin = $_POST['hora_fin'] ?? '';
$horas_practicas = floatval($_POST['horas_practicas'] ?? 0);
$nota_eva_s = floatval($_POST['nota_eva-s'] ?? 0);

// Validaciones básicas
if (empty($fecha_inicio) || empty($hora_inicio) || empty($fecha_fin) || empty($hora_fin) || $horas_practicas <= 0) {
    header("Location: ../for-dos.php?status=missing_data");
    exit();
}

if ($nota_eva_s < 0 || $nota_eva_s > 100) {
    header("Location: ../for-dos.php?status=invalid_nota");
    exit();
}

// 5. Manejo del archivo EVA-S
if (isset($_FILES['eva_s']) && $_FILES['eva_s']['error'] === UPLOAD_ERR_OK) {

    $archivo_tmp = $_FILES['eva_s']['tmp_name'];
    $nombre_original = $_FILES['eva_s']['name'];

    // Extensión del archivo
    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
    $extension = strtolower($extension);

    // Extensiones permitidas: imágenes + documentos
    $extensiones_permitidas = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif'];

    // ✅ Si quieres aceptar cualquier extensión, comenta esta condición (no recomendado por seguridad)
    if (!in_array($extension, $extensiones_permitidas)) {
        header("Location: ../for-dos.php?status=invalid_extension");
        exit();
    }

    // Ruta de guardado
    $ruta_destino = '../../uploads/eva-s/';
    
    // Asegura que el directorio exista
    if (!is_dir($ruta_destino)) {
        mkdir($ruta_destino, 0775, true);
    }

    $nombre_archivo = 'eva-s-' . $cedula . '.' . $extension;
    $ruta_completa = $ruta_destino . $nombre_archivo;

    // Mover el archivo
    if (!move_uploaded_file($archivo_tmp, $ruta_completa)) {
        header("Location: ../for-dos.php?status=upload_error");
        exit();
    }

} else {
    header("Location: ../for-dos.php?status=no_file");
    exit();
}

// 6. Guardar los datos en la tabla documento_dos
$sql_insert = "INSERT INTO documento_dos 
    (usuario_id, fecha_inicio, hora_inicio, fecha_fin, hora_fin, hora_practicas, documento_eva_s, nota_eva_s, estado) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')";

$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param(
    "issssdsd",
    $usuario_id_session,
    $fecha_inicio,
    $hora_inicio,
    $fecha_fin,
    $hora_fin,
    $horas_practicas,
    $nombre_archivo,
    $nota_eva_s
);

if ($stmt_insert->execute()) {
    header("Location: ../for-dos.php?status=success");
    exit();
} else {
    header("Location: ../for-dos.php?status=db_error");
    exit();
}

$stmt_insert->close();
$conn->close();
?>
