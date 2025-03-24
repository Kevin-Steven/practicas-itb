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

$sql_doc_tres = "SELECT
    d3.id,
    d3.estado, 
    d3.nombre_entidad_receptora,
    d3.departamento_entidad_receptora, 
    d3.nombres_tutor_receptor, 
    d3.cargo_tutor_receptor, 
    d3.numero_telefono_tutor_receptor, 
    d3.ciudad_entidad_receptora,
    d3.motivo_rechazo
FROM documento_tres d3
WHERE d3.usuario_id = ? 
ORDER BY d3.id DESC";

$stmt_doc_tres = $conn->prepare($sql_doc_tres);
$stmt_doc_tres->bind_param("i", $usuario_id);
$stmt_doc_tres->execute();
$result_doc_tres = $stmt_doc_tres->get_result();

$estado = $estado ?? null;

while ($row = $result_doc_tres->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;
    $nombre_entidad_receptora = $row['nombre_entidad_receptora'] ?? null;
    $departamento_entidad_receptora = $row['departamento_entidad_receptora'] ?? null;
    $nombres_tutor_receptor = $row['nombres_tutor_receptor'] ?? null;
    $cargo_tutor_receptor = $row['cargo_tutor_receptor'] ?? null;
    $numero_telefono_tutor_receptor = $row['numero_telefono_tutor_receptor'] ?? null;
    $ciudad_entidad_receptora = $row['ciudad_entidad_receptora'] ?? null;
    $motivo_rechazo = $row['motivo_rechazo'] ?? null;
}

$stmt_doc_tres->close();

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
            <h1 class="mb-2 text-center fw-bold">Asignación de Estudiante a Prácticas Laborales</h1>
            <?php if (empty($estado)  || $estado === 'Corregir'): ?>
                <div class="card shadow-lg container-fluid">
                    <div class="card-body">
                        <form action="../estudiante/logic/documento-tres.php" class="enviar-tema" method="POST">
                            <div class="row">
                                <h2 class="card-title text-center">Datos de la Entidad Receptora</h2>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label for="nombre_entidad_receptora" class="form-label fw-bold">Nombre Entidad Receptora:</label>
                                        <input type="text" class="form-control" id="nombre_entidad_receptora" name="nombre_entidad_receptora" placeholder="Ej. Empresa de Software S.A." required>
                                    </div>

                                    <div class="mb-2">
                                        <label for="nombres_tutor_receptor" class="form-label fw-bold">Nombres Tutor Entidad Receptora:</label>
                                        <input type="text" class="form-control" id="nombres_tutor_receptor" name="nombres_tutor_receptor" placeholder="Ej. Juan Pérez" required>
                                    </div>

                                    <div class="mb-2">
                                        <label for="cargo_tutor_receptor" class="form-label fw-bold">Cargo Tutor Entidad Receptora:</label>
                                        <input type="text" class="form-control" id="cargo_tutor_receptor" name="cargo_tutor_receptor" placeholder="Ej. Gerente de Recursos Humanos" required>
                                    </div>

                                    <div class="mb-2">
                                        <label for="ciudad_entidad_receptora" class="form-label fw-bold">Ciudad Entidad Receptora:</label>
                                        <input type="text" class="form-control" id="ciudad_entidad_receptora" name="ciudad_entidad_receptora" placeholder="Ej. Quito" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label for="departamento_entidad_receptora" class="form-label fw-bold">Departamento Entidad Receptora:</label>
                                        <input type="text" class="form-control" id="departamento_entidad_receptora" name="departamento_entidad_receptora" placeholder="Ej. Sistemas" required>
                                    </div>

                                    <div class="mb-2">
                                        <label for="numero_telefono_tutor_receptor" class="form-label fw-bold">Número de Teléfono Tutor Entidad Receptora:</label>
                                        <input type="text" class="form-control" id="numero_telefono_tutor_receptor" name="numero_telefono_tutor_receptor" placeholder="Ej. 0987654321" maxlength="10" oninput="validateInput(this)" title="Debe contener 10 dígitos numéricos" required>
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
                <h2 class="text-center mb-4">Estado del Documento</h2>
                <div class="table-responsive">
                    <table class="table table-bordered shadow-lg">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Nombre Entidad Receptora</th>
                                <th>Nombres Tutor Entidad Receptora</th>
                                <th>Número de Teléfono Tutor Entidad Receptora</th>
                                <th>Departamento Entidad Receptora</th>
                                <th>Cargo Tutor Entidad Receptora</th>
                                <th>Ciudad Entidad Receptora</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- ✅ Aquí tus datos -->
                                <td class="text-center"><?php echo $nombre_entidad_receptora; ?></td>
                                <td class="text-center"><?php echo $nombres_tutor_receptor; ?></td>
                                <td class="text-center"><?php echo $numero_telefono_tutor_receptor; ?></td>
                                <td class="text-center"><?php echo $departamento_entidad_receptora; ?></td>
                                <td class="text-center"><?php echo $cargo_tutor_receptor; ?></td>
                                <td class="text-center"><?php echo $ciudad_entidad_receptora; ?></td>
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
                                        <button type="button" class="btn btn-warning" onclick="window.location.href='for-tres-edit.php?id=<?php echo $id; ?>'">
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
                                    <form action="../estudiante/pdf/software/doc-tres-pdf.php" method="GET" target="_blank">
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