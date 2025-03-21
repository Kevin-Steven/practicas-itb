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

// ✅ Consulta mejorada con LEFT JOIN a documento_uno
$sql_doc_ocho = "SELECT 
    u.id,
    u.nombres,
    u.apellidos,
    u.cedula,

    d8.id AS documento_ocho_id,
    d8.estado, 
    d8.motivo_rechazo,
    d8.departamento,

    ia.semanas_fecha,
    ia.horas_realizadas,
    ia.actividades_realizadas

FROM usuarios u
LEFT JOIN documento_ocho d8 ON d8.usuario_id = u.id
LEFT JOIN informe_actividades ia ON d8.id = ia.documento_ocho_id
WHERE u.id = ?
ORDER BY d8.id DESC";

$stmt_doc_ocho = $conn->prepare($sql_doc_ocho);
$stmt_doc_ocho->bind_param("i", $usuario_id);
$stmt_doc_ocho->execute();
$result_doc_ocho = $stmt_doc_ocho->get_result();

$usuario_info = null;
$informe_actividades = [];

// ✅ Si hay resultados, los almacenamos
while ($row = $result_doc_ocho->fetch_assoc()) {
    if (!$usuario_info) {
        $usuario_info = [
            'id' => $row['id'],
            'nombres' => $row['nombres'],
            'apellidos' => $row['apellidos'],
            'cedula' => $row['cedula'],
            'documento_ocho_id' => $row['documento_ocho_id'],
            'estado' => $row['estado'],
            'motivo_rechazo' => $row['motivo_rechazo'],
            'departamento' => $row['departamento']
        ];
    }

    if (!empty($row['semanas_fecha'])) {
        $informe_actividades[] = [
            'semanas_fecha' => $row['semanas_fecha'],
            'horas_realizadas' => $row['horas_realizadas'],
            'actividades_realizadas' => $row['actividades_realizadas']
        ];
    }
}

$stmt_doc_ocho->close();

// ✅ Chequear conexión (opcional, pero mejor validarlo al inicio)
if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}


// ✅ Asignar valores a variables individuales para uso fácil en el HTML
$estado = $usuario_info['estado'] ?? null;
$motivo_rechazo = $usuario_info['motivo_rechazo'] ?? null;
$departamento = $usuario_info['departamento'] ?? null;

?>


