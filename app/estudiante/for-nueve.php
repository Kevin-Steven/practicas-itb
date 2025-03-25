<?php
session_start();
require '../config/config.php';
require 'sidebar-estudiante.php';
require '../admin/sidebar-admin.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
$usuario_id = $_SESSION['usuario_id'];

if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

$sql_doc_nueve = "SELECT 
       dn.id,
       dn.opcion_uno_puntaje,
       dn.opcion_dos_puntaje,
       dn.opcion_tres_puntaje,
       dn.opcion_cuatro_puntaje,
       dn.opcion_cinco_puntaje,
       dn.opcion_seis_puntaje,
       dn.opcion_siete_puntaje,
       dn.opcion_ocho_puntaje,
       dn.opcion_nueve_puntaje,
       dn.opcion_diez_puntaje,
       dn.opcion_once_puntaje,
       dn.opcion_doce_puntaje,
       dn.opcion_trece_puntaje,
       dn.opcion_catorce_puntaje,
       dn.opcion_quince_puntaje,
       dn.motivo_rechazo,
       dn.estado
FROM documento_nueve dn
WHERE dn.usuario_id = ?
ORDER BY dn.id DESC
LIMIT 1";

$stmt_doc_nueve = $conn->prepare($sql_doc_nueve);
$stmt_doc_nueve->bind_param("i", $usuario_id);
$stmt_doc_nueve->execute();
$result_doc_nueve = $stmt_doc_nueve->get_result();

$estado = null;

if ($row = $result_doc_nueve->fetch_assoc()) {
    $id = $row['id'];
    $estado = $row['estado'] ?? null;
    $motivo_rechazo = $row['motivo_rechazo'] ?? null;
    // Puntajes de cada pregunta
    $opcion_uno_puntaje = $row['opcion_uno_puntaje'];
    $opcion_dos_puntaje = $row['opcion_dos_puntaje'];
    $opcion_tres_puntaje = $row['opcion_tres_puntaje'];
    $opcion_cuatro_puntaje = $row['opcion_cuatro_puntaje'];
    $opcion_cinco_puntaje = $row['opcion_cinco_puntaje'];
    $opcion_seis_puntaje = $row['opcion_seis_puntaje'];
    $opcion_siete_puntaje = $row['opcion_siete_puntaje'];
    $opcion_ocho_puntaje = $row['opcion_ocho_puntaje'];
    $opcion_nueve_puntaje = $row['opcion_nueve_puntaje'];
    $opcion_diez_puntaje = $row['opcion_diez_puntaje'];
    $opcion_once_puntaje = $row['opcion_once_puntaje'];
    $opcion_doce_puntaje = $row['opcion_doce_puntaje'];
    $opcion_trece_puntaje = $row['opcion_trece_puntaje'];
    $opcion_catorce_puntaje = $row['opcion_catorce_puntaje'];
    $opcion_quince_puntaje = $row['opcion_quince_puntaje'];
}

