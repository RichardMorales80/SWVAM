
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

//  ADMIN FORZADO
if ($_SESSION['correo'] === 'richardmr77@gmail.com') {
    $_SESSION['id_rol'] = 1;
    header("Location: ../views/administrador.php");
    exit;
}

//  ADMIN POR ROL
if ($_SESSION['id_rol'] == 1) {
    header("Location: ../views/administrador.php");
    exit;
}

//  USUARIO
if ($_SESSION['id_rol'] == 2) {
    header("Location: ../views/clientes.php");
    exit;
}

// 🔵 OTROS
header("Location: ../views/vendedor.php");
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login | Matthew NDT</title>

<link rel="stylesheet" href="estilos/encabezado.css">
<link rel="stylesheet" href="estilos/registro.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>

<!-- FONDO CON CIRCULOS -->
<div class="background-shapes">
    <div class="circle circle1"></div>
    <div class="circle circle2"></div>
    <div class="circle circle3"></div>
</div>

<!-- ================= NAV ================= -->
<nav class="main_nav">
    <div class="menu_toggle" id="menuToggle">☰</div>
    <ul class="menu" id="menu">
        <li class="logo-item">
            <a href="#">
                <img src="../public/imagenes/logo.png" class="logo" alt="logo">
            </a>
        </li>
        <li><a href="../public/formulario.php" class="main_menu_link">Crea cuenta</a></li>
        <li><a href="/" class="main_menu_link">Pagina de inicio</a></li>
        <li><a href="../public/productos.html" class="main_menu_link">Catalogo</a></li>
    </ul>
</nav>

<!-- ================= LOGIN ================= -->
<div class="main-content">
    <div class="registro">
        <form method="POST" class="form">

            <h2 class="title">Ingreso al sistema</h2>

            <label>Correo</label>
            <input type="email" name="correo" class="control" required>

            <label>Contraseña</label>
            <input type="password" name="pas" class="control" required>

            <div class="g-recaptcha" data-sitekey="6LeXHIMrAAAAAOGSyamoisUJUxeRIv8kwcxuki77"></div>
            <input type="submit" class="boton" value="Ingresar">

            <div style="margin-top:15px; text-align:center;">
                <a href="../templetes/recuperar_password.php" style="color:#000; font-weight:bold; display:block;">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

        </form>
    </div>
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

<!-- ================= FOOTER ================= -->
<div class="wave-container">
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none" class="wave">
        <path d="M0,60 C240,100 480,20 720,50 960,80 1200,110 1440,60 L1440,0 L0,0 Z"></path>
    </svg>
</div>

<div class="zona1">
    <div class="box">
        <img src="../public/imagenes/logo.png" class="logo-footer">
    </div>
    <div class="box">
        <h2>SOBRE NOSOTROS</h2>
        <p>
            Somos Matthew NDT. Empresa mexicana dedicada a venta de equipos,
            productos NDT, servicios y capacitaciones para la industria.
        </p>
    </div>
    <div class="box">
        <h2>SÍGUENOS</h2>
        <div class="red-social">
            <a href="#" class="fa-brands fa-facebook"></a>
            <a href="#" class="fa-brands fa-youtube"></a>
            <a href="#" class="fa-solid fa-phone"></a>
            <a href="#" class="fa-solid fa-envelope"></a>
        </div>
    </div>
</div>

<div class="zona2">
    <small>&copy; 2026 Matthew NDT - Todos los derechos reservados</small>
</div>

<!-- ================= WHATSAPP ================= -->
<a href="https://wa.me/525548929587" class="whatsapp-float" target="_blank">
    <img src="https://cdn-icons-png.flaticon.com/512/124/124034.png">
</a>

<!-- ================= JS MENU ================= -->
<script>
const toggle = document.getElementById("menuToggle");
const menu = document.getElementById("menu");
toggle.addEventListener("click", () => {
    menu.classList.toggle("active");
});
</script>

</body>
</html>