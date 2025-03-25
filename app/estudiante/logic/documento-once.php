<?php
require '../../config/config.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

$campos = ['pregunta1', 'pregunta2', 'pregunta3', 'pregunta4', 'pregunta5', 'pregunta6'];
foreach ($campos as $campo) {
    if (!isset($_POST[$campo])) {
        header("Location: ../for-once.php?status=missing_data");
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

$sql = "INSERT INTO documento_once (
    usuario_id,
    opcion_uno,
    opcion_dos,
    opcion_tres,
    opcion_cuatro,
    opcion_cinco,
    opcion_seis
) VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../for-once.php?status=db_error");
    exit();
}

$stmt->bind_param("iiiiiii", $usuario_id, $p1, $p2, $p3, $p4, $p5, $p6);

if ($stmt->execute()) {
    header("Location: ../for-once.php?status=success");
} else {
    header("Location: ../for-once.php?status=db_error");
}
exit();
