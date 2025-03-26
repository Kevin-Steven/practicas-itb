<?php
session_start();
require '../../config/config.php';

// Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Directorio donde se almacenarán las imágenes
$uploadDir = '../../uploads/evidencias/for-quince/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Función para subir imagen y devolver la ruta relativa
function subirImagen($inputName, $uploadDir) {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = uniqid() . '_' . basename($_FILES[$inputName]['name']);
        $rutaDestino = $uploadDir . $nombreArchivo;
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $rutaDestino)) {
            return '../uploads/evidencias/for-quince/' . $nombreArchivo; // Ruta relativa para guardar en DB
        }
    }
    return null;
}

// Subir imágenes
$img1 = subirImagen('img_estudiante_area_trabajo', $uploadDir);
$img2 = subirImagen('img_estudiante_area_trabajo_herramientas', $uploadDir);
$img3 = subirImagen('img_estudiante_supervisor_entidad', $uploadDir);

// Verificar que todas las imágenes se hayan subido correctamente
if (!$img1 || !$img2 || !$img3) {
    header("Location: ../for-quince.php?status=missing_images");
    exit();
}

// Insertar en la base de datos
$sql = "INSERT INTO documento_quince (
    usuario_id,
    img_estudiante_area_trabajo,
    img_estudiante_area_trabajo_herramientas,
    img_estudiante_supervisor_entidad,
    estado
) VALUES (?, ?, ?, ?, 'Pendiente')";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../for-quince.php?status=db_error");
    exit();
}

$stmt->bind_param("isss", $usuario_id, $img1, $img2, $img3);

if ($stmt->execute()) {
    header("Location: ../for-quince.php?status=success");
} else {
    header("Location: ../for-quince.php?status=db_error");
}

$stmt->close();
$conn->close();
exit();
