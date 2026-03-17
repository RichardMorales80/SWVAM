<?php
session_start();
require '../config/Conexion.php';
require_once '../config/bitacora.php';

$alertas = [];

/* =========================
   CONFIGURACION SEGURIDAD
========================= */

$max_intentos = 5;
$tiempo_bloqueo = 120;

/* =========================
   CONTROL DE INTENTOS
========================= */

if (!isset($_SESSION['intentos_login'])) {
    $_SESSION['intentos_login'] = 0;
}

if (!isset($_SESSION['bloqueo_login'])) {
    $_SESSION['bloqueo_login'] = 0;
}

/* =========================
   VERIFICAR BLOQUEO
========================= */

if ($_SESSION['bloqueo_login'] > time()) {

    $minutos = ceil(($_SESSION['bloqueo_login'] - time()) / 60);
    $alertas[] = ['error', "Sistema bloqueado temporalmente. Intenta nuevamente en $minutos minuto(s)"];

} else {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $correo  = filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
        $pass    = $_POST['pas'] ?? '';
        $captcha = $_POST['g-recaptcha-response'] ?? '';

        if (empty($correo) || empty($pass)) {
            $alertas[] = ['error', 'Todos los campos son obligatorios'];
        }

        /* =========================
           VALIDAR CAPTCHA
        ========================= */

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

                        $_SESSION['intentos_login'] = 0;
                        $_SESSION['bloqueo_login'] = 0;

                        $_SESSION['id_usuario'] = $usuario['id_usuario'];
                        $_SESSION['nombre']     = $usuario['primer_nombre'];
                        $_SESSION['apellido']   = $usuario['primer_apellido'];
                        $_SESSION['correo']     = $usuario['correo'];
                        $_SESSION['id_rol']     = $usuario['id_rol'];
                           /* REGISTRAR LOGIN EN BITACORA */
                                registrarBitacora($db, $_SESSION['id_usuario'], 'Inició sesión');
                                $_SESSION['login_time'] = time();

                        if ($_SESSION['correo'] === 'richardmr77@gmail.com' || $_SESSION['id_rol'] == 1) {
                            echo '<script>window.top.location.href = "../views/administrador.php";</script>';
                            exit;
                        } elseif ($_SESSION['id_rol'] == 2) {
                            echo '<script>window.top.location.href = "../public/cliente.php";</script>';
                            exit;
                        } elseif ($_SESSION['id_rol'] == 3) {
                            echo '<script>window.top.location.href = "../views/vendedor.php";</script>';
                            exit;
                        } else {
                            $alertas[] = ['error', 'Rol de usuario no válido'];
                        }

                    } else {

                        $_SESSION['intentos_login']++;

                        if ($_SESSION['intentos_login'] >= $max_intentos) {
                            $_SESSION['bloqueo_login'] = time() + $tiempo_bloqueo;
                            $_SESSION['intentos_login'] = 0;
                            $alertas[] = ['error', 'Demasiados intentos fallidos. Sistema bloqueado por 2 minutos'];
                        } else {
                            $restantes = $max_intentos - $_SESSION['intentos_login'];
                            $alertas[] = ['error', "Contraseña incorrecta. Intentos restantes: $restantes"];
                        }
                    }

                } else {

                    $_SESSION['intentos_login']++;

                    if ($_SESSION['intentos_login'] >= $max_intentos) {
                        $_SESSION['bloqueo_login'] = time() + $tiempo_bloqueo;
                        $_SESSION['intentos_login'] = 0;
                        $alertas[] = ['error', 'Demasiados intentos fallidos. Sistema bloqueado por 2 minutos'];
                    } else {
                        $restantes = $max_intentos - $_SESSION['intentos_login'];
                        $alertas[] = ['error', "Usuario no encontrado. Intentos restantes: $restantes"];
                    }
                }

            } catch (PDOException $e) {
                $alertas[] = ['error', 'Error de conexión'];
            }
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

<link rel="stylesheet" href="estilos/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<div class="login-box">
    <div class="login-logo">
        <img src="../public/imagenes/logo.png" alt="logo">

        <div class="login-icon">
            <i class="fa fa-user"></i>
        </div>

        <h2>Matthew NDT</h2>
    </div>

    <form method="POST">
        <h3>Ingreso al sistema</h3>

        <div class="input-group">
            <i class="fa fa-envelope"></i>
            <input type="email" name="correo" placeholder="Correo electrónico" required>
        </div>

        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" name="pas" placeholder="Contraseña" required>
        </div>

        <div class="captcha-box">
            <div class="g-recaptcha" data-sitekey="6LeXHIMrAAAAAOGSyamoisUJUxeRIv8kwcxuki77"></div>
        </div>

        <button type="submit" class="login-btn">Ingresar</button>

        <div class="forgot-password">
            <a href="../templetes/recuperar_password.php">
                ¿Olvidaste tu contraseña?
            </a>
        </div>
    </form>
</div>

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