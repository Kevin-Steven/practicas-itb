<?php
require '../../config/config.php';
session_start();

// Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Validar campos requeridos
$campos = ['pregunta1', 'pregunta2', 'pregunta3', 'pregunta4', 'pregunta5', 'pregunta6'];
foreach ($campos as $campo) {
    if (!isset($_POST[$campo])) {
        header("Location: ../for-doce-edit.php?status=missing_data");
        exit();
    }
}

// Convertir respuestas a enteros
$p1 = intval($_POST['pregunta1']);
$p2 = intval($_POST['pregunta2']);
$p3 = intval($_POST['pregunta3']);
$p4 = intval($_POST['pregunta4']);
$p5 = intval($_POST['pregunta5']);
$p6 = intval($_POST['pregunta6']);

// Directorio para evidencias
$uploadDir = '../../uploads/evidencias/for-doce/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Función para subir imagen
function subirImagen($inputName, $uploadDir)
{
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = uniqid() . '_' . basename($_FILES[$inputName]['name']);
        $rutaDestino = $uploadDir . $nombreArchivo;

        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $rutaDestino)) {
            return '../uploads/evidencias/for-doce/' . $nombreArchivo; // Ruta relativa para DB
        }
    }
    return null;
}

// Obtener ID del documento existente
$sql_get_id = "SELECT id FROM documento_doce WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_id = $conn->prepare($sql_get_id);
$stmt_id->bind_param("i", $usuario_id);
$stmt_id->execute();
$result = $stmt_id->get_result();

if (!$result || $result->num_rows === 0) {
    header("Location: ../for-doce-edit.php?status=db_error");
    exit();
}
$documento_id = $result->fetch_assoc()['id'];
$stmt_id->close();

// Subir nuevas imágenes si se proporcionan
$img1 = subirImagen('img_practicas_puesto_trabajo', $uploadDir);
$img2 = subirImagen('img_puesto_trabajo', $uploadDir);
$img3 = subirImagen('img_estudiante_tutor_entidad', $uploadDir);
$img4 = subirImagen('img_cierre_practicas', $uploadDir);

// Construir consulta dinámicamente
$update = "UPDATE documento_doce SET 
    opcion_uno = ?, 
    opcion_dos = ?, 
    opcion_tres = ?, 
    opcion_cuatro = ?, 
    opcion_cinco = ?, 
    opcion_seis = ?";

$params = [$p1, $p2, $p3, $p4, $p5, $p6];
$types = "iiiiii";

if ($img1 !== null) {
    $update .= ", img_practicas_puesto_trabajo = ?";
    $params[] = $img1;
    $types .= "s";
}
if ($img2 !== null) {
    $update .= ", img_puesto_trabajo = ?";
    $params[] = $img2;
    $types .= "s";
}
if ($img3 !== null) {
    $update .= ", img_estudiante_tutor_entidad = ?";
    $params[] = $img3;
    $types .= "s";
}
if ($img4 !== null) {
    $update .= ", img_cierre_practicas = ?";
    $params[] = $img4;
    $types .= "s";
}

$update .= " WHERE id = ? AND usuario_id = ?";
$params[] = $documento_id;
$params[] = $usuario_id;
$types .= "ii";

// Preparar y ejecutar
$stmt = $conn->prepare($update);
if (!$stmt) {
    header("Location: ../for-doce-edit.php?status=db_error");
    exit();
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    header("Location: ../for-doce.php?status=update");
} else {
    header("Location: ../for-doce-edit.php?status=db_error");
}
exit();
