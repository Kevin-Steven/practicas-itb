<?php
session_start();
require '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $doc_id = $_POST['id_documento'] ?? null;
  $accion = $_POST['accion'] ?? null;
  $tipo = $_POST['tipo_documento'] ?? null;
  $motivo_rechazo = $_POST['motivo_rechazo'] ?? null;

  if (!$doc_id || !$accion || !$tipo) {
    header("Location: ../detalle-documentos.php?status=error");
    exit();
  }

  // Definir tabla
  $tabla = '';
  if ($tipo === 'uno') {
    $tabla = 'documento_uno';
  } elseif ($tipo === 'dos') {
    $tabla = 'documento_dos';
  } else {
    header("Location: ../detalle-documentos.php?status=error_tipo");
    exit();
  }

  // Acción: aprobar o corregir
  if ($accion === 'aprobar') {
    $nuevoEstado = 'Aprobado';
    $motivo_rechazo = null; // Se limpia el motivo de rechazo
  } elseif ($accion === 'corregir') {
    $nuevoEstado = 'Corregir';

    if (!$motivo_rechazo || trim($motivo_rechazo) === '') {
      header("Location: ../detalle-documentos.php?status=error_motivo");
      exit();
    }
  } else {
    header("Location: ../detalle-documentos.php?status=error_accion");
    exit();
  }

  // Actualizar estado y motivo
  $sql = "UPDATE $tabla SET estado = ?, motivo_rechazo = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssi", $nuevoEstado, $motivo_rechazo, $doc_id);

  if (!$stmt->execute()) {
    header("Location: ../detalle-documentos.php?status=db_error");
    exit();
  }

  // Recuperar el usuario_id desde el documento actualizado
  $sql_get_user = "SELECT usuario_id FROM $tabla WHERE id = ?";
  $stmt_user = $conn->prepare($sql_get_user);
  $stmt_user->bind_param("i", $doc_id);
  $stmt_user->execute();
  $result_user = $stmt_user->get_result();

  if ($result_user->num_rows === 0) {
    header("Location: ../detalle-documentos.php?status=no_user");
    exit();
  }

  $user_data = $result_user->fetch_assoc();
  $usuario_id = $user_data['usuario_id'];

  $stmt->close();
  $stmt_user->close();
  $conn->close();

  // Redirige correctamente al detalle con el ID del estudiante
  header("Location: ../detalle-documentos.php?id=$usuario_id&status=success");
  exit();
}
?>
