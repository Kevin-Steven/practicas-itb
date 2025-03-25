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

$sql_doc_doce = "SELECT 
       dq.id,
       dq.img_estudiante_area_trabajo,
       dq.img_estudiante_area_trabajo_herramientas,
       dq.img_estudiante_supervisor_entidad,
       dq.motivo_rechazo,
       dq.estado
FROM documento_quince dq
WHERE dq.usuario_id = ?
ORDER BY dq.id DESC
LIMIT 1";

$stmt_doc_doce = $conn->prepare($sql_doc_doce);
$stmt_doc_doce->bind_param("i", $usuario_id);
$stmt_doc_doce->execute();
$result_doc_doce = $stmt_doc_doce->get_result();

$estado = null;

if ($row = $result_doc_doce->fetch_assoc()) {
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
    $img_practicas_puesto_trabajo = $row['img_practicas_puesto_trabajo'];
    $img_puesto_trabajo = $row['img_puesto_trabajo'];
    $img_estudiante_tutor_entidad = $row['img_estudiante_tutor_entidad'];
    $img_cierre_practicas = $row['img_cierre_practicas'];
}

$stmt_doc_doce->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evidencias de Prácticas</title>
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
            <h1 class="mb-2 text-center fw-bold">Evidencias Del Estudiante En La Ejecución De Prácticas</h1>

            <?php if (empty($estado)): ?>
                <div class="card shadow-lg container-fluid">
                    <div class="card-body">
                        <form action="../estudiante/logic/documento-quince.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">
                            <h3 class="text-center mb-3">Evidencias</h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Evidencia 1: Foto del estudiante que evidencie las actividades que realiza durante la práctica en el área de trabajo asignada. </label>
                                    <input type="file" class="form-control" name="img_estudiante_area_trabajo" accept="image/*" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Evidencia 2: Foto del estudiante en su puesto de trabajo y las herramientas que utiliza para el desarrollo de las actividades asignadas. </label>
                                    <input type="file" class="form-control" name="img_estudiante_area_trabajo_herramientas" accept="image/*" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Evidencia 3: Foto del estudiante y el supervisor de la empresa que evidencien las actividades realizadas durante la práctica en el área de trabajo asignada.</label>
                                    <input type="file" class="form-control" name="img_estudiante_supervisor_entidad" accept="image/*" required>
                                </div>
                            </div>

                            <!-- Botón de Enviar -->
                            <div class="container-fluid text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                                <button type="submit" class="btn btn-primary">Enviar Datos</button>
                            </div>

                        </form>
                    </div>
                </div>

            <?php else: ?>
                <h3 class="text-center mt-2 mb-3">Estado del Formulario</h3>

                <div class="table-responsive">
                    <table class="table table-bordered shadow-lg text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Evidencia 1</th>
                                <th>Evidencia 2</th>
                                <th>Evidencia 3</th>
                                <th>Motivo de Rechazo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <a href="<?php echo $img_estudiante_area_trabajo; ?>" target="_blank">Ver imagen</a>
                                </td>
                                <td>
                                    <a href="<?php echo $img_estudiante_area_trabajo_herramientas; ?>" target="_blank">Ver imagen</a>
                                </td>
                                <td>
                                    <a href="<?php echo $img_estudiante_supervisor_entidad; ?>" target="_blank">Ver imagen</a>
                                </td>
                                <td class="text-center">
                                    <?php echo !empty($row['motivo_rechazo'])
                                        ? htmlspecialchars($row['motivo_rechazo'])
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
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='for-doce-edit.php?id=<?php echo $id; ?>'">
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
                            <form action="../estudiante/pdf/software/doc-doce-pdf.php" method="GET" target="_blank">
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