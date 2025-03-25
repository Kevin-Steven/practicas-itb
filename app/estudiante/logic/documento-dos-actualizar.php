<?php
session_start();
require '../../config/config.php';

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Recibir los datos
$fecha_inicio     = $_POST['fecha_inicio'] ?? null;
$hora_inicio      = $_POST['hora_inicio'] ?? null;
$fecha_fin        = $_POST['fecha_fin'] ?? null;
$hora_fin         = $_POST['hora_fin'] ?? null;
$horas_practicas  = floatval($_POST['horas_practicas'] ?? 0);
$nota_eva_s       = floatval($_POST['nota_eva-s'] ?? 0);
$nombre_tutor_academico = $_POST['tutor_academico'] ?? null;
$cedula_tutor_academico = $_POST['cedula_tutor'] ?? null;
$correo_tutor_academico = $_POST['correo_tutor'] ?? null;

if (!$fecha_inicio || !$hora_inicio || !$fecha_fin || !$hora_fin || $horas_practicas <= 0 || $nota_eva_s < 0 || $nota_eva_s > 100 || empty($nombre_tutor_academico) || empty($cedula_tutor_academico) || empty($correo_tutor_academico)) {
    header("Location: ../for-dos-edit.php?id=$usuario_id&status=missing_data");
    exit();
}

// Verificar si el documento existe
$sql_check = "SELECT id, documento_eva_s FROM documento_dos WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $usuario_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    header("Location: ../for-dos-edit.php?status=not_found");
    exit();
}

$documento = $result_check->fetch_assoc();
$documento_id = $documento['id'];
$archivo_anterior = $documento['documento_eva_s'];

$stmt_check->close();

$target_dir = "../../uploads/eva-s/";
$archivo_nombre_final = $archivo_anterior;

if (isset($_FILES['eva_s']) && $_FILES['eva_s']['error'] === 0) {
    $file_tmp  = $_FILES['eva_s']['tmp_name'];
    $file_name = $_FILES['eva_s']['name'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];

    if (!in_array($file_ext, $allowed_exts)) {
        header("Location: ../for-dos-edit.php?id=$usuario_id&status=invalid_extension");
        exit();
    }

    $archivo_nombre_final = 'eva-s-' . $usuario_id . '.' . $file_ext;
    $target_file = $target_dir . $archivo_nombre_final;

    if (!move_uploaded_file($file_tmp, $target_file)) {
        header("Location: ../for-dos-edit.php?id=$usuario_id&status=upload_error");
        exit();
    }

    if ($archivo_anterior && $archivo_anterior !== $archivo_nombre_final && file_exists($target_dir . $archivo_anterior)) {
        unlink($target_dir . $archivo_anterior);
    }
}

$sql_update = "UPDATE documento_dos 
               SET fecha_inicio = ?, hora_inicio = ?, fecha_fin = ?, hora_fin = ?, hora_practicas = ?, documento_eva_s = ?, nota_eva_s = ?, nombre_tutor_academico = ?, cedula_tutor_academico = ?, correo_tutor_academico = ?
               WHERE id = ?";

$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("ssssisdsssi", 
    $fecha_inicio, 
    $hora_inicio, 
    $fecha_fin, 
    $hora_fin, 
    $horas_practicas, 
    $archivo_nombre_final, 
    $nota_eva_s,
    $nombre_tutor_academico,
    $cedula_tutor_academico,
    $correo_tutor_academico,
    $documento_id
);

if ($stmt_update->execute()) {
    header("Location: ../for-dos.php?status=update");
    exit();
} else {
    header("Location: ../for-dos-edit.php?id=$usuario_id&status=db_error");
    exit();
}

$stmt_update->close();
$conn->close();
?>
