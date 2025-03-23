<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombres = trim(mysqli_real_escape_string($conn, $_POST['nombres']));
    $apellidos = trim(mysqli_real_escape_string($conn, $_POST['apellidos']));
    $correo = trim(mysqli_real_escape_string($conn, $_POST['correo']));
    $cedula = $_POST['cedula'];
    $direccion = trim(mysqli_real_escape_string($conn, $_POST['direccion']));
    $telefono = $_POST['telefono'];
    $convencional = $_POST['convencional'];
    $carrera_id = intval($_POST['carrera_id']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $periodo = mysqli_real_escape_string($conn, $_POST['periodo']);
    $curso_id = !empty($_POST['curso_id']) ? intval($_POST['curso_id']) : NULL;

    $nombres = mb_strtolower($nombres, 'UTF-8');
    $apellidos = mb_strtolower($apellidos, 'UTF-8');
  
    // Luego convertir la primera letra de cada palabra a mayúscula
    $nombres = mb_convert_case($nombres, MB_CASE_TITLE, "UTF-8");
    $apellidos = mb_convert_case($apellidos, MB_CASE_TITLE, "UTF-8");

    if (strlen($cedula) != 10 || !ctype_digit($cedula)) {
        $_SESSION['mensaje'] = "La cédula debe tener exactamente 10 dígitos.";
        $_SESSION['tipo'] = "danger";
        header("Location: registro.php");
        exit();
    }

    if (strlen($telefono) != 10 || !ctype_digit($telefono)) {
        $_SESSION['mensaje'] = "El número de teléfono debe tener exactamente 10 dígitos.";
        $_SESSION['tipo'] = "danger";
        header("Location: registro.php");
        exit();
    }

    // Validar que el correo y la cédula no estén registrados previamente
    $sql_check_user = "SELECT * FROM usuarios WHERE email = ? OR cedula = ?";
    $stmt = $conn->prepare($sql_check_user);
    $stmt->bind_param("ss", $correo, $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing_user = $result->fetch_assoc();
        if ($existing_user['email'] === $correo) {
            $_SESSION['mensaje'] = "Este correo ya está registrado. Intenta con otro.";
        } elseif ($existing_user['cedula'] === $cedula) {
            $_SESSION['mensaje'] = "Esta cédula ya está registrada. Intenta con otra.";
        }
        $_SESSION['tipo'] = "danger";
        header("Location: registro.php");
        exit();
    }

    // 4. Hash de la contraseña
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insertar el nuevo usuario con carrera_id y curso_id
    $sql_insert = "INSERT INTO usuarios (nombres, apellidos, email, cedula, direccion, telefono, convencional, carrera_id, curso_id, password, periodo)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param(
        "sssssssiiss",
        $nombres,
        $apellidos,
        $correo,
        $cedula,
        $direccion,
        $telefono,
        $convencional,
        $carrera_id,
        $curso_id,
        $password_hashed,
        $periodo
    );

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Usuario registrado exitosamente. Ahora puedes iniciar sesión.";
        $_SESSION['tipo'] = "success";
        header("Location: ../../index.php");
        exit();
    } else {
        $_SESSION['mensaje'] = "Error al registrar el usuario: " . $conn->error;
        $_SESSION['tipo'] = "danger";
        header("Location: registro.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
