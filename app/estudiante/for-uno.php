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
$sql_doc_uno = "SELECT 
    u.id AS usuario_id,
    u.nombres,
    u.apellidos,
    u.cedula,
    u.direccion,
    u.telefono,
    u.convencional,
    u.email,
    u.periodo,
    c.carrera AS nombre_carrera,
    cu.paralelo AS nombre_paralelo,

    d1.id AS documento_uno_id,
    d1.estado, 
    d1.promedio_notas,
    d1.motivo_rechazo,

    el.lugar_laborado,
    el.periodo_tiempo_meses,
    el.funciones_realizadas

FROM usuarios u
INNER JOIN carrera c ON u.carrera_id = c.id
LEFT JOIN cursos cu ON u.curso_id = cu.id
LEFT JOIN documento_uno d1 ON d1.usuario_id = u.id
LEFT JOIN experiencia_laboral el ON d1.id = el.documento_uno_id
WHERE u.id = ?
ORDER BY d1.id DESC";

$stmt_doc_uno = $conn->prepare($sql_doc_uno);
$stmt_doc_uno->bind_param("i", $usuario_id);
$stmt_doc_uno->execute();
$result_tema = $stmt_doc_uno->get_result();

$usuario_info = null;
$experiencia_laboral = [];

// ✅ Si hay resultados, los almacenamos
while ($row = $result_tema->fetch_assoc()) {
    if (!$usuario_info) {
        $usuario_info = [
            'usuario_id' => $row['usuario_id'],
            'nombres' => $row['nombres'],
            'apellidos' => $row['apellidos'],
            'cedula' => $row['cedula'],
            'direccion' => $row['direccion'],
            'telefono' => $row['telefono'],
            'convencional' => $row['convencional'],
            'email' => $row['email'],
            'periodo' => $row['periodo'],
            'carrera' => $row['nombre_carrera'],
            'paralelo' => $row['nombre_paralelo'],
            'documento_uno_id' => $row['documento_uno_id'],
            'estado' => $row['estado'],
            'promedio_notas' => $row['promedio_notas'],
            'motivo_rechazo' => $row['motivo_rechazo']
        ];
    }

    if (!empty($row['lugar_laborado'])) {
        $experiencia_laboral[] = [
            'lugar_laborado' => $row['lugar_laborado'],
            'periodo_tiempo_meses' => $row['periodo_tiempo_meses'],
            'funciones_realizadas' => $row['funciones_realizadas']
        ];
    }
}

$stmt_doc_uno->close();

// ✅ Si no existe $usuario_info, hacemos una consulta directa al usuario
if (!$usuario_info) {
    $sql_usuario_simple = "SELECT 
        u.id AS usuario_id,
        u.nombres,
        u.apellidos,
        u.cedula,
        u.direccion,
        u.telefono,
        u.convencional,
        u.email,
        u.periodo,
        c.carrera AS nombre_carrera,
        cu.paralelo AS nombre_paralelo
    FROM usuarios u
    INNER JOIN carrera c ON u.carrera_id = c.id
    LEFT JOIN cursos cu ON u.curso_id = cu.id
    WHERE u.id = ?";

    $stmt_simple = $conn->prepare($sql_usuario_simple);
    $stmt_simple->bind_param("i", $usuario_id);
    $stmt_simple->execute();
    $result_simple = $stmt_simple->get_result();

    if ($row_simple = $result_simple->fetch_assoc()) {
        $usuario_info = [
            'usuario_id' => $row_simple['usuario_id'],
            'nombres' => $row_simple['nombres'],
            'apellidos' => $row_simple['apellidos'],
            'cedula' => $row_simple['cedula'],
            'direccion' => $row_simple['direccion'],
            'telefono' => $row_simple['telefono'],
            'convencional' => $row_simple['convencional'],
            'email' => $row_simple['email'],
            'periodo' => $row_simple['periodo'],
            'carrera' => $row_simple['nombre_carrera'],
            'paralelo' => $row_simple['nombre_paralelo'],
            'documento_uno_id' => null,
            'estado' => null,
            'promedio_notas' => null,
            'motivo_rechazo' => null
        ];
    } else {
        die("❌ No se encontró información del usuario con id: $usuario_id");
    }

    $stmt_simple->close();
}

