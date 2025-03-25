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

$sql_doc_seis = "SELECT 
       d6.id,
       d6.actividad_economica,
       d6.provincia,
       d6.hora_inicio,
       d6.hora_fin,
       d6.jornada_laboral,
       d6.numero_practicas,
       d5.numero_institucional,
       d6.estado,
       d3.nombres_tutor_receptor,
       d3.cargo_tutor_receptor,
       d3.numero_telefono_tutor_receptor
FROM documento_seis d6
LEFT JOIN documento_tres d3 ON d6.usuario_id = d3.usuario_id
INNER JOIN documento_cinco d5 ON d6.usuario_id = d5.usuario_id
WHERE d6.usuario_id = ?
ORDER BY d6.id DESC
LIMIT 1";

$stmt_doc_seis = $conn->prepare($sql_doc_seis);
$stmt_doc_seis->bind_param("i", $usuario_id);
$stmt_doc_seis->execute();
$result_doc_seis = $stmt_doc_seis->get_result();

if ($row = $result_doc_seis->fetch_assoc()) {
    $id = $row['id'];
    $estado = $row['estado'];
    $actividad_economica = $row['actividad_economica'];
    $provincia = $row['provincia'];
    $hora_inicio = $row['hora_inicio'];
    $hora_fin = $row['hora_fin'];
    $jornada_laboral = $row['jornada_laboral'];
    $nombres_tutor_receptor = $row['nombres_tutor_receptor']; 
    $cargo_tutor_receptor = $row['cargo_tutor_receptor'];
    $numero_practicas = $row['numero_practicas'];
    $numero_telefono = $row['numero_telefono_tutor_receptor'];
    $numero_institucional = $row['numero_institucional'];
}

$stmt_doc_seis->close();

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
                    <?php
                    // Determinar el tipo de icono según el estado
                    $success_status = ['success', 'update', 'deleted'];
                    if (in_array($_GET['status'], $success_status)) : ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                        <strong class="me-auto">
                            <?php
                            switch ($_GET['status']) {
                                case 'success':
                                    echo 'Registro Exitoso';
                                    break;
                                case 'update':
                                    echo 'Actualización Exitosa';
                                    break;
                                case 'deleted':
                                    echo 'Eliminación Exitosa';
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
                            echo "Los datos de la entidad receptora se han registrado correctamente.";
                            break;
                        case 'update':
                            echo "Los datos se han actualizado correctamente.";
                            break;
                        case 'deleted':
                            echo "El documento se ha eliminado correctamente.";
                            break;

                        // Errores específicos
                        case 'invalid_user':
                            echo "Usuario inválido. Debes iniciar sesión nuevamente.";
                            break;
                        case 'missing_data':
                            echo "Faltan datos en el formulario. Revisa que todos los campos estén completos.";
                            break;
                        case 'invalid_ruc':
                            echo "El RUC ingresado no es válido. Debe contener exactamente 13 dígitos.";
                            break;
                        case 'invalid_email':
                            echo "El correo electrónico del representante no es válido.";
                            break;
                        case 'invalid_phone':
                            echo "El número institucional no es válido. Debe contener solo números (7-15 dígitos).";
                            break;
                        case 'invalid_extension':
                            echo "Solo se permiten archivos de imagen: PNG, JPG, JPEG o GIF para el logo.";
                            break;
                        case 'no_logo_file':
                            echo "Debes seleccionar el logo de la entidad receptora.";
                            break;
                        case 'upload_error':
                            echo "Hubo un error al subir el logo. Intenta nuevamente.";
                            break;
                        case 'db_error':
                            echo "Error al guardar los datos en la base de datos.";
                            break;
                        case 'prepare_error':
                            echo "Error en la preparación de la consulta SQL.";
                            break;

                        // Otros casos
                        case 'not_found':
                            echo "No se encontraron datos del usuario.";
                            break;
                        default:
                            echo "Ocurrió un error inesperado. Intenta nuevamente.";
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
            <h1 class="mb-2 text-center fw-bold">Ficha de la Entidad Receptora</h1>

            <?php if (empty($estado)): ?>
                <div class="card shadow-lg container-fluid">
                    <div class="card-body">
                        <form action="../estudiante/logic/documento-seis.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <h2 class="card-title text-center mb-3">Datos de la Entidad Receptora</h2>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label for="actividad_economica" class="form-label fw-bold">Actividad económica principal:</label>
                                        <input type="text" class="form-control" id="actividad_economica" name="actividad_economica" placeholder="Ej: Comercio al por menor" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="provincia" class="form-label fw-bold">Provincia:</label>
                                        <input type="text" class="form-control" id="provincia" name="provincia" placeholder="Ej: Guayas" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="horario_practica" class="form-label fw-bold">Horario de la práctica:</label>

                                        <div class="d-flex gap-2">
                                            <input type="time" class="form-control" id="horario_practica_inicio" name="horario_practica_inicio" required>
                                            <input type="time" class="form-control" id="horario_practica_fin" name="horario_practica_fin" required>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label for="jornada_laboral" class="form-label fw-bold">Jornada laboral:</label>
                                        <select class="form-control" id="jornada_laboral" name="jornada_laboral" required>
                                            <option value="" disabled selected>Seleccionar</option>
                                            <option value="Lunes a viernes">Lunes a viernes</option>
                                            <option value="Lunes a sábado">Lunes a sábado</option>
                                            <option value="Completa">Completa</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">

                                    <div class="mb-2">
                                        <label for="numero_practicas" class="form-label fw-bold">Número de prácticas:</label>
                                        <select class="form-control" id="numero_practicas" name="numero_practicas" required>
                                            <option value="" disabled selected>Seleccionar</option>
                                            <option value="Primera-Segunda-Tercera">Primera-Segunda-Tercera</option>
                                        </select>
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
                                <th>Actividad económica principal</th>
                                <th>Provincia</th>
                                <th>Horario de la práctica</th>
                                <th>Jornada laboral</th>
                                <th>Nombres y Apellidos del tutor de la entidad receptora</th>
                                <th>Cargo del tutor de la entidad receptora</th>
                                <th>Número de prácticas</th>
                                <th>Número de teléfono tutor</th>
                                <th>Número institucional</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- ✅ Aquí tus datos -->
                                <td class="text-center"><?php echo $actividad_economica; ?></td>
                                <td class="text-center"><?php echo $provincia; ?></td>
                                <td class="text-center"><?php echo date('H:i', strtotime($hora_inicio)); ?> - <?php echo date('H:i', strtotime($hora_fin)); ?></td>
                                <td class="text-center"><?php echo $jornada_laboral; ?></td>
                                <td class="text-center"><?php echo $nombres_tutor_receptor; ?></td>
                                <td class="text-center"><?php echo $cargo_tutor_receptor; ?></td>
                                <td class="text-center"><?php echo $numero_practicas; ?></td>
                                <td class="text-center"><?php echo $numero_telefono; ?></td>
                                <td class="text-center"><?php echo (!empty($numero_institucional)) ? $numero_institucional : 'NO APLICA'; ?></td>

                                <td class="text-center">
                                    <?php
                                    // Lógica para asignar la clase de Bootstrap según el estado
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

                                <!-- ✅ Acciones -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='for-seis-edit.php?id=<?php echo $id; ?>'">
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
                                    <form action="../estudiante/pdf/software/doc-seis-pdf.php" method="GET" target="_blank">
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
    <script src="../js/toast.js"></script>
    <script src="../js/number.js"></script>
</body>

</html>