<?php
session_start();
require '../config/config.php';
require 'sidebar-estudiante.php';
require '../admin/sidebar-admin.php';

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
    <title>Ficha de Estudiante</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>
    <?php renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil); ?>

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

    <!-- Content -->
    <div class="content" id="content">
        <div class="container">
            <h1 class="mb-2 text-center fw-bold">Actualizar Datos</h1>

            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-ocho-actualizar.php" class="inscripcion" method="POST" enctype="multipart/form-data">

                        <input type="hidden" name="documento_id" value="<?php echo htmlspecialchars($documento_uno_id); ?>">

                        <div class="container">
                            <!-- Fila principal -->
                            <div class="row g-4">

                                <!-- Columna 1: Datos Generales -->
                                <div class="col-md-4">
                                    <h2 class="card-title text-center mb-4">Datos Generales</h2>

                                    <div class="mb-3">
                                        <label for="nombres" class="form-label fw-bold">Nombres</label>
                                        <input type="text" class="form-control" id="nombres" value="<?php echo htmlspecialchars($nombres); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="apellidos" class="form-label fw-bold">Apellidos</label>
                                        <input type="text" class="form-control" id="apellidos" value="<?php echo htmlspecialchars($apellidos); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="cedula" class="form-label fw-bold">Cédula</label>
                                        <input type="text" class="form-control" id="cedula" value="<?php echo htmlspecialchars($cedula); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="direccion" class="form-label fw-bold">Dirección</label>
                                        <input type="text" class="form-control" id="direccion" value="<?php echo htmlspecialchars($direccion); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="telefono" class="form-label fw-bold">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" value="<?php echo htmlspecialchars($telefono); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="convencional" class="form-label fw-bold">Teléfono Convencional</label>
                                        <input type="text" class="form-control" id="convencional" value="<?php echo htmlspecialchars($convencional); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-bold">Correo Electrónico</label>
                                        <input type="text" class="form-control" id="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
                                    </div>
                                </div>

                                <!-- Columna 2: Datos Académicos -->
                                <div class="col-md-4">
                                    <h2 class="card-title text-center mb-4">Datos Académicos</h2>

                                    <div class="mb-3">
                                        <label for="carrera" class="form-label fw-bold">Carrera</label>
                                        <input type="text" class="form-control" id="carrera" value="<?php echo htmlspecialchars($carrera); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="periodo" class="form-label fw-bold">Periodo</label>
                                        <input type="text" class="form-control" id="periodo" value="<?php echo htmlspecialchars($periodo); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="paralelo" class="form-label fw-bold">Paralelo</label>
                                        <input type="text" class="form-control" id="paralelo" name="paralelo" value="<?php echo htmlspecialchars($paralelo); ?>" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="promedio" class="form-label fw-bold">Promedio de notas:</label>
                                        <input type="number" class="form-control" id="promedio" name="promedio" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($promedio_notas); ?>" required>
                                    </div>
                                </div>

                                <!-- Columna 3: Experiencia Laboral -->
                                <div class="col-md-4">
                                    <h2 class="card-title text-center mb-4">Experiencia Laboral</h2>

                                    <div id="contenedor-experiencia">
                                        <?php if (!empty($experiencia_laboral)): ?>
                                            <?php foreach ($experiencia_laboral as $index => $exp): ?>
                                                <div class="experiencia-laboral border p-3 mb-3 rounded">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Últimos lugares donde ha laborado:</label>
                                                        <input type="text" class="form-control" name="lugar_laborado[]" value="<?php echo htmlspecialchars($exp['lugar_laborado']); ?>">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Periodo de tiempo (meses):</label>
                                                        <input type="text" class="form-control" name="periodo_tiempo[]" value="<?php echo htmlspecialchars($exp['periodo_tiempo_meses']); ?>">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Funciones realizadas:</label>
                                                        <input type="text" class="form-control" name="funciones_realizadas[]" value="<?php echo htmlspecialchars($exp['funciones_realizadas']); ?>">
                                                    </div>

                                                    <?php if ($index > 0): ?>
                                                        <button type="button" class="btn btn-sm eliminar-experiencia" style="background: #df1f1f; color: #fff;">Eliminar</button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <!-- Si no hay experiencias, muestra un campo vacío -->
                                            <div class="experiencia-laboral border p-3 mb-3 rounded">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Últimos lugares donde ha laborado:</label>
                                                    <input type="text" class="form-control" name="lugar_laborado[]">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Periodo de tiempo (meses):</label>
                                                    <input type="text" class="form-control" name="periodo_tiempo[]">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Funciones realizadas:</label>
                                                    <input type="text" class="form-control" name="funciones_realizadas[]">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-grid">
                                        <button type="button" class="btn btn-primary btn-sm mt-2" id="agregar-experiencia">Agregar más experiencia</button>
                                    </div>
                                </div>

                            </div>

                            <!-- Botones -->
                            <div class="text-center mt-5 d-flex justify-content-center align-items-center gap-3">
                                <button type="submit" class="btn">Actualizar</button>
                                <a href="for-uno.php" id="cancelar-btn" class="btn ">Cancelar</a>
                            </div>
                        </div>

                    </form>


                </div>
            </div>
        </div>
    </div>

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/expLaboralEditar.js"></script>
    <script src="../js/toast.js"></script>

</body>

</html>