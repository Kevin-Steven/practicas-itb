<?php
session_start();
require '../../config/config.php';

// 1. Validación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 2. Recibir los datos del formulario
$documento_id = $_POST['documento_id'] ?? null;
$paralelo = trim($_POST['paralelo'] ?? '');
$promedio = floatval($_POST['promedio'] ?? 0);

$lugares = $_POST['lugar_laborado'] ?? [];
$periodos = $_POST['periodo_tiempo'] ?? [];
$funciones = $_POST['funciones_realizadas'] ?? [];

// 3. Validar los datos importantes
if (empty($documento_id) || empty($paralelo) || $promedio <= 0) {
    header("Location: ../for-uno-edit.php?id=$documento_id&status=missing_data");
    exit();
}

// ✅ Validación actualizada: promedio entre 0.00 y 100.00
if ($promedio < 0 || $promedio > 100) {
    header("Location: ../for-uno-edit.php?id=$documento_id&status=invalid_promedio");
    exit();
}

// 4. Verificar que el documento le pertenece al usuario actual
$sql_check = "SELECT id FROM documento_uno WHERE id = ? AND usuario_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $documento_id, $usuario_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    header("Location: ../for-uno.php?status=not_found");
    exit();
}
$stmt_check->close();

// 5. Actualizar el documento_uno con los nuevos datos académicos
$sql_update_doc = "UPDATE documento_uno SET paralelo = ?, promedio_notas = ?, estado = 'Pendiente' WHERE id = ?";
$stmt_update_doc = $conn->prepare($sql_update_doc);
$stmt_update_doc->bind_param("sdi", $paralelo, $promedio, $documento_id);

if (!$stmt_update_doc->execute()) {
    header("Location: ../for-uno-edit.php?id=$documento_id&status=db_error");
    exit();
}

$stmt_update_doc->close();

// 6. Eliminar experiencias laborales anteriores
$sql_delete_exp = "DELETE FROM experiencia_laboral WHERE documento_uno_id = ?";
$stmt_delete_exp = $conn->prepare($sql_delete_exp);
$stmt_delete_exp->bind_param("i", $documento_id);

if (!$stmt_delete_exp->execute()) {
    header("Location: ../for-uno-edit.php?id=$documento_id&status=db_error");
    exit();
}

$stmt_delete_exp->close();

// 7. Insertar nuevas experiencias laborales
$sql_insert_exp = "INSERT INTO experiencia_laboral (documento_uno_id, lugar_laborado, periodo_tiempo_meses, funciones_realizadas) VALUES (?, ?, ?, ?)";
$stmt_insert_exp = $conn->prepare($sql_insert_exp);

for ($i = 0; $i < count($lugares); $i++) {
    $lugar = trim($lugares[$i]);
    $periodo = trim($periodos[$i]);
    $funcion = trim($funciones[$i]);

    // Verificar si el usuario dejó vacío alguno de estos campos
    if (empty($lugar) || empty($periodo) || empty($funcion)) {
        continue; // No insertamos esta experiencia incompleta
    }

    $stmt_insert_exp->bind_param("isss", $documento_id, $lugar, $periodo, $funcion);
    $stmt_insert_exp->execute();
}

$stmt_insert_exp->close();

// 8. Redirigir al listado con éxito
header("Location: ../for-uno.php?status=update");
exit();
?>
