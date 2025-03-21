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

$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../images/user.png';

$usuario_id = $_SESSION['usuario_id'];

if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>
    <?php renderSidebarEstudiante($primer_nombre, $primer_apellido, $foto_perfil); ?>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container-fluid py-2">
            <div class="row justify-content-center">
                <div class="col-md-10 text-center">
                    <!-- Título y mensaje principal -->
                    <h1 class="display-4 fw-bold mb-4">¡Bienvenido/a, Practicante!</h1>
                    <p class="lead mb-4">Este es tu panel informativo del <strong>Instituto Superior Tecnológico Bolivariano de Tecnología</strong>.</p>
                    <p class="mb-5">Aquí puedes consultar el estado de tus prácticas profesionales y mantenerte al tanto de tu progreso.</p>

                    <!-- Cards informativas -->
                    <div class="row justify-content-center">
                        <!-- Estado del proceso -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow border-0">
                                <div class="card-body text-center">
                                    <i class='bx bx-task bx-lg mb-3'></i>
                                    <h5 class="card-title mb-3">Estado del Proceso</h5>
                                    <p class="card-text text-muted">
                                        Actualmente estás en la etapa de <strong>Seguimiento de Actividades</strong>. Sigue las indicaciones de tu tutor.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Documentos entregados -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow border-0">
                                <div class="card-body text-center">
                                    <i class='bx bx-folder-open bx-lg mb-3'></i>
                                    <h5 class="card-title mb-3">Documentos Entregados</h5>
                                    <p class="card-text text-muted">
                                        Has entregado <strong>4 de 6</strong> documentos requeridos. Revisa tus pendientes en el apartado de documentos.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Horas completadas -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow border-0">
                                <div class="card-body text-center">
                                    <i class='bx bx-time-five bx-lg mb-3'></i>
                                    <h5 class="card-title mb-3">Horas Completadas</h5>
                                    <p class="card-text text-muted">
                                        Llevas acumuladas <strong>120 de 240</strong> horas de prácticas profesionales. ¡Sigue así!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje motivacional -->
                    <div class="mt-2">
                        <p class="text-muted fst-italic">"El futuro pertenece a quienes se preparan hoy".</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php renderFooterAdmin(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>

</body>

</html>