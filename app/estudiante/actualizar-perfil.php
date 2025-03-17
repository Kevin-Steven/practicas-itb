<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nombres = trim(mysqli_real_escape_string($conn, $_POST['nombres']));  
  $apellidos = trim(mysqli_real_escape_string($conn, $_POST['apellidos']));
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $cedula = $_POST['cedula'];
  $telefono = $_POST['telefono'];
  $convencional = $_POST['convencional'];

  // Validación de número de teléfono y convencional
  if (strlen($telefono) != 10) {
    header("Location: perfil.php?status=invalid_phone");
    exit();
  }

  // Verificar si se ha subido una imagen de perfil
  if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $target_dir = "../photos/";
    $foto_perfil = $target_dir . basename($_FILES["foto_perfil"]["name"]);
    move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $foto_perfil);

    // Actualizar la variable de sesión con la nueva foto
    $_SESSION['usuario_foto'] = $foto_perfil;
  } else {
    $foto_perfil = $_POST['foto_actual']; // Mantener la foto actual si no se sube una nueva
  }

  // Verificar si los datos han cambiado
  $sql = "SELECT nombres, apellidos, email, telefono, convencional, foto_perfil FROM usuarios WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $usuario_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $usuario_actual = $result->fetch_assoc();

  if (
    $nombres === $usuario_actual['nombres'] &&
    $apellidos === $usuario_actual['apellidos'] &&
    $email === $usuario_actual['email'] &&
    $telefono === $usuario_actual['telefono'] &&
    $convencional === $usuario_actual['convencional'] &&
    $foto_perfil === $usuario_actual['foto_perfil']
  ) {
    // No se han realizado cambios
    header("Location: perfil.php?status=no_changes");
    exit();
  }

  // Actualizar los datos en la base de datos
  $sql_update = "UPDATE usuarios SET nombres=?, apellidos=?, email=?, telefono=?, convencional=?, foto_perfil=? WHERE id=?";
  $stmt_update = $conn->prepare($sql_update);
  $stmt_update->bind_param("ssssssi", $nombres, $apellidos, $email, $telefono, $convencional, $foto_perfil, $usuario_id);

  if ($stmt_update->execute()) {
    $_SESSION['usuario_nombre'] = $nombres;
    $_SESSION['usuario_apellido'] = $apellidos;
    
    header("Location: perfil.php?status=success");
    exit();
  } else {
    header("Location: perfil.php?status=error");
    exit();
  }

  $stmt_update->close();
}

$conn->close();
?>
