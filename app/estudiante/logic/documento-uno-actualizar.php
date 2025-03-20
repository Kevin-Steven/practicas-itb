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
$promedio = floatval($_POST['promedio'] ?? 0);

$lugares = $_POST['lugar_laborado'] ?? [];
$periodos = $_POST['periodo_tiempo'] ?? [];
$funciones = $_POST['funciones_realizadas'] ?? [];

// 3. Validar los datos importantes
if (empty($documento_id) || $promedio <= 0) {
    header("Location: ../for-uno-edit.php?id=$documento_id&status=missing_data");
    exit();
}

if ($promedio < 0 || $promedio > 100) {
    header("Location: ../for-uno-edit.php?id=$documento_id&status=invalid_promedio");
    exit();
}

// 4. Verificar que el documento le pertenece al usuario actual
$sql_check = "SELECT id, promedio_notas FROM documento_uno WHERE id = ? AND usuario_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $documento_id, $usuario_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    header("Location: ../for-uno.php?status=not_found");
    exit();
}

$documento_actual = $result_check->fetch_assoc();
$stmt_check->close();

// 5. Comparar el promedio actual con el nuevo promedio
$hubo_cambios = false;

if (floatval($documento_actual['promedio_notas']) != $promedio) {
    $hubo_cambios = true;
}

// 6. Obtener las experiencias laborales actuales
$sql_exp_actual = "SELECT lugar_laborado, periodo_tiempo_meses, funciones_realizadas 
                   FROM experiencia_laboral 
                   WHERE documento_uno_id = ?
                   ORDER BY id ASC";
$stmt_exp_actual = $conn->prepare($sql_exp_actual);
$stmt_exp_actual->bind_param("i", $documento_id);
$stmt_exp_actual->execute();
$result_exp_actual = $stmt_exp_actual->get_result();

$experiencias_actuales = [];
while ($row = $result_exp_actual->fetch_assoc()) {
    $experiencias_actuales[] = $row;
}
$stmt_exp_actual->close();

// 7. Comparar experiencias laborales actuales con las enviadas
$experiencias_enviadas = [];
for ($i = 0; $i < count($lugares); $i++) {
    $lugar = trim($lugares[$i]);
    $periodo = trim($periodos[$i]);
    $funcion = trim($funciones[$i]);

    if (empty($lugar) || empty($periodo) || empty($funcion)) {
        continue;
    }

    $experiencias_enviadas[] = [
        'lugar_laborado' => $lugar,
        'periodo_tiempo_meses' => $periodo,
        'funciones_realizadas' => $funcion
    ];
}

// 8. Comparar si las experiencias son iguales o cambiaron
if (count($experiencias_actuales) !== count($experiencias_enviadas)) {
    $hubo_cambios = true;
} else {
    for ($i = 0; $i < count($experiencias_actuales); $i++) {
        $exp_actual = $experiencias_actuales[$i];
        $exp_nueva = $experiencias_enviadas[$i];

        if (
            $exp_actual['lugar_laborado'] !== $exp_nueva['lugar_laborado'] ||
            $exp_actual['periodo_tiempo_meses'] !== $exp_nueva['periodo_tiempo_meses'] ||
            $exp_actual['funciones_realizadas'] !== $exp_nueva['funciones_realizadas']
        ) {
            $hubo_cambios = true;
            break;
        }
    }
}

// 9. Si no hubo cambios, redirige sin estado
if (!$hubo_cambios) {
    header("Location: ../for-uno.php");
    exit();
}

// 10. Si hubo cambios, actualiza el promedio y experiencia laboral
// ✅ Comenzamos una transacción para asegurar consistencia
$conn->begin_transaction();

try {
    // 10.1 Actualizar el documento_uno
    $sql_update_doc = "UPDATE documento_uno 
                       SET promedio_notas = ?, estado = 'Pendiente' 
                       WHERE id = ?";
    $stmt_update_doc = $conn->prepare($sql_update_doc);
    $stmt_update_doc->bind_param("di", $promedio, $documento_id);
    $stmt_update_doc->execute();
    $stmt_update_doc->close();

    // 10.2 Eliminar experiencias anteriores
    $sql_delete_exp = "DELETE FROM experiencia_laboral WHERE documento_uno_id = ?";
    $stmt_delete_exp = $conn->prepare($sql_delete_exp);
    $stmt_delete_exp->bind_param("i", $documento_id);
    $stmt_delete_exp->execute();
    $stmt_delete_exp->close();

    // 10.3 Insertar nuevas experiencias
    $sql_insert_exp = "INSERT INTO experiencia_laboral 
        (documento_uno_id, lugar_laborado, periodo_tiempo_meses, funciones_realizadas) 
        VALUES (?, ?, ?, ?)";

    $stmt_insert_exp = $conn->prepare($sql_insert_exp);

    foreach ($experiencias_enviadas as $exp) {
        $stmt_insert_exp->bind_param(
            "isss",
            $documento_id,
            $exp['lugar_laborado'],
            $exp['periodo_tiempo_meses'],
            $exp['funciones_realizadas']
        );
        $stmt_insert_exp->execute();
    }

    $stmt_insert_exp->close();

    // 10.4 Commit si todo salió bien
    $conn->commit();

    header("Location: ../for-uno.php?status=updated");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../for-uno-edit.php?id=$documento_id&status=db_error");
    exit();
}

$conn->close();
?>
