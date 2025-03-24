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
    d3.departamento_entidad_receptora,

    ia.semana_inicio,
    ia.semana_fin,
    ia.horas_realizadas,
    ia.actividades_realizadas

FROM usuarios u
LEFT JOIN documento_ocho d8 ON d8.usuario_id = u.id
LEFT JOIN informe_actividades ia ON d8.id = ia.documento_ocho_id
LEFT JOIN documento_tres d3 ON u.id = d3.usuario_id
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
            'departamento' => $row['departamento_entidad_receptora']
        ];
    }

    if (!empty($row['semana_inicio'])) {
        $informe_actividades[] = [
            'semana_inicio' => $row['semana_inicio'],
            'semana_fin' => $row['semana_fin'],
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
                                    <input type="text" class="form-control" id="departamento" name="departamento" placeholder="ej. Departamento de Sistemas" value="<?php echo htmlspecialchars($usuario_info['departamento']); ?>" disabled>
                                </div>

                                <input type="hidden" name="usuario_id" value="<?php echo $usuario_info['id']; ?>">
                            </div>

                            <!-- Columna Derecha -->
                            <div class="col-md-6">
                                <h2 class="card-title text-center mb-4">Registro de actividades</h2>

                                <div id="contenedor-semana">
                                    <?php if (!empty($informe_actividades)): ?>
                                        <?php foreach ($informe_actividades as $index => $exp): ?>
                                            <div class="semana border p-3 mb-3 rounded">
                                                <div class="mb-2">
                                                    <label for="semana" class="form-label fw-bold">Semanas/Fecha:</label>
                                                    <div class="d-flex gap-2">
                                                        <input type="date" class="form-control" name="semana_inicio[]" required value="<?php echo htmlspecialchars($exp['semana_inicio']); ?>">
                                                        <input type="date" class="form-control" name="semana_fin[]" required value="<?php echo htmlspecialchars($exp['semana_fin']); ?>">
                                                    </div>
                                                </div>

                                                <div class="mb-2">
                                                    <label for="horas_realizadas" class="form-label fw-bold">Horas realizadas:</label>
                                                    <input type="number" class="form-control" name="horas_realizadas[]" placeholder="ej. 30" required value="<?php echo htmlspecialchars($exp['horas_realizadas']); ?>">
                                                </div>

                                                <div class="mb-2">
                                                    <label for="actividades_realizadas" class="form-label fw-bold">Actividades realizadas:</label>
                                                    <textarea class="form-control" rows="2" name="actividades_realizadas[]" placeholder="ej. Integración del software de biométrico..." required><?php echo htmlspecialchars($exp['actividades_realizadas']); ?></textarea>
                                                </div>

                                                <?php if ($index > 0): ?>
                                                    <button type="button" class="btn btn-sm eliminar-semana" style="background: #df1f1f;">Eliminar</button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Si no hay experiencias, se muestra un campo vacío -->
                                        <div class="experiencia-laboral border p-3 mb-3 rounded">
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
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-grid">
                                    <button type="button" class="btn btn-sm mt-2" id="agregar-semana">Agregar semana</button>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                            <button type="submit" class="btn">Actualizar Datos</button>

                            <?php if (!empty($motivo_rechazo)): ?>
                                <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalMotivoRechazo">
                                    Ver Motivo de Rechazo
                                </a>
                            <?php endif; ?>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/semanaFechaEditar.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>