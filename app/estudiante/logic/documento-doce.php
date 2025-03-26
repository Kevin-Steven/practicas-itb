<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Validar preguntas
$campos = ['pregunta1', 'pregunta2', 'pregunta3', 'pregunta4', 'pregunta5', 'pregunta6'];
foreach ($campos as $campo) {
    if (!isset($_POST[$campo])) {
        header("Location: ../for-doce.php?status=missing_data");
        exit();
    }
}

// Convertir a enteros
$p1 = intval($_POST['pregunta1']);
$p2 = intval($_POST['pregunta2']);
$p3 = intval($_POST['pregunta3']);
$p4 = intval($_POST['pregunta4']);
$p5 = intval($_POST['pregunta5']);
$p6 = intval($_POST['pregunta6']);

// Directorio donde se subirán las imágenes
$uploadDir = '../../uploads/evidencias/for-doce/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Función para subir imagen y devolver la ruta
function subirImagen($inputName, $uploadDir) {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = uniqid() . '_' . basename($_FILES[$inputName]['name']);
        $rutaDestino = $uploadDir . $nombreArchivo;

        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $rutaDestino)) {
            return '../uploads/evidencias/for-doce/' . $nombreArchivo; // Ruta relativa para la DB
        }
    }
    return null;
}

// Subir las imágenes
$img1 = subirImagen('img_practicas_puesto_trabajo', $uploadDir);
$img2 = subirImagen('img_puesto_trabajo', $uploadDir);
$img3 = subirImagen('img_estudiante_tutor_entidad', $uploadDir);
$img4 = subirImagen('img_cierre_practicas', $uploadDir);

// Verificar que se subieron todas
if (!$img1 || !$img2 || !$img3 || !$img4) {
    header("Location: ../for-doce.php?status=missing_images");
    exit();
}

// Insertar en la base de datos
$sql = "INSERT INTO documento_doce (
    usuario_id,
    opcion_uno,
    opcion_dos,
    opcion_tres,
    opcion_cuatro,
    opcion_cinco,
    opcion_seis,
    img_practicas_puesto_trabajo,
    img_puesto_trabajo,
    img_estudiante_tutor_entidad,
    img_cierre_practicas
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../for-doce.php?status=db_error");
    exit();
}

$stmt->bind_param(
    "iiiiissssss",
    $usuario_id, $p1, $p2, $p3, $p4, $p5, $p6,
    $img1, $img2, $img3, $img4
);

if ($stmt->execute()) {
    header("Location: ../for-doce.php?status=success");
} else {
    header("Location: ../for-doce.php?status=db_error");
}
exit();
