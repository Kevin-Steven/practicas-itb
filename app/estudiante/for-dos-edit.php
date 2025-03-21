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

$sql_doc_dos = "SELECT 
       d2.id,
       d2.estado, 
       d2.fecha_inicio,
       d2.hora_inicio,
       d2.fecha_fin,
       d2.hora_fin,
       d2.documento_eva_s,
       d2.hora_practicas,
       d2.nota_eva_s,
       d2.nombre_tutor_academico,
       d2.cedula_tutor_academico,
       d2.correo_tutor_academico
FROM documento_dos d2
WHERE d2.usuario_id = ?
ORDER BY d2.id DESC";

$stmt_doc_dos = $conn->prepare($sql_doc_dos);
$stmt_doc_dos->bind_param("i", $usuario_id);
$stmt_doc_dos->execute();
$result_tema = $stmt_doc_dos->get_result();

while ($row = $result_tema->fetch_assoc()) {
    $id = $row['id'] ?? null;
    $estado = $row['estado'] ?? null;
    $fecha_inicio = $row['fecha_inicio'] ?? null;
    $hora_inicio = $row['hora_inicio'] ?? null;
    $fecha_fin = $row['fecha_fin'] ?? null;
    $hora_fin = $row['hora_fin'] ?? null;
    $documento_eva_s = $row['documento_eva_s'] ?? null;
    $horas_practicas = $row['hora_practicas'] ?? null;
    $nota_eva_s = $row['nota_eva_s'] ?? null;
    $nombre_tutor_academico = $row['nombre_tutor_academico'] ?? null;
    $cedula_tutor_academico = $row['cedula_tutor_academico'] ?? null;
    $correo_tutor_academico = $row['correo_tutor_academico'] ?? null;
}

$stmt_doc_dos->close();


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
            <h1 class="mb-2 text-center fw-bold">Datos Generales del Estudiante</h1>

            <div class="card shadow-lg container-fluid">
                <div class="card-body">
                    <form action="../estudiante/logic/documento-dos-actualizar.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <h2 class="card-title text-center">Periodo Práctica <br>Preprofesional</h2>
                                <div class="mb-2">
                                    <label for="fecha_inicio" class="form-label fw-bold">Fecha Inicio:</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="hora_inicio" class="form-label fw-bold">Hora Inicio:</label>
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="<?php echo $hora_inicio; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="fecha_fin" class="form-label fw-bold">Fecha Fin:</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="hora_fin" class="form-label fw-bold">Hora Fin:</label>
                                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="<?php echo $hora_fin; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="horas_practicas" class="form-label fw-bold">Horas Prácticas:</label>
                                    <input type="number" class="form-control" id="horas_practicas" name="horas_practicas" step="0.01" placeholder="ej. 240" value="<?php echo $horas_practicas; ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h2 class="card-title text-center">Resultados Extraídos del EVA-S del Diagnóstico Inicial</h2>
                                <div class="mb-2">
                                    <label for="eva-s" class="form-label fw-bold">Subir EVA-S:</label>
                                    <input type="file" class="form-control" id="eva_s" name="eva_s" value="<?php echo $documento_eva_s; ?>">
                                    <a href="../uploads/eva-s/<?php echo $documento_eva_s; ?>" target="_blank">
                                        Ver Imagen
                                    </a>
                                </div>
                                <div class="mb-2">
                                    <label for="nota_eva-s" class="form-label fw-bold">Nota EVA-S:</label>
                                    <input type="number" min="0" max="100" class="form-control" id="nota_eva-s" name="nota_eva-s" step="0.01" placeholder="ej. 100" value="<?php echo $nota_eva_s; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="tutor_academico" class="form-label fw-bold">Tutor Académico:</label>
                                    <input type="text" class="form-control" id="tutor_academico" name="tutor_academico" placeholder="ej. Juan Carlos Pérez Mora" value="<?php echo $nombre_tutor_academico; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="cedula_tutor" class="form-label fw-bold">Cédula del tutor:</label>
                                    <input type="text" class="form-control" id="cedula_tutor" name="cedula_tutor" placeholder="ej. 1234567890" value="<?php echo $cedula_tutor_academico; ?>">
                                </div>
                                <div class="mb-2">
                                    <label for="correo_tutor" class="form-label fw-bold">Correo electrónico del tutor:</label>
                                    <input type="email" class="form-control" id="correo_tutor" name="correo_tutor" placeholder="ej. tutor@gmail.com" value="<?php echo $correo_tutor_academico; ?>">
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