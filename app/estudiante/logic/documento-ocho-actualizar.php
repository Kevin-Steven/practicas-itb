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
$departamento = trim($_POST['departamento'] ?? '');
$semanas = $_POST['semana'] ?? [];
$horas_realizadas = $_POST['horas_realizadas'] ?? [];
$actividades_realizadas = $_POST['actividades_realizadas'] ?? [];

// 3. Validación básica
if (empty($departamento)) {
    header("Location: ../for-ocho.php?status=missing_data");
    exit();
}

// 4. Verificar si existe el documento ocho para el usuario actual
$sql_check = "SELECT id, departamento FROM documento_ocho WHERE usuario_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $usuario_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    // ✅ Si no existe, lo creamos, NO SEGUIR MÁS
    $sql_insert_doc = "INSERT INTO documento_ocho (usuario_id, departamento, estado) VALUES (?, ?, 'Pendiente')";
    $stmt_insert_doc = $conn->prepare($sql_insert_doc);
    $stmt_insert_doc->bind_param("is", $usuario_id, $departamento);
    $stmt_insert_doc->execute();
    $documento_ocho_id = $stmt_insert_doc->insert_id;
    $stmt_insert_doc->close();

    // ✅ Insertamos actividades si las hay
    if (!empty($semanas)) {
        $sql_insert_actividad = "INSERT INTO informe_actividades (documento_ocho_id, semanas_fecha, horas_realizadas, actividades_realizadas)
                                 VALUES (?, ?, ?, ?)";

        $stmt_insert_actividad = $conn->prepare($sql_insert_actividad);

        for ($i = 0; $i < count($semanas); $i++) {
            $semana = trim($semanas[$i]);
            $horas = trim($horas_realizadas[$i]);
            $actividad = trim($actividades_realizadas[$i]);

            if (empty($semana) || empty($horas) || empty($actividad)) {
                continue;
            }

            $stmt_insert_actividad->bind_param("isss", $documento_ocho_id, $semana, $horas, $actividad);
            $stmt_insert_actividad->execute();
        }

        $stmt_insert_actividad->close();
    }

    // ✅ Después de crear, redirigir. Sin status si quieres
    header("Location: ../for-ocho.php");
    exit();
}

// 5. Si el documento existe, revisamos si hubo cambios
$documento_ocho = $result_check->fetch_assoc();
$documento_ocho_id = $documento_ocho['id'];

$hubo_cambios = false;

// 6. Comparar si el departamento cambió
if ($departamento !== $documento_ocho['departamento']) {
    $hubo_cambios = true;
}

// 7. Obtener las actividades actuales
$sql_actividades_actuales = "SELECT semanas_fecha, horas_realizadas, actividades_realizadas 
                             FROM informe_actividades 
                             WHERE documento_ocho_id = ?
                             ORDER BY id ASC";

$stmt_actividades_actuales = $conn->prepare($sql_actividades_actuales);
$stmt_actividades_actuales->bind_param("i", $documento_ocho_id);
$stmt_actividades_actuales->execute();
$result_actividades_actuales = $stmt_actividades_actuales->get_result();

$actividades_actuales = [];
while ($row = $result_actividades_actuales->fetch_assoc()) {
    $actividades_actuales[] = $row;
}

$stmt_actividades_actuales->close();

// 8. Armar el array de actividades nuevas
$actividades_enviadas = [];
for ($i = 0; $i < count($semanas); $i++) {
    $semana = trim($semanas[$i]);
    $horas = trim($horas_realizadas[$i]);
    $actividad = trim($actividades_realizadas[$i]);

    if (empty($semana) || empty($horas) || empty($actividad)) {
        continue;
    }

    $actividades_enviadas[] = [
        'semanas_fecha' => $semana,
        'horas_realizadas' => $horas,
        'actividades_realizadas' => $actividad
    ];
}

// 9. Comparar actividades actuales con las enviadas
if (count($actividades_actuales) !== count($actividades_enviadas)) {
    $hubo_cambios = true;
} else {
    for ($i = 0; $i < count($actividades_actuales); $i++) {
        $actual = $actividades_actuales[$i];
        $nueva = $actividades_enviadas[$i];

        if (
            $actual['semanas_fecha'] !== $nueva['semanas_fecha'] ||
            $actual['horas_realizadas'] !== $nueva['horas_realizadas'] ||
            $actual['actividades_realizadas'] !== $nueva['actividades_realizadas']
        ) {
            $hubo_cambios = true;
            break;
        }
    }
}

// 10. Si no hubo cambios, redirige sin status
if (!$hubo_cambios) {
    header("Location: ../for-ocho.php");
    exit();
}

// 11. Si hubo cambios, actualizamos
$conn->begin_transaction();

try {
    // 11.1 Actualizar el departamento y poner estado pendiente
    $sql_update_doc = "UPDATE documento_ocho SET departamento = ?, estado = 'Pendiente' WHERE id = ?";
    $stmt_update_doc = $conn->prepare($sql_update_doc);
    $stmt_update_doc->bind_param("si", $departamento, $documento_ocho_id);
    $stmt_update_doc->execute();
    $stmt_update_doc->close();

    // 11.2 Eliminar las actividades anteriores
    $sql_delete_actividades = "DELETE FROM informe_actividades WHERE documento_ocho_id = ?";
    $stmt_delete_actividades = $conn->prepare($sql_delete_actividades);
    $stmt_delete_actividades->bind_param("i", $documento_ocho_id);
    $stmt_delete_actividades->execute();
    $stmt_delete_actividades->close();

    // 11.3 Insertar nuevas actividades
    $sql_insert_actividad = "INSERT INTO informe_actividades (documento_ocho_id, semanas_fecha, horas_realizadas, actividades_realizadas)
                             VALUES (?, ?, ?, ?)";

    $stmt_insert_actividad = $conn->prepare($sql_insert_actividad);

    foreach ($actividades_enviadas as $actividad) {
        $stmt_insert_actividad->bind_param(
            "isss",
            $documento_ocho_id,
            $actividad['semanas_fecha'],
            $actividad['horas_realizadas'],
            $actividad['actividades_realizadas']
        );
        $stmt_insert_actividad->execute();
    }

    $stmt_insert_actividad->close();

    $conn->commit();

    header("Location: ../for-ocho.php?status=updated");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../for-ocho.php?status=db_error");
    exit();
}

$conn->close();
