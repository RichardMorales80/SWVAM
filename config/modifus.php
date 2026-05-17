<?php
//var_dump($_POST);
//exit;
require '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

/* ADMIN Y EMPLEADO */
verificarRoles([1,3]);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../public/login.php");
    exit;
}

/* ===== SANEAMIENTO ===== */
function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

/* ===== CAPTURAR DATOS DEL POST ===== */
$id_usuario         = intval($_POST['id_usuario'] ?? 0);
$primer_nombre      = limpiar($_POST['primer_nombre'] ?? '');
$primer_apellido    = limpiar($_POST['primer_apellido'] ?? '');
$segundo_apellido   = limpiar($_POST['segundo_apellido'] ?? '');
$correo             = filter_var($_POST['correo'] ?? '', FILTER_SANITIZE_EMAIL);
$telefono           = limpiar($_POST['telefono'] ?? '');
$id_rol             = intval($_POST['id_rol'] ?? 2);

$password           = $_POST['password'] ?? '';
$confirmar_password = $_POST['confirmar_password'] ?? '';

$id_direccion       = intval($_POST['id_direccion'] ?? 0);
$calle              = limpiar($_POST['calle'] ?? '');
$numero_exterior    = limpiar($_POST['numero_exterior'] ?? '');
$numero_interior    = limpiar($_POST['numero_interior'] ?? '');
$colonia            = limpiar($_POST['colonia'] ?? '');
$ciudad             = limpiar($_POST['ciudad'] ?? '');
$estado             = limpiar($_POST['estado'] ?? '');
$codigo_postal      = limpiar($_POST['codigo_postal'] ?? '');

$mensaje = '';
$tipo = '';
$redirigir = '../privadas/modificar_usuarios.php';

/* ===== VALIDACIONES ===== */
if (empty($id_usuario) || empty($primer_nombre) || empty($primer_apellido) || empty($correo)) {
    $mensaje = 'Datos incompletos';
    $tipo = 'error';
} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $mensaje = 'El correo no es válido';
    $tipo = 'error';
} elseif ((!empty($password) || !empty($confirmar_password)) && $password !== $confirmar_password) {
    $mensaje = 'La nueva contraseña y la confirmación no coinciden';
    $tipo = 'error';
} else {
    try {
        $db = Conexion::conectar();

        /* ===== VERIFICAR EXISTENCIA USUARIO ===== */
        $check = $db->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
        $check->execute([$id_usuario]);

        if ($check->rowCount() !== 1) {
            $mensaje = 'El usuario no existe';
            $tipo = 'error';
        } else {
            /* ===== VALIDAR CORREO REPETIDO EN OTRO USUARIO ===== */
            $checkCorreo = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?");
            $checkCorreo->execute([$correo, $id_usuario]);

            if ($checkCorreo->rowCount() > 0) {
                $mensaje = 'El correo ya está registrado por otro usuario';
                $tipo = 'error';
            } else {
                /* ===== ACTUALIZAR USUARIO ===== */
                $sql = "
                    UPDATE usuarios SET
                        primer_nombre    = :primer_nombre,
                        primer_apellido  = :primer_apellido,
                        segundo_apellido = :segundo_apellido,
                        correo           = :correo,
                        telefono         = :telefono,
                        id_rol           = :id_rol
                ";

                if (!empty($password)){
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $sql .= ", password = :password";
                }

                $sql .= " WHERE id_usuario = :id_usuario";

                $stmt = $db->prepare($sql);
                $stmt->bindParam(':primer_nombre', $primer_nombre);
                $stmt->bindParam(':primer_apellido', $primer_apellido);
                $stmt->bindParam(':segundo_apellido', $segundo_apellido);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':id_rol', $id_rol);
                $stmt->bindParam(':id_usuario', $id_usuario);

                if (!empty($password) && !empty($confirmar_password)) {
                    $stmt->bindParam(':password', $hash);
                }

                $stmt->execute();
                if($stmt->rowCount() > 0){
                     $mensaje = 'Usuario actualizado correctamente';
                      $tipo = 'success';
                       }else{
                        $mensaje = 'No se realizaron cambios';
                         $tipo = 'info';
                          }

                /* ===== ACTUALIZAR DIRECCIÓN ===== */
                if ($id_direccion > 0) {
                    $sqlDir = "
                        UPDATE direcciones SET
                            calle           = :calle,
                            numero_exterior = :numero_exterior,
                            numero_interior = :numero_interior,
                            colonia         = :colonia,
                            ciudad          = :ciudad,
                            estado          = :estado,
                            codigo_postal   = :codigo_postal
                        WHERE id_direccion = :id_direccion
                    ";
                    $stmtDir = $db->prepare($sqlDir);
                    $stmtDir->bindParam(':calle', $calle);
                    $stmtDir->bindParam(':numero_exterior', $numero_exterior);
                    $stmtDir->bindParam(':numero_interior', $numero_interior);
                    $stmtDir->bindParam(':colonia', $colonia);
                    $stmtDir->bindParam(':ciudad', $ciudad);
                    $stmtDir->bindParam(':estado', $estado);
                    $stmtDir->bindParam(':codigo_postal', $codigo_postal);
                    $stmtDir->bindParam(':id_direccion', $id_direccion);
                    $stmtDir->execute();
                }

                $mensaje = 'Usuario actualizado correctamente';
                $tipo = 'success';
            }
        }
    } catch (PDOException $e) {
        $mensaje = 'Error de base de datos: ' . $e->getMessage();
        $tipo = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar usuario</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
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
</body>
</html>