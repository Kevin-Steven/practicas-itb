<?php
session_start();
require '../config/config.php';
require 'sidebar-admin.php';
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio - Administrador</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <?php renderLayoutAdmin($primer_nombre, $primer_apellido, $foto_perfil); ?>


    <!-- Content -->
    <div class="content" id="content">
        <div class="container-fluid py-3">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h1 class="display-4 mb-3 fw-bold">Bienvenido a tu panel de administración</h1>
                    <p class="lead mb-5">Desde este panel podrás gestionar usuarios, modificar roles relacionados con el proceso de titulación.</p>

                    <!-- Cards con acciones rápidas -->
                    <div class="row justify-content-center">

                        <div class="col-md-6 mb-3">
                            <div class="card card-principal h-100 shadow">
                                <div class="card-body text-center">
                                    <i class='bx bx-user bx-lg mb-3'></i>
                                    <h5 class="card-title">Modificar Rol</h5>
                                    <p class="card-text">Cambia el rol de los usuarios registrados.</p>
                                    <a href="modificar-rol.php" class="btn">Acceder</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card card-principal h-100 shadow">
                                <div class="card-body text-center">
                                    <i class='bx bx-lock bx-lg mb-3'></i>
                                    <h5 class="card-title">Restaurar clave</h5>
                                    <p class="card-text">Modifica la clave de los postulantes.</p>
                                    <a href="restaurar-claves.php" class="btn">Acceder</a>
                                </div>
                            </div>
                        </div>
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