<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informe de Actividades</title>
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
                    <?php elseif ($_GET['status'] === 'updated'): ?>
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
                        case 'updated':
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

    <?php if (!empty($motivo_rechazo)): ?>
        <div class="modal fade" id="modalMotivoRechazo" tabindex="-1" aria-labelledby="modalMotivoRechazoLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalMotivoRechazoLabel">Motivo de Rechazo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <p><?php echo htmlspecialchars($motivo_rechazo); ?></p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container">
            <h1 class="mb-2 text-center fw-bold">Informe de Actividades</h1>

            <div class="modal fade" id="modalCamposIncompletos" tabindex="-1" aria-labelledby="modalCamposIncompletosLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="modalCamposIncompletosLabel">Atención</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            Por favor completa todos los campos antes de agregar otra semana de actividades.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($estado)  || $estado === 'Corregir'): ?>

                <div class="card shadow-lg container-fluid">
                    <div class="card-body">
                        <form action="../estudiante/logic/documento-ocho.php" class="enviar-tema" method="POST">

                            <div class="row">
                                <!-- Columna Izquierda -->
                                <div class="col-md-6">
                                    <h2 class="card-title text-center mb-4">Datos Generales</h2>
                                    <div class="mb-2">
                                        <label for="nombres" class="form-label fw-bold">Nombres</label>
                                        <input type="text" class="form-control" id="nombres" value="<?php echo htmlspecialchars($usuario_info['nombres']); ?>" disabled>
                                    </div>
                                    <div class="mb-2">
                                        <label for="apellidos" class="form-label fw-bold">Apellidos</label>
                                        <input type="text" class="form-control" id="apellidos" value="<?php echo htmlspecialchars($usuario_info['apellidos']); ?>" disabled>
                                    </div>
                                    <div class="mb-2">
                                        <label for="cedula" class="form-label fw-bold">Cédula</label>
                                        <input type="text" class="form-control" id="cedula" value="<?php echo htmlspecialchars($usuario_info['cedula']); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="departamento" class="form-label fw-bold">Departamento</label>
                                        <input type="text" class="form-control" id="departamento" name="departamento" placeholder="ej. Departamento de Sistemas" required>
                                    </div>

                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario_info['id']; ?>">
                                </div>

                                <!-- Columna Derecha -->
                                <div class="col-md-6">
                                    <h2 class="card-title text-center mb-4">Registro de actividades</h2>

                                    <div id="contenedor-semana">
                                        <div class="semana border p-3 mb-3">
                                            <div class="mb-2">
                                                <label for="semana" class="form-label fw-bold">Semanas/Fecha:</label>
                                                <input type="text" class="form-control" name="semana[]" placeholder="ej. Fecha 28 al 01, noviembre de 2024" required>
                                            </div>

                                            <div class="mb-2">
                                                <label for="horas_realizadas" class="form-label fw-bold">Horas realizadas:</label>
                                                <input type="number" class="form-control" name="horas_realizadas[]" placeholder="ej. 30" required>
                                            </div>

                                            <div class="mb-2">
                                                <label for="actividades_realizadas" class="form-label fw-bold">Actividades realizadas:</label>
                                                <textarea class="form-control" rows="2" name="actividades_realizadas[]" placeholder="ej. Integración del software de biométrico..." required></textarea>
                                            </div>

                                            <button type="button" class="btn btn-sm eliminar-semana" style="display: none; background: #df1f1f;">Eliminar</button>
                                        </div>
                                    </div>

                                    <div class="d-grid">
                                        <button type="button" class="btn btn-sm mt-2" id="agregar-semana">Agregar semana</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                                <button type="submit" class="btn">Enviar Datos</button>

                                <?php if (!empty($motivo_rechazo)): ?>
                                    <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalMotivoRechazo">
                                        Ver Motivo de Rechazo
                                    </a>
                                <?php endif; ?>
                            </div>

                        </form>

                    </div>
                </div>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-bordered shadow-lg">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Departamento</th>
                                <th>Semanas/Fecha</th>
                                <th>Horas realizadas</th>
                                <th>Actividades realizadas</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($informe_actividades)): ?>

                                <?php foreach ($informe_actividades as $index => $ia): ?>
                                    <tr>
                                        <!-- ✅ Solo en la primera fila mostramos Departamento, Estado y Acciones con rowspan -->
                                        <?php if ($index === 0): ?>
                                            <td class="text-center" rowspan="<?= count($informe_actividades) ?>">
                                                <?php echo $departamento ?? 'No aplica'; ?>
                                            </td>
                                        <?php endif; ?>

                                        <!-- ✅ Semanas/Fecha siempre cambia -->
                                        <td><?= htmlspecialchars($ia['semanas_fecha'] ?? 'No aplica'); ?></td>

                                        <!-- ✅ Horas realizadas -->
                                        <td class="text-center"><?= htmlspecialchars($ia['horas_realizadas'] ?? 'No aplica'); ?></td>

                                        <!-- ✅ Actividades realizadas -->
                                        <td><?= htmlspecialchars($ia['actividades_realizadas'] ?? 'No aplica'); ?></td>

                                        <?php if ($index === 0): ?>
                                            <td class="text-center" rowspan="<?= count($informe_actividades) ?>">
                                                <?php if ($estado === 'Pendiente'): ?>
                                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                                <?php elseif ($estado === 'Aprobado'): ?>
                                                    <span class="badge bg-success">Aprobado</span>
                                                <?php elseif ($estado === 'Corregir'): ?>
                                                    <span class="badge bg-danger">Rechazado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Desconocido</span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-center" rowspan="<?= count($informe_actividades) ?>">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-warning" onclick="window.location.href='for-ocho-edit.php?id=<?= $usuario_info['documento_ocho_id']; ?>'">
                                                        <i class='bx bx-edit-alt'></i>
                                                    </button>

                                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalImprimir<?= $usuario_info['documento_ocho_id']; ?>">
                                                        <i class='bx bxs-file-pdf'></i>
                                                    </button>
                                                </div>

                                                <!-- ✅ Modal para imprimir -->
                                                <div class="modal fade" id="modalImprimir<?= $usuario_info['documento_ocho_id']; ?>" tabindex="-1" aria-labelledby="modalImprimirLabel<?= $usuario_info['documento_ocho_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="../estudiante/pdf/doc-ocho-pdf.php" method="GET" target="_blank">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalImprimirLabel<?= $usuario_info['documento_ocho_id']; ?>">¿Desea generar el documento?</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-start">
                                                                    <p>Se generará un documento en formato PDF</p>
                                                                    <input type="hidden" name="id" value="<?= $usuario_info['documento_ocho_id']; ?>">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <!-- ✅ Si no hay registros de actividades -->
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($departamento ?? 'No aplica'); ?></td>
                                    <td class="text-center" colspan="2">No hay semanas registradas</td>
                                    <td class="text-center">No hay actividades registradas</td>

                                    <td class="text-center">
                                        <?php if ($estado === 'Pendiente'): ?>
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        <?php elseif ($estado === 'Aprobado'): ?>
                                            <span class="badge bg-success">Aprobado</span>
                                        <?php elseif ($estado === 'Corregir'): ?>
                                            <span class="badge bg-danger">Rechazado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Desconocido</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-warning" onclick="window.location.href='for-ocho-edit.php?id=<?= $usuario_info['documento_ocho_id']; ?>'">
                                                <i class='bx bx-edit-alt'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/semanaFecha.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>