<?php
session_start();
require '../../config/config.php';

// 1. Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 2. Validar que se haya enviado un archivo
if (!isset($_FILES['pdf-escaneado']) || $_FILES['pdf-escaneado']['error'] !== UPLOAD_ERR_OK) {
    header("Location: ../for-cuatro.php?status=missing_file");
    exit();
}

// 3. Configurar la ruta de subida
$uploadDir = '../../uploads/pdfs-escaneados/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 4. Validar extensión y mover archivo
$nombreArchivo = basename($_FILES['pdf-escaneado']['name']);
$extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

if ($extension !== 'pdf') {
    header("Location: ../for-cuatro.php?status=invalid_format");
    exit();
}

$nombreFinal = uniqid('pdf_4_') . '.' . $extension;
$rutaDestino = $uploadDir . $nombreFinal;

if (!move_uploaded_file($_FILES['pdf-escaneado']['tmp_name'], $rutaDestino)) {
    header("Location: ../for-cuatro.php?status=upload_error");
    exit();
}

// Ruta relativa para guardar en la BD
$rutaBD = '../uploads/pdfs-escaneados/' . $nombreFinal;

// 5. Insertar en la base de datos
$sql = "INSERT INTO documento_cuatro (usuario_id, pdf_escaneado) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../for-cuatro.php?status=db_error");
    exit();
}

$stmt->bind_param("is", $usuario_id, $rutaBD);

if ($stmt->execute()) {
    header("Location: ../for-cuatro.php?status=success");
} else {
    header("Location: ../for-cuatro.php?status=db_error");
}

$stmt->close();
$conn->close();
exit();
