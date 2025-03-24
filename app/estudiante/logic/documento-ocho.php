<?php
session_start();
require '../../config/config.php';

// ✅ Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ✅ Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ✅ Validación de sesión
    if (!isset($_SESSION['usuario_id'])) {
        die("Error: Usuario no autenticado.");
    }

    $usuario_id = intval($_SESSION['usuario_id']);

    // ✅ Capturar datos del formulario
    $semana_inicio = $_POST['semana_inicio'] ?? [];
    $semana_fin = $_POST['semana_fin'] ?? [];
    $horas_realizadas = $_POST['horas_realizadas'] ?? [];
    $actividades_realizadas = $_POST['actividades_realizadas'] ?? [];

    // ✅ Validaciones básicas
    if (
        empty($semana_inicio) ||
        empty($semana_fin) ||
        !is_array($semana_inicio) ||
        !is_array($semana_fin)
    ) {
        header("Location: ../for-ocho.php?status=missing_data");
        exit();
    }

    // ✅ Verificar si ya existe un documento_ocho para este usuario
    $sql_check = "SELECT id FROM documento_ocho WHERE usuario_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $usuario_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // ✅ Documento ya existe -> actualizar estado y reinsertar actividades
        $row = $result_check->fetch_assoc();
        $documento_ocho_id = $row['id'];

        // ✅ Iniciar transacción
        $conn->begin_transaction();

        try {
            // ✅ 1. Actualizar estado del documento
            $sql_update = "UPDATE documento_ocho SET estado = 'Pendiente' WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $documento_ocho_id);
            $stmt_update->execute();
            $stmt_update->close();

            // ✅ 2. Eliminar actividades anteriores
            $sql_delete = "DELETE FROM informe_actividades WHERE documento_ocho_id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $documento_ocho_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            // ✅ 3. Insertar nuevas actividades
            insertarActividades($conn, $documento_ocho_id, $semana_inicio, $semana_fin, $horas_realizadas, $actividades_realizadas);

            $conn->commit();
            header("Location: ../for-ocho.php?status=updated");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en la actualización: " . $e->getMessage());
            header("Location: ../for-ocho.php?status=db_error");
            exit();
        }

    } else {
        // ✅ No existe documento -> insertar nuevo documento y actividades
        $sql_insert_doc = "INSERT INTO documento_ocho (usuario_id) VALUES (?)";
        $stmt_insert_doc = $conn->prepare($sql_insert_doc);
        $stmt_insert_doc->bind_param("i", $usuario_id);

        if ($stmt_insert_doc->execute()) {
            $documento_ocho_id = $stmt_insert_doc->insert_id;

            try {
                insertarActividades($conn, $documento_ocho_id, $semana_inicio, $semana_fin, $horas_realizadas, $actividades_realizadas);
                header("Location: ../for-ocho.php?status=success");
                exit();

            } catch (Exception $e) {
                error_log("Error en la inserción: " . $e->getMessage());
                header("Location: ../for-ocho.php?status=db_error");
                exit();
            }

        } else {
            error_log("Error al insertar documento_ocho: " . $stmt_insert_doc->error);
            header("Location: ../for-ocho.php?status=db_error");
            exit();
        }
    }

    $stmt_check->close();
}

$conn->close();

/**
 * Función para insertar actividades en informe_actividades
 * @param mysqli $conn
 * @param int $documento_ocho_id
 * @param array $semana_inicio
 * @param array $semana_fin
 * @param array $horas_realizadas
 * @param array $actividades_realizadas
 */
function insertarActividades($conn, $documento_ocho_id, $semana_inicio, $semana_fin, $horas_realizadas, $actividades_realizadas)
{
    $sql_insert_actividad = "INSERT INTO informe_actividades (
        documento_ocho_id,
        semana_inicio,
        semana_fin,
        horas_realizadas,
        actividades_realizadas
    ) VALUES (?, ?, ?, ?, ?)";

    $stmt_insert_actividad = $conn->prepare($sql_insert_actividad);

    for ($i = 0; $i < count($semana_inicio); $i++) {

        $inicio = $semana_inicio[$i] ?? null;
        $fin = $semana_fin[$i] ?? null;
        $horas = trim($horas_realizadas[$i] ?? "NO APLICA");
        $actividad = trim($actividades_realizadas[$i] ?? "NO APLICA");

        if (!$inicio || !$fin) {
            continue; // Evita insertar registros sin fechas
        }

        $stmt_insert_actividad->bind_param("issss", $documento_ocho_id, $inicio, $fin, $horas, $actividad);
        $stmt_insert_actividad->execute();
    }

    $stmt_insert_actividad->close();
}
?>
