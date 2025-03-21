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
       d6.horario_practica,
       d6.jornada_laboral,
       d6.nombres_representante,
       d6.cargo_tutor,
       d6.numero_practicas,
       d6.numero_telefono,
       d6.numero_institucional,
       d6.estado 
FROM documento_seis d6
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
    $horario_practica = $row['horario_practica'];
    $jornada_laboral = $row['jornada_laboral'];
    $nombres_representante = $row['nombres_representante'];
    $cargo_tutor = $row['cargo_tutor'];
    $numero_practicas = $row['numero_practicas'];
    $numero_telefono = $row['numero_telefono'];
    $numero_institucional = $row['numero_institucional'] ?? 'NO APLICA';
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

    <!-- Content -->
    <div class="content" id="content">
        <div class="container">
            <h1 class="mb-2 text-center fw-bold">Actualizar Ficha de la Entidad Receptora</h1>

            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-seis-actualizar.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <h2 class="card-title text-center mb-3">Datos de la Entidad Receptora</h2>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="actividad_economica" class="form-label fw-bold">Actividad económica principal:</label>
                                    <input type="text" class="form-control" id="actividad_economica" name="actividad_economica" placeholder="Ej: Comercio al por menor" value="<?php echo $actividad_economica; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="provincia" class="form-label fw-bold">Provincia:</label>
                                    <input type="text" class="form-control" id="provincia" name="provincia" placeholder="Ej: Guayas" value="<?php echo $provincia; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="horario_practica" class="form-label fw-bold">Horario de la práctica:</label>
                                    <input type="text" class="form-control" id="horario_practica" name="horario_practica" placeholder="Ej: 08:00 - 17:00" value="<?php echo $horario_practica; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="jornada_laboral" class="form-label fw-bold">Jornada laboral:</label>
                                    <select class="form-control" id="jornada_laboral" name="jornada_laboral">
                                        <option value="" disabled selected>Seleccionar</option>
                                        <option value="Lunes a viernes" <?php echo $jornada_laboral === 'Lunes a viernes' ? 'selected' : ''; ?>>Lunes a viernes</option>
                                        <option value="Lunes a sábado" <?php echo $jornada_laboral === 'Lunes a sábado' ? 'selected' : ''; ?>>Lunes a sábado</option>
                                        <option value="Completa" <?php echo $jornada_laboral === 'Completa' ? 'selected' : ''; ?>>Completa</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="numero_institucional" class="form-label fw-bold">Número institucional (opcional):</label>
                                    <input type="number" class="form-control" id="numero_institucional" name="numero_institucional" placeholder="No aplica" value="<?php echo $numero_institucional; ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="nombres-representante" class="form-label fw-bold">Nombres y Apellidos del tutor de la entidad receptora</label>
                                    <input type="text" class="form-control" id="nombres-representante" name="nombres-representante" placeholder="Ej: Juan Pérez" value="<?php echo $nombres_representante; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="cargo_tutor" class="form-label fw-bold">Cargo del tutor de la entidad receptora:</label>
                                    <input type="text" class="form-control" id="cargo_tutor" name="cargo_tutor" placeholder="Ej: Gerente" value="<?php echo $cargo_tutor; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="numero_practicas" class="form-label fw-bold">Número de prácticas:</label>
                                    <select class="form-control" id="numero_practicas" name="numero_practicas" required>
                                        <option value="" disabled selected>Seleccionar</option>
                                        <option value="Primera-Segunda-Tercera" <?php echo $numero_practicas === 'Primera-Segunda-Tercera' ? 'selected' : ''; ?>>Primera-Segunda-Tercera</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="numero_telefono" class="form-label fw-bold">Número de teléfono celular:</label>
                                    <input type="number" class="form-control" id="numero_telefono" name="numero_telefono" placeholder="Ej: 0987654321" value="<?php echo $numero_telefono; ?>">
                                </div>
                            </div>
                            <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">

                        </div>

                        <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                            <button type="submit" class="btn">Actualizar Datos</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/expLaboral.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>