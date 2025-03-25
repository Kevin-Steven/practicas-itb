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

$sql_doc_catorce = "SELECT 
       dc.id,
       dc.estado,
       dc.pdf_escaneado,
       dc.motivo_rechazo
FROM documento_catorce dc
WHERE dc.usuario_id = ?
ORDER BY dc.id DESC";

$stmt_doc_catorce = $conn->prepare($sql_doc_catorce);
$stmt_doc_catorce->bind_param("i", $usuario_id);
$stmt_doc_catorce->execute();
$result_tema = $stmt_doc_catorce->get_result();

while ($row = $result_tema->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;
    $pdf_escaneado = $row['pdf_escaneado'] ?? null;
    $motivo_rechazo = $row['motivo_rechazo'] ?? null;
}

$stmt_doc_catorce->close();


if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Base Legal</title>
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
            <h1 class="mb-2 text-center fw-bold">Base Legal de las Prácticas Laborales PDF Firmado</h1>

            <?php if (empty($estado)): ?>

                <div class="card shadow-lg container-fluid">
                    <div class="card-body">
                        <form action="../estudiante/logic/documento-catorce.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-12">
                                    <h2 class="card-title text-center">Subir Documento Escaneado</h2>

                                    <div class="mb-2">
                                        <label for="pdf-escaneado" class="form-label fw-bold">Subir PDF Escaneado:</label>
                                        <input type="file" class="form-control" id="pdf-escaneado" name="pdf-escaneado" accept="application/pdf" required>
                                    </div>

                                </div>
                                <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">

                            </div>

                            <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                                <button type="submit" class="btn">Enviar Datos</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <h3 class="text-center mt-2 mb-3">Estado del Documento</h3>
                <div class="table-responsive">
                    <table class="table table-bordered shadow-lg">
                        <thead class="table-light text-center">
                            <tr>
                                <th>PDF Escaneado</th>
                                <th>Motivo de Rechazo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- ✅ Aquí tus datos -->
                                <td class="text-center">
                                    <a href="<?php echo $pdf_escaneado; ?>" target="_blank">Ver PDF</a>
                                </td>
                                <td class="text-center">
                                    <?php echo !empty($row['motivo_rechazo'])
                                        ? htmlspecialchars($row['motivo_rechazo'])
                                        : '<span class="text-muted">No hay motivo de rechazo</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    // Lógica para asignar la clase de Bootstrap según el estado
                                    $badgeClass = '';

                                    if ($estado === 'Pendiente') {
                                        $badgeClass = 'badge bg-warning text-dark'; // Amarillo
                                    } elseif ($estado === 'Corregir') {
                                        $badgeClass = 'badge bg-danger'; // Rojo
                                    } elseif ($estado === 'Aprobado') {
                                        $badgeClass = 'badge bg-success'; // Verde
                                    } else {
                                        $badgeClass = 'badge bg-secondary'; // Gris si el estado no es reconocido
                                    }
                                    ?>

                                    <span class="<?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($estado); ?>
                                    </span>
                                </td>

                                <!-- ✅ Acciones -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='for-catorce-edit.php?id=<?php echo $id; ?>'">
                                            <i class='bx bx-edit-alt'></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>

                        <!-- ✅ Modal fuera de la tabla -->
                        <div class="modal fade" id="modalImprimir<?php echo $id; ?>" tabindex="-1" aria-labelledby="modalImprimirLabel<?php echo $id; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="../estudiante/pdf/software/doc-dos-pdf.php" method="GET" target="_blank">
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

                    </table>
                </div>
        </div>
    <?php endif; ?>
    </div>
    </div>

    <!-- Toast -->
    <?php if (isset($_GET['status'])): ?>
        <?php
        $status = $_GET['status'];
        $icon = "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
        $title = "Error";
        $message = "Ocurrió un error desconocido.";

        switch ($status) {
            case 'success':
                $icon = "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
                $title = "Documento Enviado";
                $message = "Los datos se han subido correctamente.";
                break;
            case 'update':
                $icon = "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
                $title = "Documento Actualizado";
                $message = "El documento se ha actualizado correctamente.";
                break;
            case 'deleted':
                $icon = "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
                $title = "Documento Eliminado";
                $message = "El documento se ha eliminado correctamente.";
                break;
            case 'no_changes':
                $icon = "<i class='bx bx-info-circle fs-4 me-2 text-secondary'></i>";
                $title = "Sin Cambios";
                $message = "No se realizaron cambios al documento.";
                break;
            case 'missing_data':
                $message = "Faltan datos en el formulario.";
                break;
            case 'missing_pdf':
                $message = "Debes seleccionar un archivo PDF.";
                break;
            case 'invalid_format':
                $message = "Solo se permite subir archivos PDF.";
                break;
            case 'invalid_extension':
                $message = "Solo se permiten archivos ZIP.";
                break;
            case 'too_large':
                $message = "El archivo supera el tamaño máximo permitido (20 MB).";
                break;
            case 'upload_error':
                $message = "Hubo un error al mover el archivo.";
                break;
            case 'db_error':
                $message = "Error al actualizar la base de datos.";
                break;
            case 'no_file':
                $message = "No se ha seleccionado ningún archivo.";
                break;
            case 'form_error':
                $message = "Error en el envío del formulario.";
                break;
            case 'not_found':
                $message = "No se encontraron datos del documento.";
                break;
            case 'invalid_user':
                $message = "No tienes permiso para editar este documento.";
                break;
        }
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