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

$sql_doc_dos = "SELECT 
       d2.id,
       d2.estado, 
       d2.fecha_inicio,
       d2.hora_inicio,
       d2.fecha_fin,
       d2.hora_fin,
       d2.documento_eva_s,
       d2.hora_practicas,
       d2.nota_eva_s,
       d2.nombre_tutor_academico,
       d2.cedula_tutor_academico,
       d2.correo_tutor_academico
FROM documento_dos d2
WHERE d2.usuario_id = ?
ORDER BY d2.id DESC";

$stmt_doc_dos = $conn->prepare($sql_doc_dos);
$stmt_doc_dos->bind_param("i", $usuario_id);
$stmt_doc_dos->execute();
$result_tema = $stmt_doc_dos->get_result();

while ($row = $result_tema->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;
    $fecha_inicio = $row['fecha_inicio'] ?? null;
    $hora_inicio = $row['hora_inicio'] ?? null;
    $fecha_fin = $row['fecha_fin'] ?? null;
    $hora_fin = $row['hora_fin'] ?? null;
    $documento_eva_s = $row['documento_eva_s'] ?? null;
    $horas_practicas = $row['hora_practicas'] ?? null;
    $nota_eva_s = $row['nota_eva_s'] ?? null;
    $nombre_tutor_academico = $row['nombre_tutor_academico'] ?? null;
    $cedula_tutor_academico = $row['cedula_tutor_academico'] ?? null;
    $correo_tutor_academico = $row['correo_tutor_academico'] ?? null;
}

$stmt_doc_dos->close();


if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plan de Aprendizaje</title>
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
            <h1 class="mb-2 text-center fw-bold">Datos Generales del Estudiante</h1>

            <?php if (empty($estado)  || $estado === 'Corregir'): ?>

                <div class="card shadow-lg container-fluid">
                    <div class="card-body">
                        <form action="../estudiante/logic/documento-dos.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-title text-center">Periodo Práctica <br>Preprofesional</h2>
                                    <div class="mb-2">
                                        <label for="fecha_inicio" class="form-label fw-bold">Fecha Inicio:</label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="hora_inicio" class="form-label fw-bold">Hora Inicio:</label>
                                        <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="fecha_fin" class="form-label fw-bold">Fecha Fin:</label>
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="hora_fin" class="form-label fw-bold">Hora Fin:</label>
                                        <input type="time" class="form-control" id="hora_fin" name="hora_fin" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="horas_practicas" class="form-label fw-bold">Horas Prácticas:</label>
                                        <input type="number" class="form-control" id="horas_practicas" name="horas_practicas" step="0.01" placeholder="ej. 240" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h2 class="card-title text-center">Resultados Extraídos del EVA-S del Diagnóstico Inicial</h2>
                                    <div class="mb-2">
                                        <label for="eva-s" class="form-label fw-bold">Subir EVA-S:</label>
                                        <input type="file" class="form-control" id="eva_s" name="eva_s" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="nota_eva-s" class="form-label fw-bold">Nota EVA-S:</label>
                                        <input type="number" min="0" max="100" class="form-control" id="nota_eva-s" name="nota_eva-s" step="0.01" placeholder="ej. 100" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="tutor_academico" class="form-label fw-bold">Tutor Académico:</label>
                                        <input type="text" class="form-control" id="tutor_academico" name="tutor_academico" placeholder="ej. Juan Carlos Pérez Mora" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="cedula_tutor" class="form-label fw-bold">Cédula del tutor:</label>
                                        <input type="text" class="form-control" id="cedula_tutor" name="cedula_tutor" placeholder="ej. 1234567890" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="correo_tutor" class="form-label fw-bold">Correo electrónico del tutor:</label>
                                        <input type="email" class="form-control" id="correo_tutor" name="correo_tutor" placeholder="ej. tutor@gmail.com" required>
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
                                <th>Fecha Inicio</th>
                                <th>Hora Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Hora Fin</th>
                                <th>Horas Prácticas</th>
                                <th>EVA-S</th>
                                <th>Nota EVA-S</th>
                                <th>Tutor Académico</th>
                                <th>Cédula del tutor</th>
                                <th>Correo electrónico del tutor</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- ✅ Aquí tus datos -->
                                <td class="text-center"><?php echo $fecha_inicio; ?></td>
                                <td class="text-center"><?php echo $hora_inicio; ?></td>
                                <td class="text-center"><?php echo $fecha_fin; ?></td>
                                <td class="text-center"><?php echo $hora_fin; ?></td>
                                <td class="text-center"><?php echo $horas_practicas; ?></td>
                                <td class="text-center"><?php echo $nota_eva_s; ?></td>
                                <td class="text-center">
                                    <a href="../uploads/eva-s/<?php echo $documento_eva_s; ?>" target="_blank">
                                        Ver Imagen
                                    </a>

                                </td>
                                <td class="text-center"><?php echo $nombre_tutor_academico; ?></td>
                                <td class="text-center"><?php echo $cedula_tutor_academico; ?></td>
                                <td class="text-center"><?php echo $correo_tutor_academico; ?></td>
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
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='for-dos-edit.php?id=<?php echo $id; ?>'">
                                            <i class='bx bx-edit-alt'></i>
                                        </button>

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
                                    <form action="../estudiante/pdf/doc-dos-pdf.php" method="GET" target="_blank">
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

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/expLaboral.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>