$stmt_doc_nueve->close();

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evaluación Conductual</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>

    <?php renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil); ?>
    <!-- Content -->
    <div class="content" id="content">
        <div class="container">
            <h1 class="mb-2 text-center fw-bold">Evaluación Conductual del Estudiante</h1>

            <?php if (empty($estado)): ?>
                <form action="../estudiante/logic/documento-nueve.php" class="enviar-tema" method="POST" enctype="multipart/form-data">

                    <p class="text-center">
                        Indique la evaluación que usted considere adecuada, basada en el desempeño del estudiante durante la Práctica Pre-profesional laboral, y teniendo en cuenta la siguiente escala:
                    </p>
                    <p class="text-center">
                        <strong>5</strong> - Siempre. &nbsp;&nbsp;
                        <strong>4</strong> - Casi siempre. &nbsp;&nbsp;
                        <strong>3</strong> - Ocasionalmente. &nbsp;&nbsp;
                        <strong>2</strong> - Casi nunca. &nbsp;&nbsp;
                        <strong>1</strong> - Nunca.
                    </p>


                    <!-- Campo oculto -->
                    <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">

                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" colspan="2">INDICADORES:</th>
                                    <th>5</th>
                                    <th>4</th>
                                    <th>3</th>
                                    <th>2</th>
                                    <th>1</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- DISCIPLINA -->
                                <tr>
                                    <td rowspan="4" class="fw-bold align-middle">Disciplina</td>
                                    <td class="text-start">Asiste puntualmente a su práctica.</td>
                                    <td><input type="radio" name="pregunta1" value="5" required></td>
                                    <td><input type="radio" name="pregunta1" value="4"></td>
                                    <td><input type="radio" name="pregunta1" value="3"></td>
                                    <td><input type="radio" name="pregunta1" value="2"></td>
                                    <td><input type="radio" name="pregunta1" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Se presenta con adecuado porte y respeto en el área laboral asignada.</td>
                                    <td><input type="radio" name="pregunta2" value="5" required></td>
                                    <td><input type="radio" name="pregunta2" value="4"></td>
                                    <td><input type="radio" name="pregunta2" value="3"></td>
                                    <td><input type="radio" name="pregunta2" value="2"></td>
                                    <td><input type="radio" name="pregunta2" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Manifiesta una actitud de servicio, cooperación y trabajo en equipo.</td>
                                    <td><input type="radio" name="pregunta3" value="5" required></td>
                                    <td><input type="radio" name="pregunta3" value="4"></td>
                                    <td><input type="radio" name="pregunta3" value="3"></td>
                                    <td><input type="radio" name="pregunta3" value="2"></td>
                                    <td><input type="radio" name="pregunta3" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Actúa siguiendo la ética profesional y normas de principios morales.</td>
                                    <td><input type="radio" name="pregunta4" value="5" required></td>
                                    <td><input type="radio" name="pregunta4" value="4"></td>
                                    <td><input type="radio" name="pregunta4" value="3"></td>
                                    <td><input type="radio" name="pregunta4" value="2"></td>
                                    <td><input type="radio" name="pregunta4" value="1"></td>
                                </tr>

                                <!-- INTEGRACIÓN AL AMBIENTE LABORAL -->
                                <tr>
                                    <td rowspan="4" class="fw-bold align-middle">Integración al<br> ambiente laboral</td>
                                    <td class="text-start">Cumple con las Normas, Políticas, procedimientos y cultura organizacional.</td>
                                    <td><input type="radio" name="pregunta5" value="5" required></td>
                                    <td><input type="radio" name="pregunta5" value="4"></td>
                                    <td><input type="radio" name="pregunta5" value="3"></td>
                                    <td><input type="radio" name="pregunta5" value="2"></td>
                                    <td><input type="radio" name="pregunta5" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Establece una comunicación profesional efectiva y asertiva en el área asignada.</td>
                                    <td><input type="radio" name="pregunta6" value="5" required></td>
                                    <td><input type="radio" name="pregunta6" value="4"></td>
                                    <td><input type="radio" name="pregunta6" value="3"></td>
                                    <td><input type="radio" name="pregunta6" value="2"></td>
                                    <td><input type="radio" name="pregunta6" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Trabaja en iniciativa y soluciones integrales acorde a su asignación de práctica.</td>
                                    <td><input type="radio" name="pregunta7" value="5" required></td>
                                    <td><input type="radio" name="pregunta7" value="4"></td>
                                    <td><input type="radio" name="pregunta7" value="3"></td>
                                    <td><input type="radio" name="pregunta7" value="2"></td>
                                    <td><input type="radio" name="pregunta7" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Demuestra capacidad de adaptación y desenvolvimiento al área asignada.</td>
                                    <td><input type="radio" name="pregunta8" value="5" required></td>
                                    <td><input type="radio" name="pregunta8" value="4"></td>
                                    <td><input type="radio" name="pregunta8" value="3"></td>
                                    <td><input type="radio" name="pregunta8" value="2"></td>
                                    <td><input type="radio" name="pregunta8" value="1"></td>
                                </tr>

                                <!-- CONOCIMIENTOS Y HABILIDADES PROFESIONALES -->
                                <tr>
                                    <td rowspan="7" class="fw-bold align-middle">Conocimientos y<br> habilidades profesionales</td>
                                    <td class="text-start">Aplica adecuadamente los conocimientos teóricos y prácticos del perfil profesional.</td>
                                    <td><input type="radio" name="pregunta9" value="5" required></td>
                                    <td><input type="radio" name="pregunta9" value="4"></td>
                                    <td><input type="radio" name="pregunta9" value="3"></td>
                                    <td><input type="radio" name="pregunta9" value="2"></td>
                                    <td><input type="radio" name="pregunta9" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Demuestra adecuadamente las destrezas y habilidades acordes al perfil profesional.</td>
                                    <td><input type="radio" name="pregunta10" value="5" required></td>
                                    <td><input type="radio" name="pregunta10" value="4"></td>
                                    <td><input type="radio" name="pregunta10" value="3"></td>
                                    <td><input type="radio" name="pregunta10" value="2"></td>
                                    <td><input type="radio" name="pregunta10" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Genera soluciones y propuestas halladas en el área de asignación de práctica.</td>
                                    <td><input type="radio" name="pregunta11" value="5" required></td>
                                    <td><input type="radio" name="pregunta11" value="4"></td>
                                    <td><input type="radio" name="pregunta11" value="3"></td>
                                    <td><input type="radio" name="pregunta11" value="2"></td>
                                    <td><input type="radio" name="pregunta11" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Comunica asertivamente situaciones para la mejora continua del área asignada.</td>
                                    <td><input type="radio" name="pregunta12" value="5" required></td>
                                    <td><input type="radio" name="pregunta12" value="4"></td>
                                    <td><input type="radio" name="pregunta12" value="3"></td>
                                    <td><input type="radio" name="pregunta12" value="2"></td>
                                    <td><input type="radio" name="pregunta12" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Demuestra capacidad resolutiva a casos reales del área asignada.</td>
                                    <td><input type="radio" name="pregunta13" value="5" required></td>
                                    <td><input type="radio" name="pregunta13" value="4"></td>
                                    <td><input type="radio" name="pregunta13" value="3"></td>
                                    <td><input type="radio" name="pregunta13" value="2"></td>
                                    <td><input type="radio" name="pregunta13" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Demuestra proactividad en adquirir nuevos conocimientos en el área asignada de prácticas.</td>
                                    <td><input type="radio" name="pregunta14" value="5" required></td>
                                    <td><input type="radio" name="pregunta14" value="4"></td>
                                    <td><input type="radio" name="pregunta14" value="3"></td>
                                    <td><input type="radio" name="pregunta14" value="2"></td>
                                    <td><input type="radio" name="pregunta14" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Aporta destreza académica en reuniones de trabajo del área asignada.</td>
                                    <td><input type="radio" name="pregunta15" value="5" required></td>
                                    <td><input type="radio" name="pregunta15" value="4"></td>
                                    <td><input type="radio" name="pregunta15" value="3"></td>
                                    <td><input type="radio" name="pregunta15" value="2"></td>
                                    <td><input type="radio" name="pregunta15" value="1"></td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- Botón de Enviar -->
                    <div class="container-fluid text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                        <button type="submit" class="btn btn-primary">Enviar Datos</button>
                    </div>

                </form>
            <?php else: ?>
                <h3 class="text-center mt-2 mb-3">Estado del Formulario</h3>

                <div class="table-responsive">
                    <table class="table table-bordered shadow-lg text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Pregunta 1</th>
                                <th>Pregunta 2</th>
                                <th>Pregunta 3</th>
                                <th>Pregunta 4</th>
                                <th>Pregunta 5</th>
                                <th>Pregunta 6</th>
                                <th>Pregunta 7</th>
                                <th>Pregunta 8</th>
                                <th>Pregunta 9</th>
                                <th>Pregunta 10</th>
                                <th>Pregunta 11</th>
                                <th>Pregunta 12</th>
                                <th>Pregunta 13</th>
                                <th>Pregunta 14</th>
                                <th>Pregunta 15</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars($opcion_uno_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_dos_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_tres_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_cuatro_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_cinco_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_seis_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_siete_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_ocho_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_nueve_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_diez_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_once_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_doce_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_trece_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_catorce_puntaje ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_quince_puntaje ?? '-'); ?></td>

                                <!-- Estado con badge dinámico -->
                                <td class="text-center">
                                    <?php
                                    $badgeClass = '';

                                    if ($estado === 'Pendiente') {
                                        $badgeClass = 'badge bg-warning text-dark';
                                    } elseif ($estado === 'Corregir') {
                                        $badgeClass = 'badge bg-danger';
                                    } elseif ($estado === 'Aprobado') {
                                        $badgeClass = 'badge bg-success';
                                    } else {
                                        $badgeClass = 'badge bg-secondary';
                                    }
                                    ?>
                                    <span class="<?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($estado); ?>
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='for-nueve-edit.php?id=<?php echo $id; ?>'">
                                            <i class='bx bx-edit-alt'></i>
                                        </button>

                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalImprimir<?php echo $id; ?>">
                                            <i class='bx bxs-file-pdf'></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ✅ Modal fuera de la tabla -->
                <div class="modal fade" id="modalImprimir<?php echo $id; ?>" tabindex="-1" aria-labelledby="modalImprimirLabel<?php echo $id; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="../estudiante/pdf/software/doc-nueve-pdf.php" method="GET" target="_blank">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalImprimirLabel<?php echo $id; ?>">¿Desea generar el documento?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Se generará un documento en formato PDF.</p>
                                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Toast -->
    <?php if (isset($_GET['status'])): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <?php
                    // Estados que son exitosos
                    $success_status = ['success', 'updated', 'deleted'];

                    if (in_array($_GET['status'], $success_status)) : ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                        <strong class="me-auto">
                            <?php
                            switch ($_GET['status']) {
                                case 'success':
                                    echo 'Evaluación Registrada';
                                    break;
                                case 'updated':
                                    echo 'Evaluación Actualizada';
                                    break;
                                case 'deleted':
                                    echo 'Evaluación Eliminada';
                                    break;
                            }
                            ?>
                        </strong>
                    <?php else: ?>
                        <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                        <strong class="me-auto">Error</strong>
                    <?php endif; ?>
                    <small>Justo ahora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>

                <div class="toast-body">
                    <?php
                    switch ($_GET['status']) {
                        // Éxitos
                        case 'success':
                            echo "La evaluación conductual se ha registrado correctamente.";
                            break;
                        case 'updated':
                            echo "La evaluación conductual se ha actualizado correctamente.";
                            break;
                        case 'deleted':
                            echo "La evaluación conductual se ha eliminado correctamente.";
                            break;

                        // Errores
                        case 'missing_data':
                            echo "Debes responder todas las preguntas para enviar la evaluación.";
                            break;
                        case 'invalid_value':
                            echo "Uno de los valores seleccionados no es válido. Revisa tus respuestas.";
                            break;
                        case 'invalid_user':
                            echo "Usuario no válido. Por favor, inicia sesión nuevamente.";
                            break;
                        case 'prepare_error':
                            echo "Error en la preparación de la consulta SQL. Intenta nuevamente.";
                            break;
                        case 'db_error':
                            echo "Error al guardar los datos en la base de datos.";
                            break;
                        default:
                            echo "Ocurrió un error inesperado. Por favor, inténtalo más tarde.";
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>