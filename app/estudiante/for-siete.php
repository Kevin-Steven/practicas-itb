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

$sql_doc_siete = "SELECT 
        u.id AS usuario_id,
        u.nombres,
        u.apellidos,
        u.cedula,
        c.carrera AS nombre_carrera,
        ds.estado,
        ds.motivo_rechazo
    FROM usuarios u
    INNER JOIN carrera c ON u.carrera_id = c.id
    LEFT JOIN documento_siete ds ON u.id = ds.usuario_id
    WHERE u.id = ?";

$stmt_doc_siete = $conn->prepare($sql_doc_siete);
$stmt_doc_siete->bind_param("i", $usuario_id);
$stmt_doc_siete->execute();
$result_doc_siete = $stmt_doc_siete->get_result();

while ($row = $result_doc_siete->fetch_assoc()) {
    $id = $row['usuario_id'] ?? null;
    $nombres = $row['nombres'] ?? null;
    $apellidos = $row['apellidos'] ?? null;
    $cedula = $row['cedula'] ?? null;
    $nombre_carrera = $row['nombre_carrera'] ?? null;
    $estado = $row['estado'] ?? "Sin estado";
    $motivo_rechazo = $row['motivo_rechazo'] ?? "<span class='text-muted'>No tiene motivo de rechazo</span>";
}

$stmt_doc_siete->close();

if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carta de Asignación</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>
    <?php renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil); ?>

    <!-- Toast -->
    <?php if (isset($_GET['status'])): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <?php if ($_GET['status'] === 'success'): ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                        <strong class="me-auto">Subida Exitosa</strong>
                    <?php elseif ($_GET['status'] === 'deleted'): ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                        <strong class="me-auto">Documento Eliminado</strong>
                    <?php elseif ($_GET['status'] === 'update'): ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                        <strong class="me-auto">Documento Actualizado</strong>
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
                        case 'success':
                            echo "Los datos se han subido correctamente.";
                            break;
                        case 'deleted':
                            echo "El documento se ha eliminado correctamente.";
                            break;
                        case 'update':
                            echo "El documento se ha actualizado correctamente.";
                            break;
                        case 'invalid_extension':
                            echo "Solo se permiten archivos ZIP.";
                            break;
                        case 'too_large':
                            echo "El archivo supera el tamaño máximo de 20 MB.";
                            break;
                        case 'upload_error':
                            echo "Hubo un error al mover el archivo.";
                            break;
                        case 'db_error':
                            echo "Error al actualizar la base de datos.";
                            break;
                        case 'no_file':
                            echo "No se ha seleccionado ningún archivo.";
                            break;
                        case 'form_error':
                            echo "Error en el envío del formulario.";
                            break;
                        case 'not_found':
                            echo "No se encontraron datos del usuario.";
                            break;
                        case 'missing_data':
                            echo "Faltan datos en el formulario.";
                            break;
                        default:
                            echo "Ocurrió un error desconocido.";
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container">
            <h1 class="mb-2 text-center fw-bold">Compromiso Ético de Responsabilidad para las Prácticas en el Entorno Laboral Real</h1>

            <h3 class="text-center mt-2 mb-3">Estado del Documento</h3>
            <div class="table-responsive">
                <table class="table table-bordered shadow-lg">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Cédula</th>
                            <th>Carrera</th>
                            <th>Motivo de Rechazo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- ✅ Aquí tus datos -->
                            <td class="text-center"><?php echo $nombres; ?></td>
                            <td class="text-center"><?php echo $apellidos; ?></td>
                            <td class="text-center"><?php echo $cedula; ?></td>
                            <td class="text-center"><?php echo $nombre_carrera; ?></td>
                            <td class="text-center"><?php echo $motivo_rechazo; ?></td>
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
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalImprimir<?php echo $id; ?>">
                                        <i class='bx bxs-file-pdf'></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                    <!-- ✅ Modal fuera de la tabla -->
                    <div class="modal fade" id="modalImprimir<?php echo $id; ?>" tabindex="-1" aria-labelledby="modalImprimirLabel<?php echo $id; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="../estudiante/pdf/doc-siete-pdf.php" method="GET" target="_blank">
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
    </div>

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>