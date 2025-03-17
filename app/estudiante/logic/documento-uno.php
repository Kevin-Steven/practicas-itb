<?php
session_start();
require '../../config/config.php';

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['usuario_id'])) {
        die("Error: Usuario no autenticado.");
    }
    $usuario_id = $_SESSION['usuario_id'];

    // Recibir y validar los datos académicos
    $paralelo = filter_input(INPUT_POST, 'paralelo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $promedio = filter_input(INPUT_POST, 'promedio', FILTER_VALIDATE_FLOAT);

    if (empty($paralelo) || $promedio === false) {
        die("Error: Los campos de datos académicos son obligatorios y el promedio debe ser un número válido.");
    }

    // Insertar en la tabla documento_uno
    $sql_documento = "INSERT INTO documento_uno (usuario_id, paralelo, promedio_notas) VALUES (?, ?, ?)";
    $stmt_documento = $conn->prepare($sql_documento);
    $stmt_documento->bind_param("isd", $usuario_id, $paralelo, $promedio);

    if ($stmt_documento->execute()) {
        $documento_uno_id = $stmt_documento->insert_id;

        // Verificar si existen datos de experiencia laboral
        if (!empty($_POST['lugar_laborado']) && is_array($_POST['lugar_laborado'])) {
            $lugares_laborados = $_POST['lugar_laborado'];
            $periodos_tiempo = $_POST['periodo_tiempo'];
            $funciones_realizadas_array = $_POST['funciones_realizadas'];

            $sql_experiencia = "INSERT INTO experiencia_laboral (documento_uno_id, lugar_laborado, periodo_tiempo_meses, funciones_realizadas) VALUES (?, ?, ?, ?)";
            $stmt_experiencia = $conn->prepare($sql_experiencia);

            for ($i = 0; $i < count($lugares_laborados); $i++) {
                // Sanear y extraer solo los números del periodo
                $lugar = filter_var($lugares_laborados[$i], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $periodo = filter_var($periodos_tiempo[$i], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $funciones = filter_var($funciones_realizadas_array[$i], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                if (empty($lugar) || empty($periodo) || empty($funciones)) {
                    echo "Error en la entrada " . ($i + 1) . ": Datos inválidos.<br>";
                    continue;
                }

                // Insertar experiencia laboral
                $stmt_experiencia->bind_param("isss", $documento_uno_id, $lugar, $periodo, $funciones);
                if (!$stmt_experiencia->execute()) {
                    echo "Error al guardar la experiencia laboral: " . $stmt_experiencia->error . "<br>";
                }
            }
        }

        header("Location: ../for-uno.php?status=success");
        exit();

    } else {
        echo "Error al guardar los datos académicos: " . $stmt_documento->error;
        header("Location: ../for-uno.php?status=error");
    }

    $stmt_documento->close();
}

$conn->close();
?>
