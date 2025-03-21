<?php
session_start();
require '../../config/config.php';

// Verificación de conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Verifica si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validación de sesión
    if (!isset($_SESSION['usuario_id'])) {
        die("Error: Usuario no autenticado.");
    }

    $usuario_id = intval($_SESSION['usuario_id']);
    $departamento = trim($_POST['departamento'] ?? '');

    $semanas = $_POST['semana'] ?? [];
    $horas_realizadas = $_POST['horas_realizadas'] ?? [];
    $actividades_realizadas = $_POST['actividades_realizadas'] ?? [];

    // ✅ Validaciones básicas
    if (empty($departamento)) {
        header("Location: ../for-ocho.php?status=missing_departamento");
        exit();
    }

    if (empty($semanas) || !is_array($semanas)) {
        header("Location: ../for-ocho.php?status=missing_actividades");
        exit();
    }

    // ✅ Verificamos si ya existe un documento_ocho para el usuario
    $sql_check = "SELECT id FROM documento_ocho WHERE usuario_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $usuario_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Si existe, actualizamos el documento y eliminamos las actividades anteriores
        $row = $result_check->fetch_assoc();
        $documento_ocho_id = $row['id'];

        // ✅ Comienza la transacción
        $conn->begin_transaction();

        try {
            // Actualizar el departamento y estado a Pendiente
            $sql_update = "UPDATE documento_ocho SET departamento = ?, estado = 'Pendiente' WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $departamento, $documento_ocho_id);
            $stmt_update->execute();
            $stmt_update->close();

            // Eliminar las actividades anteriores
            $sql_delete = "DELETE FROM informe_actividades WHERE documento_ocho_id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $documento_ocho_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Insertar las nuevas actividades
            insertarActividades($conn, $documento_ocho_id, $semanas, $horas_realizadas, $actividades_realizadas);

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
        // Si no existe, insertamos un nuevo documento
        $sql_insert_doc = "INSERT INTO documento_ocho (usuario_id, departamento) VALUES (?, ?)";
        $stmt_insert_doc = $conn->prepare($sql_insert_doc);
        $stmt_insert_doc->bind_param("is", $usuario_id, $departamento);

        if ($stmt_insert_doc->execute()) {
            $documento_ocho_id = $stmt_insert_doc->insert_id;

            try {
                insertarActividades($conn, $documento_ocho_id, $semanas, $horas_realizadas, $actividades_realizadas);
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


// ✅ Función para insertar actividades
function insertarActividades($conn, $documento_ocho_id, $semanas, $horas_realizadas, $actividades_realizadas)
{
    $sql_insert_actividad = "INSERT INTO informe_actividades (documento_ocho_id, semanas_fecha, horas_realizadas, actividades_realizadas) VALUES (?, ?, ?, ?)";
    $stmt_insert_actividad = $conn->prepare($sql_insert_actividad);

    for ($i = 0; $i < count($semanas); $i++) {

        $semana = trim($semanas[$i]);
        $horas = trim($horas_realizadas[$i]);
        $actividad = trim($actividades_realizadas[$i]);

        // Valida y asigna "NO APLICA" si es necesario
        $semana = !empty($semana) ? $semana : "NO APLICA";
        $horas = !empty($horas) ? $horas : "NO APLICA";
        $actividad = !empty($actividad) ? $actividad : "NO APLICA";

        $stmt_insert_actividad->bind_param("isss", $documento_ocho_id, $semana, $horas, $actividad);
        $stmt_insert_actividad->execute();
    }

    $stmt_insert_actividad->close();
}

?>
