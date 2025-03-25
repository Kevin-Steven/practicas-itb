<?php
session_start();
require '../../config/config.php';

// Validar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Validar si hay archivo subido
if (!isset($_FILES['pdf-escaneado']) || $_FILES['pdf-escaneado']['error'] === UPLOAD_ERR_NO_FILE) {
    // No se seleccionó nuevo archivo, redirigir sin hacer cambios
    header("Location: ../for-catorce.php?status=no_changes");
    exit();
}

if ($_FILES['pdf-escaneado']['error'] !== UPLOAD_ERR_OK) {
    header("Location: ../for-catorce.php?status=upload_error");
    exit();
}

// Validar que sea un PDF
$tipoArchivo = mime_content_type($_FILES['pdf-escaneado']['tmp_name']);
if ($tipoArchivo !== 'application/pdf') {
    header("Location: ../for-catorce.php?status=invalid_format");
    exit();
}

// Directorio donde se guardan los PDFs
$uploadDir = '../../uploads/pdfs-escaneados/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generar nombre de archivo
$nombreArchivo = uniqid() . '_' . basename($_FILES['pdf-escaneado']['name']);
$rutaDestino = $uploadDir . $nombreArchivo;
$rutaParaDB = '../uploads/pdfs-escaneados/' . $nombreArchivo;

// Obtener el ID del documento más reciente
$sql_get_id = "SELECT id FROM documento_catorce WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_get = $conn->prepare($sql_get_id);
$stmt_get->bind_param("i", $usuario_id);
$stmt_get->execute();
$result = $stmt_get->get_result();

if ($row = $result->fetch_assoc()) {
    $documento_id = $row['id'];
    $stmt_get->close();

    // Mover archivo al destino final
    if (!move_uploaded_file($_FILES['pdf-escaneado']['tmp_name'], $rutaDestino)) {
        header("Location: ../for-catorce.php?status=upload_error");
        exit();
    }

    // Actualizar el archivo en la base de datos
    $sql_update = "UPDATE documento_catorce SET pdf_escaneado = ? WHERE id = ? AND usuario_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sii", $rutaParaDB, $documento_id, $usuario_id);

    if ($stmt_update->execute()) {
        header("Location: ../for-catorce.php?status=update");
    } else {
        header("Location: ../for-catorce.php?status=db_error");
    }
    $stmt_update->close();
} else {
    header("Location: ../for-catorce.php?status=not_found");
    exit();
}

$conn->close();
exit();
