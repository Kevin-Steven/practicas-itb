<?php
require '../../config/config.php';
session_start();

// 1. Validar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar que se hayan enviado todos los campos del formulario
$campos = ['pregunta1', 'pregunta2', 'pregunta3', 'pregunta5', 'pregunta6', 'pregunta7', 'pregunta8', 'pregunta9', 'pregunta10'];
foreach ($campos as $campo) {
    if (!isset($_POST[$campo]) || !is_numeric($_POST[$campo])) {
        header("Location: ../for-diez.php?status=missing_data");
        exit();
    }
}

// Asignar valores
$p1 = intval($_POST['pregunta1']);
$p2 = intval($_POST['pregunta2']);
$p3 = intval($_POST['pregunta3']);
$p5 = intval($_POST['pregunta5']);
$p6 = intval($_POST['pregunta6']);
$p7 = intval($_POST['pregunta7']);
$p8 = intval($_POST['pregunta8']);
$p9 = intval($_POST['pregunta9']);
$p10 = intval($_POST['pregunta10']);

// Insertar en la base de datos
$sql = "INSERT INTO documento_diez (
    usuario_id,
    opcion_uno_puntaje,
    opcion_dos_puntaje,
    opcion_tres_puntaje,
    opcion_cuatro_puntaje,
    opcion_cinco_puntaje,
    opcion_seis_puntaje,
    opcion_siete_puntaje,
    opcion_ocho_puntaje,
    opcion_nueve_puntaje,
    opcion_diez_puntaje
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../for-diez.php?status=db_error");
    exit();
}

$stmt->bind_param("iiiiiiiiiii", $usuario_id, $p1, $p2, $p3, $p5, $p6, $p7, $p8, $p9, $p10, $p10); // El último repite por falta de pregunta11
$ejecutado = $stmt->execute();

if ($ejecutado) {
    header("Location: ../for-diez.php?status=success");
} else {
    header("Location: ../for-diez.php?status=db_error");
}
exit();
