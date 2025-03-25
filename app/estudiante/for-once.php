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

$sql_doc_once = "SELECT 
       do.id,
       do.opcion_uno,
       do.opcion_dos,
       do.opcion_tres,
       do.opcion_cuatro,
       do.opcion_cinco,
       do.opcion_seis,
       do.motivo_rechazo,
       do.estado
FROM documento_once do
WHERE do.usuario_id = ?
ORDER BY do.id DESC
LIMIT 1";

$stmt_doc_once = $conn->prepare($sql_doc_once);
$stmt_doc_once->bind_param("i", $usuario_id);
$stmt_doc_once->execute();
$result_doc_once = $stmt_doc_once->get_result();

$estado = null;

if ($row = $result_doc_once->fetch_assoc()) {
    $id = $row['id'];
    $estado = $row['estado'] ?? null;
    $motivo_rechazo = $row['motivo_rechazo'] ?? null;
    // Puntajes de cada pregunta
    $opcion_uno = $row['opcion_uno'];
    $opcion_dos = $row['opcion_dos'];
    $opcion_tres = $row['opcion_tres'];
    $opcion_cuatro = $row['opcion_cuatro'];
    $opcion_cinco = $row['opcion_cinco'];
    $opcion_seis = $row['opcion_seis'];
}

$stmt_doc_once->close();

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Supervisión académica</title>
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
            <h1 class="mb-2 text-center fw-bold">Supervisión de la Práctica Laboral al Estudiante</h1>

            <?php if (empty($estado)): ?>

                <form action="../estudiante/logic/documento-once.php" class="enviar-tema" method="POST" enctype="multipart/form-data">

                    <p class="text-center">
                        Indique con una “X” la evaluación que usted considere adecuada, en el momento de la supervisión durante la Práctica Pre-profesional laboral, teniendo en cuenta el cumplimiento de los siguientes indicadores:
                    </p>
                    <p class="text-center">
                        <strong>Supervisado</strong> ◉ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <strong>No Supervisado</strong> ○

                    </p>

                    <!-- Campo oculto -->
                    <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">

                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" colspan="1">INDICADORES:</th>
                                    <th>Cumple</th>
                                    <th>No Cumple</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- DISCIPLINA -->
                                <tr>
                                    <td class="text-start">El estudiante se encuentra en el área de trabajo asignada. </td>
                                    <td><input type="radio" name="pregunta1" value="1" required></td>
                                    <td><input type="radio" name="pregunta1" value="0"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">El estudiante se observa con la vestimenta adecuada según el área de trabajo.</td>
                                    <td><input type="radio" name="pregunta2" value="1" required></td>
                                    <td><input type="radio" name="pregunta2" value="0"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">El estudiante cuenta con los recursos necesarios para realizar sus prácticas.</td>
                                    <td><input type="radio" name="pregunta3" value="1" required></td>
                                    <td><input type="radio" name="pregunta3" value="0"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Existencia del docente que asigne y controle las actividades del estudiante.</td>
                                    <td><input type="radio" name="pregunta4" value="1" required></td>
                                    <td><input type="radio" name="pregunta4" value="0"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Los formatos de la carpeta de prácticas pre-profesionales laborales se han ido completando adecuadamente.</td>
                                    <td><input type="radio" name="pregunta5" value="1" required></td>
                                    <td><input type="radio" name="pregunta5" value="0"></td>
                                </tr>
                                <tr>
                                    <td class="text-start">Las actividades que realiza el estudiante están relacionadas con el objeto de la profesión.</td>
                                    <td><input type="radio" name="pregunta6" value="1" required></td>
                                    <td><input type="radio" name="pregunta6" value="0"></td>
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
                                <th>Motivo de Rechazo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars($opcion_uno ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_dos ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_tres ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_cuatro ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_cinco ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($opcion_seis ?? '-'); ?></td>
                                <td class="text-center">
                                    <?php echo !empty($motivo_rechazo)
                                        ? htmlspecialchars($motivo_rechazo)
                                        : '<span class="text-muted">No hay motivo de rechazo</span>'; ?>
                                </td>
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
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='for-once-edit.php?id=<?php echo $id; ?>'">
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
                            <form action="../estudiante/pdf/software/doc-once-pdf.php" method="GET" target="_blank">
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

        // Icono y título del encabezado
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
            $title = "Error en la Base de Datos";
        } else {
            $icon = "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
            $title = "Error";
        }

        // Mensaje del cuerpo del toast
        $message = match ($status) {
            'success'      => "Supervisión académica enviada correctamente.",
            'deleted'      => "El documento se ha eliminado correctamente.",
            'update'       => "La supervisión ha sido actualizada exitosamente.",
            'missing_data' => "Por favor, responde todas las preguntas antes de enviar.",
            'db_error'     => "Hubo un problema al guardar los datos. Intenta nuevamente.",
            default        => "Ocurrió un error inesperado."
        };
        ?>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <?= $icon ?>
                    <strong class="me-auto"><?= $title ?></strong>
                    <small>Justo ahora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
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