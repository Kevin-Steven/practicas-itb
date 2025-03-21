<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

$usuario_id = $_SESSION['usuario_id'];

$sql_doc_uno = "SELECT 
       d1.id,
       d1.estado, 
       d1.paralelo, 
       d1.promedio_notas,
       el.lugar_laborado,
       el.periodo_tiempo_meses,
       el.funciones_realizadas
FROM documento_uno d1
LEFT JOIN experiencia_laboral el ON d1.id = el.documento_uno_id
WHERE d1.usuario_id = ?
ORDER BY d1.id DESC";

$stmt_doc_uno = $conn->prepare($sql_doc_uno);
$stmt_doc_uno->bind_param("i", $usuario_id);
$stmt_doc_uno->execute();
$result_tema = $stmt_doc_uno->get_result();

$experiencia_laboral = [];
while ($row = $result_tema->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;
    $paralelo = $row['paralelo'] ?? null;
    $promedio = $row['promedio_notas'] ?? null;

    // Agregar experiencia solo si existen datos
    if ($row['lugar_laborado']) {
        $experiencia_laboral[] = [
            'lugar_laborado' => $row['lugar_laborado'],
            'periodo_tiempo_meses' => $row['periodo_tiempo_meses'],
            'funciones_realizadas' => $row['funciones_realizadas']
        ];
    }
}

$stmt_doc_uno->close();


if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>For 1</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>
    <div class="topbar z-1">
        <div class="menu-toggle">
            <i class='bx bx-menu'></i>
        </div>
        <div class="topbar-right">
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
                    <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
                    <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="perfil.php"><i class='bx bx-user me-2'></i>Perfil</a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="cambio-clave.php"><i class='bx bx-lock me-2'></i>Cambio de Clave</a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php"><i class='bx bx-log-out me-2'></i>Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="sidebar z-2" id="sidebar">
        <div class="profile">
            <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
            <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5>
            <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="inicio-estudiante.php"><i class='bx bx-home-alt'></i> Inicio</a>
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuAnteproyecto" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
                <span><i class='bx bxs-folder-open'></i> Documentos</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse show" id="submenuAnteproyecto">
                <ul class="list-unstyled ps-4">
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-uno.php' ? 'active' : ''; ?>" href="for-uno.php">
                            <i class="bx bx-file"></i> For 1
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-dos.php' ? 'active' : ''; ?>" href="for-dos.php">
                            <i class="bx bx-file"></i> For 2
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-tres.php' ? 'active' : ''; ?>" href="for-tres.php">
                            <i class="bx bx-file"></i> For 3
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-cuatro.php' ? 'active' : ''; ?>" href="for-cuatro.php">
                            <i class="bx bx-file"></i> For 4
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-cinco.php' ? 'active' : ''; ?>" href="for-cinco.php">
                            <i class="bx bx-file"></i> For 5
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-seis.php' ? 'active' : ''; ?>" href="for-seis.php">
                            <i class="bx bx-file"></i> For 6
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-siete.php' ? 'active' : ''; ?>" href="for-siete.php">
                            <i class="bx bx-file"></i> For 7
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-ocho.php' ? 'active' : ''; ?>" href="for-ocho.php">
                            <i class="bx bx-file"></i> For 8
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-nueve.php' ? 'active' : ''; ?>" href="for-nueve.php">
                            <i class="bx bx-file"></i> For 9
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-diez.php' ? 'active' : ''; ?>" href="for-diez.php">
                            <i class="bx bx-file"></i> For 10
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-once.php' ? 'active' : ''; ?>" href="for-once.php">
                            <i class="bx bx-file"></i> For 11
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-doce.php' ? 'active' : ''; ?>" href="for-doce.php">
                            <i class="bx bx-file"></i> For 12
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-trece.php' ? 'active' : ''; ?>" href="for-trece.php">
                            <i class="bx bx-file"></i> For 13
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-catorce.php' ? 'active' : ''; ?>" href="for-catorce.php">
                            <i class="bx bx-file"></i> For 14
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-quince.php' ? 'active bg-secondary' : ''; ?>" href="for-quince.php">
                            <i class="bx bx-file"></i> For 15
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for-dieciseis.php' ? 'active bg-secondary' : ''; ?>" href="for-dieciseis.php">
                            <i class="bx bx-file"></i> For 16
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
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
            <h1 class="mb-2 text-center fw-bold">Enviar Datos</h1>

            <?php if (empty($estado)  || $estado === 'Corregir'): ?>
                <div class="card shadow-lg container-fluid">
                    <div class="card-body">
                        <form action="../estudiante/logic/documento-uno.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-title text-center">Datos académicos</h2>
                                    <div class="mb-2">
                                        <label for="paralelo" class="form-label fw-bold">Paralelo</label>
                                        <input type="text" class="form-control" id="paralelo" name="paralelo" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="promedio" class="form-label fw-bold">Promedio de notas:</label>
                                        <input type="number" class="form-control" id="promedio" name="promedio" step="0.01" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h2 class="card-title text-center">Experiencia laboral</h2>
                                    <div id="contenedor-experiencia">
                                        <div class="experiencia-laboral">
                                            <div class="mb-2">
                                                <label for="lugar_laborado" class="form-label fw-bold">Últimos lugares donde ha laborado:</label>
                                                <input type="text" class="form-control" name="lugar_laborado[]" required>
                                            </div>
                                            <div class="mb-2">
                                                <label for="periodo_tiempo" class="form-label fw-bold">Periodo de tiempo (meses):</label>
                                                <input type="text" class="form-control" name="periodo_tiempo[]" required>
                                            </div>
                                            <div class="mb-2">
                                                <label for="funciones_realizadas" class="form-label fw-bold">Funciones realizadas:</label>
                                                <input type="text" class="form-control" name="funciones_realizadas[]" required>
                                            </div>
                                            <button type="button" class="btn btn-sm eliminar-experiencia" style="display: none; background: #df1f1f;">Eliminar</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm mt-2" id="agregar-experiencia">Agregar más experiencia</button>
                                </div>
                            </div>

                            <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                                <button type="submit" class="btn">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <h3 class="text-center mt-4 mb-3">Estado del Documento</h3>
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
                                                    <button type="button" class="btn btn-warning" onclick="window.location.href='for-uno-edit.php?id=<?php echo $id; ?>'">
                                                        <i class='bx bx-edit-alt'></i>
                                                    </button>
                                                </div>
                                            </td>
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

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light text-center">
        <div class="container">
            <p class="mb-0">&copy; 2025 Gestoria de Practicas Profesionales - Instituto Superior Tecnológico Bolivariano de Tecnología.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/expLaboral.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>