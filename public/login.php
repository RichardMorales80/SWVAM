
<?php
session_start();
require '../config/Conexion.php';

$alertas = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ===== SANEAMIENTO =====
    $correo  = filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
    $pass    = $_POST['pas'] ?? '';
    $captcha = $_POST['g-recaptcha-response'] ?? '';

    // ===== VALIDACIONES =====
    if (empty($correo) || empty($pass)) {
        $alertas[] = ['error', 'Todos los campos son obligatorios'];
    }

    // ===== CAPTCHA =====
    if (empty($captcha)) {
        $alertas[] = ['error', 'Verifica el reCAPTCHA'];
    } else {
       $secret = '6LeXHIMrAAAAAEZH2eoiGhX0bFdUk4xIPVlXZe-A';
        $verify = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$captcha"
        );
        $captchaOK = json_decode($verify, true);

        if (!$captchaOK['success']) {
            $alertas[] = ['error', 'Captcha inválido'];
        }
    }

    // ===== LOGIN =====
    if (empty($alertas)) {
        try {
            $db = Conexion::conectar();

            $sql = "
                SELECT id_usuario, primer_nombre, primer_apellido, correo, password, id_rol
                FROM usuarios
                WHERE correo = ?
                LIMIT 1
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([$correo]);

            if ($stmt->rowCount() === 1) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($pass, $usuario['password'])) {

                   //  SESIÓN
                   $_SESSION['id_usuario'] = $usuario['id_usuario'];
                   $_SESSION['nombre']     = $usuario['primer_nombre'];
                   $_SESSION['apellido']   = $usuario['primer_apellido'];
                   $_SESSION['correo']     = $usuario['correo'];
                   $_SESSION['id_rol']     = $usuario['id_rol'];

                   // REDIRECCIÓN CORRECTA PARA MODAL
                   if ($_SESSION['correo'] === 'richardmr77@gmail.com' || $_SESSION['id_rol'] == 1) {
                       echo '<script>window.top.location.href = "../views/administrador.php";</script>';
                       exit;
                   }

                   if ($_SESSION['id_rol'] == 2) {
                       echo '<script>window.top.location.href = "../views/clientes.php";</script>';
                       exit;
                   }

                   // Otros roles
                   echo '<script>window.top.location.href = "../views/vendedor.php";</script>';
                   exit;

                } else {
                    $alertas[] = ['error', 'Contraseña incorrecta'];
                }

            } else {
                $alertas[] = ['error', 'Usuario no encontrado'];
            }

        } catch (PDOException $e) {
            $alertas[] = ['error', 'Error de conexión'];
        }
    }
}
session_regenerate_id(true);

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login | Matthew NDT</title>
<link rel="stylesheet" href="estilos/registro.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<!-- ================= LOGIN ================= -->
<div class="main-content">
    <form method="POST" class="registro">
        <h2 class="title">Ingreso al sistema</h2>

        <label>Correo</label>
        <input type="email" name="correo" class="control" placeholder="Ingresa tu correo" required>

        <label>Contraseña</label>
        <input type="password" name="pas" class="control" placeholder="Ingresa tu contraseña" required>

        <div class="g-recaptcha" data-sitekey="6LeXHIMrAAAAAOGSyamoisUJUxeRIv8kwcxuki77"></div>

        <input type="submit" class="boton" value="Ingresar">

        <div class="forgot-password">
            <a href="../templetes/recuperar_password.php">
                ¿Olvidaste tu contraseña?
            </a>
        </div>
    </form>
</div>

<!-- ================= ALERTAS ================= -->
<?php if (!empty($alertas)): ?>
<script>
<?php foreach ($alertas as $a): ?>
swal({
    title: "<?php echo $a[0] === 'error' ? 'Error' : 'Éxito'; ?>",
    text: "<?php echo $a[1]; ?>",
    icon: "<?php echo $a[0]; ?>"
});
<?php endforeach; ?>
</script>
<?php endif; ?>

</body>
</html>
