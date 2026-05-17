<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/Conexion.php';

$pdo = Conexion::conectar();

/* =========================
   OBTENER CORREO
========================= */

$correo = trim($_POST['correo'] ?? '');

if(empty($correo)){
    exit('Debes ingresar un correo');
}

/* =========================
   BUSCAR USUARIO
========================= */

$sql = "SELECT id_usuario
        FROM usuarios
        WHERE correo = ?
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([$correo]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$usuario){
    exit('Este correo no está registrado');
}

/* =========================
   GENERAR TOKEN
========================= */

$token = bin2hex(random_bytes(32));

$expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

/* =========================
   GUARDAR TOKEN
========================= */

$update = $pdo->prepare("
    UPDATE usuarios
    SET token_recuperacion = ?,
        token_expira = ?
    WHERE correo = ?
");

$update->execute([
    $token,
    $expira,
    $correo
]);

/* =========================
   CREAR LINK
========================= */

$link = "https://morqui.org/templetes/nueva_password.php?token=" . $token;

?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

window.top.Swal.fire({

    icon: 'success',

    title: 'Correo enviado',

    text: 'Da clic en el botón para cambiar tu contraseña.',

    showCancelButton: true,

    confirmButtonText: 'Cambiar contraseña',

    cancelButtonText: 'Cerrar',

    confirmButtonColor: '#2563eb',

    cancelButtonColor: '#6b7280',

    allowOutsideClick: false,

    backdrop: true

}).then((result) => {

    // SI PRESIONA CAMBIAR CONTRASEÑA
    if(result.isConfirmed){

        // REDIRECCIONAR A NUEVA PASSWORD
        window.top.location.href =
        "<?php echo $link; ?>";
    }

    // SI CIERRA
    else{

        // RECARGAR
        window.top.location.href =
        "https://morqui.org/index.php";
    }

});

</script>