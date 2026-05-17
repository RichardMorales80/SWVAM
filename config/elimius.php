<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require '../config/Conexion.php';

/* =========================
   PROTECCIÓN
========================= */
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    die("Acceso denegado");
}

/* =========================
   VALIDAR POST
========================= */
if (empty($_POST['id_usuario'])) {
    die("ID no recibido");
}

$id_usuario = intval($_POST['id_usuario']);
$base = Conexion::conectar();

/* =========================
   NO DESACTIVAR ADMIN PRINCIPAL
========================= */
$adminProtegido = "richardmr77@gmail.com";

$check = $base->prepare("
    SELECT correo 
    FROM usuarios 
    WHERE id_usuario = ?
");
$check->execute([$id_usuario]);
$usuario = $check->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $mensaje = "El usuario no existe";
    $tipo = "error";

} elseif ($usuario['correo'] === $adminProtegido) {
    $mensaje = "No puedes desactivar al administrador principal";
    $tipo = "error";

} else {

    try {
        //  INICIAR TRANSACCIÓN
        $base->beginTransaction();

        /* =========================
           DESACTIVAR SOLO USUARIO
        ========================= */
        $update = $base->prepare("
            UPDATE usuarios 
            SET estado = 0
            WHERE id_usuario = ?
        ");
        $update->execute([$id_usuario]);

        
        $base->commit();

        $mensaje = "Usuario desactivado correctamente";
        $tipo = "success";

    } catch (Exception $e) {

        
        $base->rollBack();

        $mensaje = "Error al desactivar: " . $e->getMessage();
        $tipo = "error";
    }
}

$redirigir = "../privadas/modificar_usuarios.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
Swal.fire({
    icon: "<?= $tipo ?>",
    title: "<?= $tipo === 'success' ? 'Éxito' : 'Error' ?>",
    text: "<?= $mensaje ?>",
    confirmButtonText: "Aceptar"
}).then(() => {
    window.location.href = "<?= $redirigir ?>";
});
</script>

</body>
</html>