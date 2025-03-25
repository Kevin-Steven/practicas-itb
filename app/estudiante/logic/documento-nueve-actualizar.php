<?php
session_start();
require '../../config/config.php';

// ✅ 1. Verificación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id_session = $_SESSION['usuario_id'];

// ✅ 2. Recibir los datos del formulario
$usuario_id_form = intval($_POST['usuario_id'] ?? 0);

// Validación de que el usuario coincida con el de la sesión
if ($usuario_id_form !== $usuario_id_session) {
    header("Location: ../for-nueve.php?status=invalid_user");
    exit();
}

// ✅ 3. Recoger las respuestas de las 15 preguntas
$respuestas = [];
for ($i = 1; $i <= 15; $i++) {
    $respuesta = isset($_POST["pregunta$i"]) ? intval($_POST["pregunta$i"]) : null;

    // ✅ Validamos que exista y que esté entre 1 y 5
    if (is_null($respuesta) || $respuesta < 1 || $respuesta > 5) {
        header("Location: ../for-nueve.php?status=missing_data");
        exit();
    }

    $respuestas[] = $respuesta;
}

// ✅ 4. Revisamos si ya existe un documento_nueve para este usuario
$sql_check = "SELECT id FROM documento_nueve WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $usuario_id_session);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    // ✅ 5. No existe -> Insertamos un nuevo registro
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
        opcion_quince_puntaje
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param(
        "iiiiiiiiiiiiiiii",
        $usuario_id_session,
        $respuestas[0],
        $respuestas[1],
        $respuestas[2],
        $respuestas[3],
        $respuestas[4],
        $respuestas[5],
        $respuestas[6],
        $respuestas[7],
        $respuestas[8],
        $respuestas[9],
        $respuestas[10],
        $respuestas[11],
        $respuestas[12],
        $respuestas[13],
        $respuestas[14]
    );

    if ($stmt_insert->execute()) {
        header("Location: ../for-nueve.php?status=success");
        exit();
    } else {
        header("Location: ../for-nueve.php?status=db_error");
        exit();
    }
} else {
    // ✅ 6. Ya existe -> Actualizamos el registro más reciente
    $fila = $result_check->fetch_assoc();
    $documento_nueve_id = $fila['id'];

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
    opcion_quince_puntaje = ?
WHERE id = ?";

    $stmt_update = $conn->prepare($sql_update);

    if (!$stmt_update) {
        header("Location: ../for-nueve.php?status=prepare_error");
        exit();
    }

    $stmt_update->bind_param(
        "iiiiiiiiiiiiiiii", // 16 en total
        $respuestas[0],
        $respuestas[1],
        $respuestas[2],
        $respuestas[3],
        $respuestas[4],
        $respuestas[5],
        $respuestas[6],
        $respuestas[7],
        $respuestas[8],
        $respuestas[9],
        $respuestas[10],
        $respuestas[11],
        $respuestas[12],
        $respuestas[13],
        $respuestas[14],
        $documento_nueve_id
    );


    if ($stmt_update->execute()) {
        header("Location: ../for-nueve.php?status=updated");
        exit();
    } else {
        header("Location: ../for-nueve.php?status=db_error");
        exit();
    }
}

// ✅ Cierre de conexiones
$stmt_check->close();
$conn->close();
