<?php
session_start();
require '../../config/config.php';

// 1. Validación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// 2. Recibir los datos del formulario (nuevos campos)
$semana_inicio = $_POST['semana_inicio'] ?? [];
$semana_fin = $_POST['semana_fin'] ?? [];
$horas_realizadas = $_POST['horas_realizadas'] ?? [];
$actividades_realizadas = $_POST['actividades_realizadas'] ?? [];

// Validación básica
if (
    empty($semana_inicio) || !is_array($semana_inicio) ||
    empty($semana_fin) || !is_array($semana_fin) ||
    empty($horas_realizadas) || !is_array($horas_realizadas) ||
    empty($actividades_realizadas) || !is_array($actividades_realizadas)
) {
    header("Location: ../for-ocho.php?status=missing_data");
    exit();
}

// 4. Verificar si existe el documento ocho para el usuario actual
$sql_check = "SELECT id FROM documento_ocho WHERE usuario_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $usuario_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    // ✅ Si no existe el documento, se crea uno nuevo
    $sql_insert_doc = "INSERT INTO documento_ocho (usuario_id, estado) VALUES (?, 'Pendiente')";
    $stmt_insert_doc = $conn->prepare($sql_insert_doc);
    $stmt_insert_doc->bind_param("i", $usuario_id);
    $stmt_insert_doc->execute();
    $documento_ocho_id = $stmt_insert_doc->insert_id;
    $stmt_insert_doc->close();

    // ✅ Insertamos actividades si las hay
    if (!empty($semana_inicio)) {
        $sql_insert_actividad = "INSERT INTO informe_actividades (documento_ocho_id, semana_inicio, semana_fin, horas_realizadas, actividades_realizadas)
                                 VALUES (?, ?, ?, ?, ?)";

        $stmt_insert_actividad = $conn->prepare($sql_insert_actividad);

        for ($i = 0; $i < count($semana_inicio); $i++) {
            $inicio = trim($semana_inicio[$i]);
            $fin = trim($semana_fin[$i]);
            $horas = trim($horas_realizadas[$i]);
            $actividad = trim($actividades_realizadas[$i]);

            if (empty($inicio) || empty($fin) || empty($horas) || empty($actividad)) {
                continue;
            }

            $stmt_insert_actividad->bind_param("issss", $documento_ocho_id, $inicio, $fin, $horas, $actividad);
            $stmt_insert_actividad->execute();
        }

        $stmt_insert_actividad->close();
    }

    header("Location: ../for-ocho.php?status=created");
    exit();
}

// 5. Si el documento existe, revisamos si hubo cambios
$documento_ocho = $result_check->fetch_assoc();
$documento_ocho_id = $documento_ocho['id'];

$hubo_cambios = false;

// 7. Obtener las actividades actuales
$sql_actividades_actuales = "SELECT semana_inicio, semana_fin, horas_realizadas, actividades_realizadas 
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
for ($i = 0; $i < count($semana_inicio); $i++) {
    $inicio = trim($semana_inicio[$i]);
    $fin = trim($semana_fin[$i]);
    $horas = trim($horas_realizadas[$i]);
    $actividad = trim($actividades_realizadas[$i]);

    if (empty($inicio) || empty($fin) || empty($horas) || empty($actividad)) {
        continue;
    }

    $actividades_enviadas[] = [
        'semana_inicio' => $inicio,
        'semana_fin' => $fin,
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
            $actual['semana_inicio'] !== $nueva['semana_inicio'] ||
            $actual['semana_fin'] !== $nueva['semana_fin'] ||
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
    // 11.1 Actualizar el estado a Pendiente
    $sql_update_doc = "UPDATE documento_ocho SET estado = 'Pendiente' WHERE id = ?";
    $stmt_update_doc = $conn->prepare($sql_update_doc);
    $stmt_update_doc->bind_param("i", $documento_ocho_id);
    $stmt_update_doc->execute();
    $stmt_update_doc->close();

    // 11.2 Eliminar las actividades anteriores
    $sql_delete_actividades = "DELETE FROM informe_actividades WHERE documento_ocho_id = ?";
    $stmt_delete_actividades = $conn->prepare($sql_delete_actividades);
    $stmt_delete_actividades->bind_param("i", $documento_ocho_id);
    $stmt_delete_actividades->execute();
    $stmt_delete_actividades->close();

    // 11.3 Insertar nuevas actividades
    $sql_insert_actividad = "INSERT INTO informe_actividades (documento_ocho_id, semana_inicio, semana_fin, horas_realizadas, actividades_realizadas)
                             VALUES (?, ?, ?, ?, ?)";

    $stmt_insert_actividad = $conn->prepare($sql_insert_actividad);

    foreach ($actividades_enviadas as $actividad) {
        $stmt_insert_actividad->bind_param(
            "issss",
            $documento_ocho_id,
            $actividad['semana_inicio'],
            $actividad['semana_fin'],
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
    error_log("Error: " . $e->getMessage());
    header("Location: ../for-ocho.php?status=db_error");
    exit();
}

$conn->close();
?>
