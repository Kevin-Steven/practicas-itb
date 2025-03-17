<?php
session_start();  
require '../config/config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombres = trim(mysqli_real_escape_string($conn, $_POST['nombres']));  
    $apellidos = trim(mysqli_real_escape_string($conn, $_POST['apellidos']));
    $correo = mysqli_real_escape_string($conn, $_POST['correo']);
    $cedula = $_POST['cedula'];
    $direccion = mysqli_real_escape_string($conn, $_POST['direccion']);
    $telefono = $_POST['telefono'];
    $convencional = $_POST['convencional'];
    $carrera = mysqli_real_escape_string($conn, $_POST['carrera']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $periodo = mysqli_real_escape_string($conn, $_POST['periodo']);

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
        // Verificar cuál de los dos campos está duplicado
        $existing_user = $result->fetch_assoc();
        if ($existing_user['email'] === $correo) {
            $_SESSION['mensaje'] = "Este correo ya está registrado. Intenta con otro.";
            $_SESSION['tipo'] = "danger";
        } elseif ($existing_user['cedula'] === $cedula) {
            $_SESSION['mensaje'] = "Esta cédula ya está registrada. Intenta con otra.";
            $_SESSION['tipo'] = "danger";
        }
        header("Location: registro.php");
        exit();
    } else {
        // Encriptar la contraseña
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el nuevo usuario en la base de datos
        $sql = "INSERT INTO usuarios (nombres, apellidos, email, cedula, direccion, telefono, convencional, carrera, password, periodo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $nombres, $apellidos, $correo, $cedula, $direccion, $telefono, $convencional, $carrera, $password_hashed, $periodo);

        if ($stmt->execute()) {
            $_SESSION['tipo'] = "success";
            header("Location: ../../index.php");
            exit();
        } else {
            $_SESSION['mensaje'] = "Error al registrar: " . $conn->error;
            $_SESSION['tipo'] = "danger";
            header("Location: registro.php");
            exit();
        }
    }

    $stmt->close();
    $conn->close();
}
