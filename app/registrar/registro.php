<?php
session_start();
require '../config/config.php'; // O la ruta que tengas de tu conexión

require '../config/config.php';
$sql_carreras = "SELECT id, carrera FROM carrera WHERE estado = 'activo'";
$result_carreras = $conn->query($sql_carreras);

$sql_cursos = "SELECT id, paralelo FROM cursos WHERE estado = 'activo'";
$result_cursos = $conn->query($sql_cursos);

$conn->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="estilos.css" rel="stylesheet">
    <link rel="icon" href="../../images/favicon.png" type="image/png">
</head>

<body>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card py-2 px-4 shadow-lg" style="width: 100%; max-width: 850px;">
            <h2 class="text-center mb-4 fw-bold">Crea tu Cuenta</h2>

            <!-- Mostrar mensaje si existe -->
            <?php
            if (isset($_SESSION['mensaje'])):
            ?>
                <p class="alert alert-<?php echo $_SESSION['tipo']; ?> text-center">
                    <?php echo $_SESSION['mensaje']; ?>
                </p>
                <?php
                // Eliminar el mensaje después de mostrarlo
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo']);
                ?>
            <?php endif; ?>

            <form action="logica-registro.php" class="form-registro" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombres" class="form-label fw-bold">Nombres</label>
                        <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Ingrese sus nombres" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellidos" class="form-label fw-bold">Apellidos</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Ingrese sus apellidos" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cedula" class="form-label fw-bold">Cédula de identidad</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" maxlength="10" placeholder="Ingrese su Cédula" required oninput="validateInput(this)">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="direccion" class="form-label fw-bold">Dirección del domicilio</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Ingrese su dirección" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label fw-bold">Teléfono celular</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" maxlength="10" placeholder="Ingrese su número de teléfono" required oninput="validateInput(this)">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="convencional" class="form-label fw-bold">Teléfono convencional (opcional)</label>
                        <input type="text" class="form-control" id="convencional" name="convencional" maxlength="9" placeholder="Ingrese su número convencional">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Periodo académico (Nivel)</label>
                        <select class="form-select" name="periodo" id="periodo" required>
                            <option selected value="" disabled>Selecciona una opción</option>
                            <option value="4TO NIVEL">4TO NIVEL</option>
                            <option value="5TO NIVEL">5TO NIVEL</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="correo" class="form-label fw-bold">Correo electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="name@gmail.com" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Carrera</label>
                        <select class="form-select" name="carrera_id" id="carrera_id" required>
                            <option selected value="" disabled>Selecciona una carrera</option>
                            <?php while ($row = $result_carreras->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['carrera']; ?></option>
                            <?php endwhile; ?>
                        </select>

                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Curso (Paralelo)</label>
                        <select class="form-select" name="curso_id" id="curso_id">
                            <option selected value="">Selecciona un curso (opcional)</option>
                            <?php while ($row = $result_cursos->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['paralelo']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    
                </div>
                <div class="row">

                    <div class="col-md-6 mb-4">
                        <label for="password" class="form-label fw-bold">Contraseña</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Ingrese su clave" required>
                        </div>
                    </div>
                </div>


                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn w-50">Registrarse</button>
                </div>
            </form>
            <p class="text-center mt-3">
                ¿Ya tienes una cuenta? <a href="../../index.php" class="text-decoration-none">Iniciar Sesión</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/number.js" defer></script>
</body>

</html>