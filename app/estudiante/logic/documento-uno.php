<?php
session_start();
require '../../config/config.php';

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Forzar el charset
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['usuario_id'])) {
        die("Error: Usuario no autenticado.");
    }
    $usuario_id = $_SESSION['usuario_id'];

    // Recibir los datos académicos sin convertir caracteres especiales
    $paralelo = trim($conn->real_escape_string($_POST['paralelo']));
    $promedio = floatval($_POST['promedio']);

    if (empty($paralelo) || $promedio === false) {
        die("Error: Los campos de datos académicos son obligatorios y el promedio debe ser un número válido.");
    }

    // Insertar en documento_uno
    $sql_documento = "INSERT INTO documento_uno (usuario_id, paralelo, promedio_notas) VALUES (?, ?, ?)";
    $stmt_documento = $conn->prepare($sql_documento);
    $stmt_documento->bind_param("isd", $usuario_id, $paralelo, $promedio);

    if ($stmt_documento->execute()) {
        $documento_uno_id = $stmt_documento->insert_id;

        // Experiencia laboral
        if (!empty($_POST['lugar_laborado']) && is_array($_POST['lugar_laborado'])) {
            $lugares_laborados = $_POST['lugar_laborado'];
            $periodos_tiempo = $_POST['periodo_tiempo'];
            $funciones_realizadas_array = $_POST['funciones_realizadas'];

            $sql_experiencia = "INSERT INTO experiencia_laboral (documento_uno_id, lugar_laborado, periodo_tiempo_meses, funciones_realizadas) VALUES (?, ?, ?, ?)";
            $stmt_experiencia = $conn->prepare($sql_experiencia);

            for ($i = 0; $i < count($lugares_laborados); $i++) {
                $lugar = trim($conn->real_escape_string($lugares_laborados[$i]));
                $periodo = trim($conn->real_escape_string($periodos_tiempo[$i]));
                $funciones = trim($conn->real_escape_string($funciones_realizadas_array[$i]));

                if (empty($lugar) || empty($periodo) || empty($funciones)) {
                    continue;
                }

                $stmt_experiencia->bind_param("isss", $documento_uno_id, $lugar, $periodo, $funciones);
                $stmt_experiencia->execute();
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
