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

$sql_doc_diez = "SELECT 
       dd.id,
       dd.opcion_uno_puntaje,
       dd.opcion_dos_puntaje,
       dd.opcion_tres_puntaje,
       dd.opcion_cuatro_puntaje,
       dd.opcion_cinco_puntaje,
       dd.opcion_seis_puntaje,
       dd.opcion_siete_puntaje,
       dd.opcion_ocho_puntaje,
       dd.opcion_nueve_puntaje,
       dd.opcion_diez_puntaje,
       dd.motivo_rechazo,
       dd.estado
FROM documento_diez dd
WHERE dd.usuario_id = ?
ORDER BY dd.id DESC
LIMIT 1";

$stmt_doc_diez = $conn->prepare($sql_doc_diez);
$stmt_doc_diez->bind_param("i", $usuario_id);
$stmt_doc_diez->execute();
$result_doc_diez = $stmt_doc_diez->get_result();

$estado = null;

if ($row = $result_doc_diez->fetch_assoc()) {
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
}

$stmt_doc_diez->close();

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evaluación Final del Estudiante</title>
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
            <h1 class="mb-2 text-center fw-bold">Evaluación Final del Estudiante en el Entorno Laboral Real Facultad de Ciencias Empresariales y Sistemas.</h1>

            <?php if (empty($estado)): ?>

                <form action="../estudiante/logic/documento-diez.php" class="enviar-tema" method="POST" enctype="multipart/form-data">

                    <p class="text-center">
                        Puntuación del 1 al 5 según el grado de resultado de aprendizaje obtenido el estudiante en su desempeño laboral descripción puntual de las actividades efectuadas durante sus prácticas preprofesionales en la entidad receptora:
                    </p>
                    <p class="text-center">
                        <strong>5</strong> - Eficiente. &nbsp;&nbsp;
                        <strong>4</strong> - Alta. &nbsp;&nbsp;
                        <strong>3</strong> - Moderado. &nbsp;&nbsp;
                        <strong>2</strong> - Regular. &nbsp;&nbsp;
                        <strong>1</strong> - Deficiente.
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
                                    <td rowspan="3" class="fw-bold align-middle">Conocimientos</td>
                                    <td class="text-start">Diseñar e implementar algoritmos utilizando las técnicas de programación lineal, estructurada, procedimental y funcional.</td>
                                    <td><input type="radio" name="pregunta1" value="5" required></td>
                                    <td><input type="radio" name="pregunta1" value="4"></td>
                                    <td><input type="radio" name="pregunta1" value="3"></td>
                                    <td><input type="radio" name="pregunta1" value="2"></td>
                                    <td><input type="radio" name="pregunta1" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Utilizar las estructuras de datos básicas y compuestas, así como estáticas y dinámicas para la entrada y salida de datos, en la implementación de algoritmos que les den solución a problemas de requerimientos de software</td>
                                    <td><input type="radio" name="pregunta2" value="5" required></td>
                                    <td><input type="radio" name="pregunta2" value="4"></td>
                                    <td><input type="radio" name="pregunta2" value="3"></td>
                                    <td><input type="radio" name="pregunta2" value="2"></td>
                                    <td><input type="radio" name="pregunta2" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Brindar soporte técnico y de mantenimiento a sistemas de hardware de cómputo.</td>
                                    <td><input type="radio" name="pregunta3" value="5" required></td>
                                    <td><input type="radio" name="pregunta3" value="4"></td>
                                    <td><input type="radio" name="pregunta3" value="3"></td>
                                    <td><input type="radio" name="pregunta3" value="2"></td>
                                    <td><input type="radio" name="pregunta3" value="1"></td>
                                </tr>

                                <!-- INTEGRACIÓN AL AMBIENTE LABORAL -->
                                <tr>
                                    <td rowspan="6" class="fw-bold align-middle">Habilidades</td>
                                    <td class="text-start">Diseñar e implementar bases de datos mediante el Modelo-Entidad-Relación</td>
                                    <td><input type="radio" name="pregunta5" value="5" required></td>
                                    <td><input type="radio" name="pregunta5" value="4"></td>
                                    <td><input type="radio" name="pregunta5" value="3"></td>
                                    <td><input type="radio" name="pregunta5" value="2"></td>
                                    <td><input type="radio" name="pregunta5" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Aplicar las formas normales en el diseño de bases de datos mediante el Modelo-Entidad-Relación. </td>
                                    <td><input type="radio" name="pregunta6" value="5" required></td>
                                    <td><input type="radio" name="pregunta6" value="4"></td>
                                    <td><input type="radio" name="pregunta6" value="3"></td>
                                    <td><input type="radio" name="pregunta6" value="2"></td>
                                    <td><input type="radio" name="pregunta6" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Optimizar el diseño de bases de datos implementadas.</td>
                                    <td><input type="radio" name="pregunta7" value="5" required></td>
                                    <td><input type="radio" name="pregunta7" value="4"></td>
                                    <td><input type="radio" name="pregunta7" value="3"></td>
                                    <td><input type="radio" name="pregunta7" value="2"></td>
                                    <td><input type="radio" name="pregunta7" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Identificar componentes de hardware de redes LAN.</td>
                                    <td><input type="radio" name="pregunta8" value="5" required></td>
                                    <td><input type="radio" name="pregunta8" value="4"></td>
                                    <td><input type="radio" name="pregunta8" value="3"></td>
                                    <td><input type="radio" name="pregunta8" value="2"></td>
                                    <td><input type="radio" name="pregunta8" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Optimizar el diseño de redes LAN.</td>
                                    <td><input type="radio" name="pregunta9" value="5" required></td>
                                    <td><input type="radio" name="pregunta9" value="4"></td>
                                    <td><input type="radio" name="pregunta9" value="3"></td>
                                    <td><input type="radio" name="pregunta9" value="2"></td>
                                    <td><input type="radio" name="pregunta9" value="1"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Implementar y monitorear servicios de redes LAN </td>
                                    <td><input type="radio" name="pregunta10" value="5" required></td>
                                    <td><input type="radio" name="pregunta10" value="4"></td>
                                    <td><input type="radio" name="pregunta10" value="3"></td>
                                    <td><input type="radio" name="pregunta10" value="2"></td>
                                    <td><input type="radio" name="pregunta10" value="1"></td>
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
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='for-diez-edit.php?id=<?php echo $id; ?>'">
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
                            <form action="../estudiante/pdf/software/doc-diez-pdf.php" method="GET" target="_blank">
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
        <?php
        $status = $_GET['status'];

        // Icono + Título según el estado
        if ($status === 'success') {
            $icon = "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
            $title = "Formulario enviado";
        } elseif ($status === 'deleted') {
            $icon = "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
            $title = "Documento Eliminado";
        } elseif ($status === 'update') {
            $icon = "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
            $title = "Documento Actualizado";
        } elseif ($status === 'missing_data') {
            $icon = "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
            $title = "Campos incompletos";
        } elseif ($status === 'db_error') {
            $icon = "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
            $title = "Error de Base de Datos";
        } else {
            $icon = "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
            $title = "Error";
        }

        // Mensaje del cuerpo del toast
        $message = match ($status) {
            'success' => "Evaluación final enviada correctamente.",
            'deleted' => "El documento se ha eliminado correctamente.",
            'update' => "El documento se ha actualizado correctamente.",
            'missing_data' => "Debes responder todas las preguntas antes de enviar.",
            'db_error' => "Hubo un error al guardar los datos en la base de datos.",
            default => "Ocurrió un error inesperado."
        };
        ?>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <?= $icon ?>
                    <strong class="me-auto"><?= $title ?></strong>
                    <small>Justo ahora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <?= $message ?>
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