// ✅ Chequear conexión (opcional, pero mejor validarlo al inicio)
if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// ✅ Asignar valores a variables individuales para uso fácil en el HTML
$estado = $usuario_info['estado'] ?? null;
$promedio = $usuario_info['promedio_notas'] ?? null;
$paralelo = $usuario_info['paralelo'] ?? null;
$motivo_rechazo = $usuario_info['motivo_rechazo'] ?? null;
?>


<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ficha de Estudiante</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>
    <?php renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil); ?>

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
            <h1 class="mb-2 text-center fw-bold">Ficha de Estudiante</h1>

            <?php if (empty($estado)  || $estado === 'Corregir'): ?>

                <div class="card shadow-lg container-fluid">
                    <div class="card-body">
                        <form action="../estudiante/logic/documento-uno.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                            <div class="container">
                                <!-- Fila principal -->
                                <div class="row g-4">

                                    <!-- Columna 1: Datos Generales -->
                                    <div class="col-md-4">
                                        <h2 class="card-title text-center mb-4">Datos Generales</h2>

                                        <div class="mb-3">
                                            <label for="nombres" class="form-label fw-bold">Nombres</label>
                                            <input type="text" class="form-control" id="nombres" value="<?php echo htmlspecialchars($usuario_info['nombres']); ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="apellidos" class="form-label fw-bold">Apellidos</label>
                                            <input type="text" class="form-control" id="apellidos" value="<?php echo htmlspecialchars($usuario_info['apellidos']); ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="cedula" class="form-label fw-bold">Cédula</label>
                                            <input type="text" class="form-control" id="cedula" value="<?php echo htmlspecialchars($usuario_info['cedula']); ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="direccion" class="form-label fw-bold">Dirección</label>
                                            <input type="text" class="form-control" id="direccion" value="<?php echo htmlspecialchars($usuario_info['direccion']); ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="telefono" class="form-label fw-bold">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" value="<?php echo htmlspecialchars($usuario_info['telefono']); ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="convencional" class="form-label fw-bold">Teléfono convencional</label>
                                            <input type="text" class="form-control" id="convencional" value="<?php echo !empty($usuario_info['convencional']) ? htmlspecialchars($usuario_info['convencional']) : 'NO APLICA'; ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-bold">Correo electrónico</label>
                                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($usuario_info['email']); ?>" disabled>
                                        </div>
                                    </div>

                                    <!-- Columna 2: Datos Académicos -->
                                    <div class="col-md-4">
                                        <h2 class="card-title text-center mb-4">Datos Académicos</h2>

                                        <div class="mb-3">
                                            <label for="carrera" class="form-label fw-bold">Carrera</label>
                                            <input type="text" class="form-control" id="carrera" value="<?php echo htmlspecialchars($usuario_info['carrera']); ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="periodo" class="form-label fw-bold">Periodo</label>
                                            <input type="text" class="form-control" id="periodo" value="<?php echo htmlspecialchars($usuario_info['periodo']); ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="paralelo" class="form-label fw-bold">Paralelo (curso)</label>
                                            <input type="text" class="form-control" id="paralelo" name="paralelo" value="<?php echo htmlspecialchars($usuario_info['paralelo']); ?>" disabled>
                                        </div>

                                        <div class="mb-3">
                                            <label for="promedio" class="form-label fw-bold">Promedio de notas</label>
                                            <input type="number" class="form-control" id="promedio" name="promedio" step="0.01" placeholder="Ej. 90.00" required>
                                        </div>
                                    </div>

                                    <!-- Columna 3: Experiencia Laboral -->
                                    <div class="col-md-4">
                                        <h2 class="card-title text-center mb-4">Experiencia Laboral</h2>

                                        <div id="contenedor-experiencia">
                                            <div class="experiencia-laboral border p-3 mb-3 rounded">
                                                <div class="mb-3">
                                                    <label for="lugar_laborado" class="form-label fw-bold">Últimos lugares donde ha laborado</label>
                                                    <input type="text" class="form-control" name="lugar_laborado[]">
                                                </div>

                                                <div class="mb-3">
                                                    <label for="periodo_tiempo" class="form-label fw-bold">Periodo de tiempo (meses)</label>
                                                    <input type="text" class="form-control" name="periodo_tiempo[]">
                                                </div>

                                                <div class="mb-3">
                                                    <label for="funciones_realizadas" class="form-label fw-bold">Funciones realizadas</label>
                                                    <input type="text" class="form-control" name="funciones_realizadas[]">
                                                </div>

                                                <button type="button" class="btn btn-danger btn-sm eliminar-experiencia" style="background: #df1f1f; display: none;">Eliminar</button>
                                            </div>
                                        </div>

                                        <div class="d-grid">
                                            <button type="button" class="btn btn-primary btn-sm mt-2" id="agregar-experiencia">Agregar más experiencia</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div class="text-center mt-5 d-flex justify-content-center align-items-center gap-3">
                                    <button type="submit" class="btn">Enviar Datos</button>

                                    <?php if (!empty($motivo_rechazo)): ?>
                                        <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalMotivoRechazo">
                                            Ver Motivo de Rechazo
                                        </a>
                                    <?php endif; ?>
                                </div>
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
                                <th>Paralelo</th>
                                <th>Promedio</th>
                                <th>Lugar Laborado</th>
                                <th>Periodo de tiempo</th>
                                <th>Funciones realizadas</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($experiencia_laboral)): ?>
                                <?php foreach ($experiencia_laboral as $index => $exp): ?>
                                    <?php if ($index === 0): ?>
                                        <tr>
                                            <!-- ✅ Paralelo con rowspan -->
                                            <td class="text-center" rowspan="<?= max(1, count($experiencia_laboral)) ?>">
                                                <?php echo htmlspecialchars($paralelo ?? 'No aplica'); ?>
                                            </td>

                                            <!-- ✅ Promedio con rowspan -->
                                            <td class="text-center" rowspan="<?= max(1, count($experiencia_laboral)) ?>">
                                                <?php echo htmlspecialchars($promedio ?? 'No aplica'); ?>
                                            </td>

                                            <!-- ✅ Primer registro de experiencia -->
                                            <td class="text-center"><?php echo htmlspecialchars($exp['lugar_laborado']); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($exp['periodo_tiempo_meses']); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($exp['funciones_realizadas']); ?></td>

                                            <!-- ✅ Estado con rowspan -->
                                            <td class="text-center" rowspan="<?= max(1, count($experiencia_laboral)) ?>">
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

                                            <!-- ✅ Acciones con rowspan -->
                                            <td class="text-center" rowspan="<?= max(1, count($experiencia_laboral)) ?>">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-warning" onclick="window.location.href='for-uno-edit.php?id=<?php echo $usuario_info['documento_uno_id']; ?>'">
                                                        <i class='bx bx-edit-alt'></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalImprimir<?php echo $usuario_info['documento_uno_id']; ?>">
                                                        <i class='bx bxs-file-pdf'></i>
                                                    </button>

                                                </div>
                                            </td>

                                            <div class="modal fade" id="modalImprimir<?php echo $usuario_info['documento_uno_id']; ?>" tabindex="-1" aria-labelledby="modalImprimirLabel<?php echo $usuario_info['documento_uno_id']; ?>" aria-hidden="true">

                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="../estudiante/pdf/software/doc-uno-pdf.php" method="GET" target="_blank">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalImprimirLabel<?php echo $id; ?>">Desea generar el siguiente documento?</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Se generará un documento en formato PDF</p>
                                                                <input type="hidden" name="id" value="<?php echo $usuario_info['documento_uno_id']; ?>">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </tr>
                                    <?php else: ?>
                                        <!-- ✅ Registros de experiencia adicionales -->
                                        <tr>
                                            <td class="text-center"><?php echo htmlspecialchars($exp['lugar_laborado']); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($exp['periodo_tiempo_meses']); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($exp['funciones_realizadas']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- ✅ Sin experiencia laboral -->
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($paralelo ?? 'No aplica'); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($promedio ?? 'No aplica'); ?></td>
                                    <td class="text-center" colspan="3">No hay experiencia registrada</td>
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
                                            <button type="button" class="btn btn-warning" onclick="window.location.href='for-uno-edit.php?id=<?php echo $id; ?>'">
                                                <i class='bx bx-edit-alt'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
        </div>
    <?php endif; ?>
    </div>
    </div>

    <!-- Modal de Advertencia -->
    <div class="modal fade" id="modalAdvertencia" tabindex="-1" aria-labelledby="modalAdvertenciaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalAdvertenciaLabel">Atención</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    Por favor, complete todos los campos antes de agregar una nueva experiencia.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

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

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/expLaboral.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>