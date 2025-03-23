<?php 
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cedula_o_nombre = $_POST['cedula']; // Campo que acepta cédula o nombres
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Consulta para buscar por cedula o nombres
    $sql = "SELECT * FROM usuarios WHERE cedula = ? OR nombres = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $cedula_o_nombre, $cedula_o_nombre);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $usuario = $result->fetch_assoc();

        // ✅ Verificamos si el usuario está inactivo
        if ($usuario['estado'] === 'inactivo') {
            $_SESSION['error'] = "Su cuenta ha sido inhabilitada. Contacte al administrador.";
            header("Location: ../../index.php");
            exit();
        }

        // ✅ Verificamos la contraseña
        if (password_verify($password, $usuario['password'])) {

            // ✅ Creamos sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombres'];
            $_SESSION['usuario_apellido'] = $usuario['apellidos'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_foto'] = $usuario['foto_perfil'] ? $usuario['foto_perfil'] : '../../images/user.png';

            // ✅ Redirecciona según el rol
            switch ($usuario['rol']) {
                case 'gestor':
                    header("Location: ../gestor/inicio-gestor.php");
                    break;
                case 'administrador':
                    header("Location: ../admin/inicio-administrador.php");
                    break;
                default:
                    header("Location: ../estudiante/inicio-estudiante.php");
                    break;
            }

            exit();

        } else {
            $_SESSION['error'] = "Contraseña incorrecta.";
            header("Location: ../../index.php");
            exit();
        }

    } else {
        $_SESSION['error'] = "No existe una cuenta con esa cédula o usuario.";
        header("Location: ../../index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
