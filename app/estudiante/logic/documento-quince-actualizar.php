
<?php
session_start();
require '../../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$uploadDir = '../../uploads/evidencias/for-quince/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Función para subir imagen y retornar ruta relativa
function subirImagen($inputName, $uploadDir) {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = uniqid() . '_' . basename($_FILES[$inputName]['name']);
        $rutaDestino = $uploadDir . $nombreArchivo;
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $rutaDestino)) {
            return '../uploads/evidencias/for-quince/' . $nombreArchivo;
        }
    }
    return null;
}

// Obtener el documento existente
$sql_get = "SELECT id, img_estudiante_area_trabajo, img_estudiante_area_trabajo_herramientas, img_estudiante_supervisor_entidad FROM documento_quince WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("i", $usuario_id);
$stmt_get->execute();
$result = $stmt_get->get_result();

if ($row = $result->fetch_assoc()) {
    $documento_id = $row['id'];
    $img_actual1 = $row['img_estudiante_area_trabajo'];
    $img_actual2 = $row['img_estudiante_area_trabajo_herramientas'];
    $img_actual3 = $row['img_estudiante_supervisor_entidad'];
} else {
    header("Location: ../for-quince.php?status=not_found");
    exit();
}
$stmt_get->close();

// Cargar nuevas imágenes
$img1 = subirImagen('img_estudiante_area_trabajo', $uploadDir) ?? $img_actual1;
$img2 = subirImagen('img_estudiante_area_trabajo_herramientas', $uploadDir) ?? $img_actual2;
$img3 = subirImagen('img_estudiante_supervisor_entidad', $uploadDir) ?? $img_actual3;

// Verificar si hubo cambios reales
if (
    $img1 === $img_actual1 &&
    $img2 === $img_actual2 &&
    $img3 === $img_actual3
) {
    header("Location: ../for-quince.php");
    exit();
}

// Realizar el update
$sql_update = "UPDATE documento_quince SET 
    img_estudiante_area_trabajo = ?, 
    img_estudiante_area_trabajo_herramientas = ?, 
    img_estudiante_supervisor_entidad = ?, 
    fecha_subida = CURRENT_TIMESTAMP
WHERE id = ? AND usuario_id = ?";

$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("sssii", $img1, $img2, $img3, $documento_id, $usuario_id);

if ($stmt_update->execute()) {
    header("Location: ../for-quince.php?status=update");
} else {
    header("Location: ../for-quince-edit.php?status=db_error");
}
exit();
?>
