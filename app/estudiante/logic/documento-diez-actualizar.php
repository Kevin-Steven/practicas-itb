<?php
require '../../config/config.php';
session_start();

// Validar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar que se hayan enviado todos los campos necesarios
$campos = ['pregunta1', 'pregunta2', 'pregunta3', 'pregunta5', 'pregunta6', 'pregunta7', 'pregunta8', 'pregunta9', 'pregunta10'];
foreach ($campos as $campo) {
    if (!isset($_POST[$campo]) || !is_numeric($_POST[$campo])) {
        header("Location: ../for-diez-edit.php?status=missing_data");
        exit();
    }
}

// Obtener el ID del documento más reciente para este usuario
$sql_select = "SELECT id FROM documento_diez WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $usuario_id);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($row = $result->fetch_assoc()) {
    $documento_id = $row['id'];
} else {
    header("Location: ../for-diez-edit.php?status=db_error");
    exit();
}
$stmt_select->close();

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

// Actualizar en la base de datos
$sql_update = "UPDATE documento_diez SET 
    opcion_uno_puntaje = ?, 
    opcion_dos_puntaje = ?, 
    opcion_tres_puntaje = ?, 
    opcion_cuatro_puntaje = ?, 
    opcion_cinco_puntaje = ?, 
    opcion_seis_puntaje = ?, 
    opcion_siete_puntaje = ?, 
    opcion_ocho_puntaje = ?, 
    opcion_nueve_puntaje = ?, 
    opcion_diez_puntaje = ?
WHERE id = ? AND usuario_id = ?";

$stmt_update = $conn->prepare($sql_update);

if (!$stmt_update) {
    header("Location: ../for-diez-edit.php?status=db_error");
    exit();
}

$stmt_update->bind_param("iiiiiiiiiiii", $p1, $p2, $p3, $p5, $p6, $p7, $p8, $p9, $p10, $documento_id, $usuario_id);

if ($stmt_update->execute()) {
    header("Location: ../for-diez.php?status=update");
} else {
    header("Location: ../for-diez-edit.php?status=db_error");
}
exit();
