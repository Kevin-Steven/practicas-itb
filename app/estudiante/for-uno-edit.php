<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$usuario_id = $_SESSION['usuario_id'];

// Aquí recibimos el ID desde la URL
$documento_id = $_GET['id'] ?? null;

if (!$documento_id) {
    header("Location: for-uno.php?status=not_found");
    exit();
}

// Consultar el documento por ID
$sql_doc_uno = "SELECT 
       d1.id,
       d1.estado, 
       d1.paralelo, 
       d1.promedio_notas
FROM documento_uno d1
WHERE d1.id = ? AND d1.usuario_id = ?";

$stmt_doc_uno = $conn->prepare($sql_doc_uno);
$stmt_doc_uno->bind_param("ii", $documento_id, $usuario_id);
$stmt_doc_uno->execute();
$result_doc_uno = $stmt_doc_uno->get_result();

if ($result_doc_uno->num_rows === 0) {
    header("Location: for-uno.php?status=not_found");
    exit();
}

$documento = $result_doc_uno->fetch_assoc();
$paralelo = $documento['paralelo'];
$promedio = $documento['promedio_notas'];

$stmt_doc_uno->close();

// Obtener las experiencias laborales relacionadas
$sql_experiencias = "SELECT lugar_laborado, periodo_tiempo_meses, funciones_realizadas 
FROM experiencia_laboral 
WHERE documento_uno_id = ?";

$stmt_exp = $conn->prepare($sql_experiencias);
$stmt_exp->bind_param("i", $documento_id);
$stmt_exp->execute();
$result_exp = $stmt_exp->get_result();

$experiencia_laboral = [];
while ($exp = $result_exp->fetch_assoc()) {
    $experiencia_laboral[] = $exp;
}

$stmt_exp->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>For 1 Editar</title>
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
                        <a class="nav-link active<?php echo basename($_SERVER['PHP_SELF']) == 'for-uno.php' ? 'active' : ''; ?>" href="for-uno.php">
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
            <h1 class="mb-2 text-center fw-bold">Actualizar Datos</h1>

            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-uno-actualizar.php" class="inscripcion" method="POST" enctype="multipart/form-data">

                        <!-- Campo oculto para el ID del documento -->
                        <input type="hidden" name="documento_id" value="<?php echo htmlspecialchars($documento_id); ?>">

                        <div class="row">
                            <!-- Datos académicos -->
                            <div class="col-md-6">
                                <h2 class="card-title text-center">Datos Académicos</h2>
                                <div class="mb-2">
                                    <label for="paralelo" class="form-label fw-bold">Paralelo</label>
                                    <input type="text" class="form-control" id="paralelo" name="paralelo" value="<?php echo htmlspecialchars($paralelo); ?>" required>
                                </div>
                                <div class="mb-2">
                                    <label for="promedio" class="form-label fw-bold">Promedio de notas:</label>
                                    <input type="number" class="form-control" id="promedio" name="promedio" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($promedio); ?>" required>
                                </div>
                            </div>

                            <!-- Experiencia laboral -->
                            <div class="col-md-6">
                                <h2 class="card-title text-center">Experiencia Laboral</h2>

                                <div id="contenedor-experiencia">
                                    <?php if (!empty($experiencia_laboral)): ?>
                                        <?php foreach ($experiencia_laboral as $index => $exp): ?>
                                            <div class="experiencia-laboral border p-3 mb-3 rounded">
                                                <div class="mb-2">
                                                    <label class="form-label fw-bold">Últimos lugares donde ha laborado:</label>
                                                    <input type="text" class="form-control" name="lugar_laborado[]" value="<?php echo htmlspecialchars($exp['lugar_laborado']); ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label fw-bold">Periodo de tiempo (meses):</label>
                                                    <input type="text" class="form-control" name="periodo_tiempo[]" value="<?php echo htmlspecialchars($exp['periodo_tiempo_meses']); ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label fw-bold">Funciones realizadas:</label>
                                                    <input type="text" class="form-control" name="funciones_realizadas[]" value="<?php echo htmlspecialchars($exp['funciones_realizadas']); ?>" required>
                                                </div>

                                                <?php if ($index > 0): ?>
                                                    <button type="button" class="btn btn-sm eliminar-experiencia" style="background: #df1f1f;">Eliminar</button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Si no hay experiencias, se muestra un campo vacío -->
                                        <div class="experiencia-laboral border p-3 mb-3 rounded">
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">Últimos lugares donde ha laborado:</label>
                                                <input type="text" class="form-control" name="lugar_laborado[]" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">Periodo de tiempo (meses):</label>
                                                <input type="text" class="form-control" name="periodo_tiempo[]" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">Funciones realizadas:</label>
                                                <input type="text" class="form-control" name="funciones_realizadas[]" required>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <button type="button" class="btn btn-sm mt-2" id="agregar-experiencia">Agregar más experiencia</button>
                            </div>
                        </div>

                        <div class="text-center mt-5 d-flex justify-content-center align-items-center gap-3">
                            <button type="submit" class="btn">Actualizar</button>
                            <a href="for-uno.php" class="btn" id="cancelar-btn">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
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
    <script src="../js/expLaboralEditar.js"></script>
    <script src="../js/toast.js"></script>
    
</body>

</html>