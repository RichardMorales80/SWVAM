<?php
session_start();
require '../config/Conexion.php';

/* 🔐 SOLO ADMIN */
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../public/login.php");
    exit;
}

/* ===== SANEAMIENTO ===== */
function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

$id_usuario       = intval($_POST['id_usuario'] ?? 0);
$primer_nombre    = limpiar($_POST['primer_nombre'] ?? '');
$primer_apellido  = limpiar($_POST['primer_apellido'] ?? '');
$correo           = filter_var($_POST['correo'] ?? '', FILTER_SANITIZE_EMAIL);
$telefono         = limpiar($_POST['telefono'] ?? '');
$lugar_residencia = limpiar($_POST['lugar_residencia'] ?? '');
$id_rol           = intval($_POST['id_rol'] ?? 2);
$password         = $_POST['password'] ?? '';

$mensaje = '';
$tipo = '';
$redirigir = '../privadas/modificar_usuarios.php';

/* ===== VALIDACIONES ===== */
if (
    empty($id_usuario) ||
    empty($primer_nombre) ||
    empty($primer_apellido) ||
    empty($correo)
) {
    $mensaje = 'Datos incompletos';
    $tipo = 'error';
} else {

    try {
        $db = Conexion::conectar();

        /* ===== VERIFICAR EXISTENCIA ===== */
        $check = $db->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
        $check->execute([$id_usuario]);

        if ($check->rowCount() !== 1) {
            $mensaje = 'El usuario no existe';
            $tipo = 'error';
        } else {

            /* ===== UPDATE BASE ===== */
            $sql = "
                UPDATE usuarios SET
                    primer_nombre    = :primer_nombre,
                    primer_apellido  = :primer_apellido,
                    correo           = :correo,
                    telefono         = :telefono,
                    lugar_residencia = :lugar_residencia,
                    id_rol           = :id_rol
            ";

            /* 👉 SI SE ESCRIBE CONTRASEÑA */
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
            }

            $sql .= " WHERE id_usuario = :id_usuario";

            $stmt = $db->prepare($sql);

            $stmt->bindParam(':primer_nombre', $primer_nombre);
            $stmt->bindParam(':primer_apellido', $primer_apellido);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':lugar_residencia', $lugar_residencia);
            $stmt->bindParam(':id_rol', $id_rol);
            $stmt->bindParam(':id_usuario', $id_usuario);

            if (!empty($password)) {
                $stmt->bindParam(':password', $hash);
            }

            if ($stmt->execute()) {
                $mensaje = 'Usuario actualizado correctamente';
                $tipo = 'success';
            } else {
                $mensaje = 'No se pudo actualizar el usuario';
                $tipo = 'error';
            }
        }

    } catch (PDOException $e) {
        $mensaje = 'Error de base de datos';
        $tipo = 'error';
    }
}
?>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({
        icon: "<?= $tipo ?>",
        title: "<?= $tipo === 'success' ? 'Éxito' : 'Error' ?>",
        text: "<?= $mensaje ?>",
        confirmButtonText: "Aceptar"
    }).then(() => {
        window.location.href = "<?= $redirigir ?>";
    });
});
</script>
