<?php
session_start();
require '../../config/config.php';

// 1. Validar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = intval($_SESSION['usuario_id']);

// 2. Recibir los valores de los campos enviados desde el formulario
$preguntas = [];
for ($i = 1; $i <= 15; $i++) {
    $campo = 'pregunta' . $i;
    $preguntas[$i] = isset($_POST[$campo]) ? intval($_POST[$campo]) : null;
}

// 3. Validar que se hayan respondido todas las preguntas
foreach ($preguntas as $index => $valor) {
    if (is_null($valor)) {
        header("Location: ../for-nueve.php?status=missing_data");
        exit();
    }

    // Validar el rango permitido de 1 a 5 (por seguridad extra)
    if ($valor < 1 || $valor > 5) {
        header("Location: ../for-nueve.php?status=invalid_value");
        exit();
    }
}

// 4. Verificar si ya existe un documento_nueve para este usuario
$sql_check = "SELECT id FROM documento_nueve WHERE usuario_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $usuario_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // ✅ Si existe, se actualiza
    $documento_id = $result_check->fetch_assoc()['id'];

    $sql_update = "UPDATE documento_nueve SET 
        opcion_uno_puntaje = ?, 
        opcion_dos_puntaje = ?, 
        opcion_tres_puntaje = ?, 
        opcion_cuatro_puntaje = ?, 
        opcion_cinco_puntaje = ?, 
        opcion_seis_puntaje = ?, 
        opcion_siete_puntaje = ?, 
        opcion_ocho_puntaje = ?, 
        opcion_nueve_puntaje = ?, 
        opcion_diez_puntaje = ?, 
        opcion_once_puntaje = ?, 
        opcion_doce_puntaje = ?, 
        opcion_trece_puntaje = ?, 
        opcion_catorce_puntaje = ?, 
        opcion_quince_puntaje = ?, 
        estado = 'Pendiente'
    WHERE usuario_id = ?";

    $stmt_update = $conn->prepare($sql_update);

    if (!$stmt_update) {
        header("Location: ../for-nueve.php?status=prepare_error");
        exit();
    }

    $stmt_update->bind_param(
        "iiiiiiiiiiiiiiii",
        $preguntas[1],
        $preguntas[2],
        $preguntas[3],
        $preguntas[4],
        $preguntas[5],
        $preguntas[6],
        $preguntas[7],
        $preguntas[8],
        $preguntas[9],
        $preguntas[10],
        $preguntas[11],
        $preguntas[12],
        $preguntas[13],
        $preguntas[14],
        $preguntas[15],
        $usuario_id
    );

    if ($stmt_update->execute()) {
        header("Location: ../for-nueve.php?status=updated");
        exit();
    } else {
        header("Location: ../for-nueve.php?status=db_error");
        exit();
    }

} else {
    // ✅ Si NO existe, se inserta
    $sql_insert = "INSERT INTO documento_nueve (
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
        opcion_diez_puntaje, 
        opcion_once_puntaje, 
        opcion_doce_puntaje, 
        opcion_trece_puntaje, 
        opcion_catorce_puntaje, 
        opcion_quince_puntaje, 
        estado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')";

    $stmt_insert = $conn->prepare($sql_insert);

    if (!$stmt_insert) {
        header("Location: ../for-nueve.php?status=prepare_error");
        exit();
    }

    $stmt_insert->bind_param(
        "iiiiiiiiiiiiiiii",
        $usuario_id,
        $preguntas[1],
        $preguntas[2],
        $preguntas[3],
        $preguntas[4],
        $preguntas[5],
        $preguntas[6],
        $preguntas[7],
        $preguntas[8],
        $preguntas[9],
        $preguntas[10],
        $preguntas[11],
        $preguntas[12],
        $preguntas[13],
        $preguntas[14],
        $preguntas[15]
    );

    if ($stmt_insert->execute()) {
        header("Location: ../for-nueve.php?status=success");
        exit();
    } else {
        header("Location: ../for-nueve.php?status=db_error");
        exit();
    }
}

$conn->close();
?>
