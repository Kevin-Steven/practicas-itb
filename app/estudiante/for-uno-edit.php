<?php
session_start();
require '../config/config.php';
require 'sidebar-estudiante.php';
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$usuario_id = $_SESSION['usuario_id'];

// ✅ Recibimos el documento id desde la URL
$documento_id = $_GET['id'] ?? null;

if (!$documento_id) {
    header("Location: for-uno.php?status=not_found");
    exit();
}

// ✅ Consulta mejorada para obtener toda la info (basada en for-uno.php)
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
WHERE d1.id = ? AND u.id = ?
ORDER BY d1.id DESC";

$stmt_doc_uno = $conn->prepare($sql_doc_uno);
$stmt_doc_uno->bind_param("ii", $documento_id, $usuario_id);
$stmt_doc_uno->execute();
$result_doc_uno = $stmt_doc_uno->get_result();

$usuario_info = null;
$experiencia_laboral = [];

// ✅ Recogemos la información de la consulta
while ($row = $result_doc_uno->fetch_assoc()) {
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

    // ✅ Agregar experiencia si la hay
    if (!empty($row['lugar_laborado'])) {
        $experiencia_laboral[] = [
            'lugar_laborado' => $row['lugar_laborado'],
            'periodo_tiempo_meses' => $row['periodo_tiempo_meses'],
            'funciones_realizadas' => $row['funciones_realizadas']
        ];
    }
}

$stmt_doc_uno->close();

// ✅ Validamos si no se encontró el documento
if (!$usuario_info) {
    header("Location: for-uno.php?status=not_found");
    exit();
}

// ✅ Asignamos a variables individuales para el HTML (esto pediste)
$usuario_id = $usuario_info['usuario_id'];
$nombres = $usuario_info['nombres'];
$apellidos = $usuario_info['apellidos'];
$cedula = $usuario_info['cedula'];
$direccion = $usuario_info['direccion'];
$telefono = $usuario_info['telefono'];
$convencional = $usuario_info['convencional'] ?: 'NO APLICA'; // fallback si está vacío
$email = $usuario_info['email'];
$periodo = $usuario_info['periodo'];
$carrera = $usuario_info['carrera'];
$paralelo = $usuario_info['paralelo'];
$documento_uno_id = $usuario_info['documento_uno_id'];
$estado = $usuario_info['estado'];
$promedio_notas = $usuario_info['promedio_notas'];
$motivo_rechazo = $usuario_info['motivo_rechazo'];
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
            <h1 class="mb-2 text-center fw-bold">Actualizar Datos</h1>

            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-uno-actualizar.php" class="inscripcion" method="POST" enctype="multipart/form-data">

                        <!-- Campo oculto para el ID del documento -->
                        <input type="hidden" name="documento_id" value="<?php echo htmlspecialchars($documento_uno_id); ?>">

                        <div class="row">
                            <!-- Datos generales -->
                            <div class="col-md-4">
                                <h2 class="card-title text-center">Datos Generales</h2>

                                <div class="mb-2">
                                    <label for="nombres" class="form-label fw-bold">Nombres</label>
                                    <input type="text" class="form-control" id="nombres" value="<?php echo htmlspecialchars($nombres); ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label for="apellidos" class="form-label fw-bold">Apellidos</label>
                                    <input type="text" class="form-control" id="apellidos" value="<?php echo htmlspecialchars($apellidos); ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label for="cedula" class="form-label fw-bold">Cédula</label>
                                    <input type="text" class="form-control" id="cedula" value="<?php echo htmlspecialchars($cedula); ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label for="direccion" class="form-label fw-bold">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" value="<?php echo htmlspecialchars($direccion); ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label for="telefono" class="form-label fw-bold">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" value="<?php echo htmlspecialchars($telefono); ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label for="convencional" class="form-label fw-bold">Teléfono Convencional</label>
                                    <input type="text" class="form-control" id="convencional" value="<?php echo htmlspecialchars($convencional); ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label for="email" class="form-label fw-bold">Correo Electrónico</label>
                                    <input type="text" class="form-control" id="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
                                </div>
                            </div>

                            <!-- Datos académicos -->
                            <div class="col-md-4">
                                <h2 class="card-title text-center">Datos Académicos</h2>

                                <div class="mb-2">
                                    <label for="carrera" class="form-label fw-bold">Carrera</label>
                                    <input type="text" class="form-control" id="carrera" value="<?php echo htmlspecialchars($carrera); ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label for="periodo" class="form-label fw-bold">Periodo</label>
                                    <input type="text" class="form-control" id="periodo" value="<?php echo htmlspecialchars($periodo); ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label for="paralelo" class="form-label fw-bold">Paralelo</label>
                                    <input type="text" class="form-control" id="paralelo" name="paralelo" value="<?php echo htmlspecialchars($paralelo); ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label for="promedio" class="form-label fw-bold">Promedio de notas:</label>
                                    <input type="number" class="form-control" id="promedio" name="promedio" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($promedio_notas); ?>" required>
                                </div>
                            </div>

                            <!-- Experiencia laboral -->
                            <div class="col-md-4">
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