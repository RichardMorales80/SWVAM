
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
       $secret = '6LfDwd8rAAAAAFo0WyCcPZBVi8NxcPA8B1R-WWK8';
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

<link rel="stylesheet" href="estilos/principal.css">
<link rel="stylesheet" href="estilos/registro.css">

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>

<!-- NAV -->
<nav class="main_nav">

    <!-- BOTON HAMBURGUESA -->
    <div class="menu_toggle" id="toggle">☰</div>

    <ul id="main-menu" class="menu">

        <li class="logo">
            <a href="/" class="enlace">
                <img src="imagenes/logo1.png" class="logo">
            </a>
        </li>

        <li class="main_menu_item">
            <a href="formulario.php" class="main_menu_link">Crear cuenta</a>
        </li>

        <li class="main_menu_item">
            <a href="/" class="main_menu_link">Atrás</a>
        </li>

    </ul>

</nav>


<!-- LOGIN -->
<div class="registro">
<form method="POST" class="form">

<h2 class="title">Ingreso al sistema</h2>

<label>Correo</label>
<input type="email" name="correo" class="control" required>

<label>Contraseña</label>
<input type="password" name="pas" class="control" required>

<div class="g-recaptcha" data-sitekey="6LeXHIMrAAAAAOGSyamoisUJUxeRIv8kwcxuki77"></div> local
<input type="submit" class="boton" value="Ingresar">
<div style="margin-top:15px; text-align:center;">
    <a href="../templetes/recuperar_password.php" 
       style="color:#000; font-weight:bold; display:block;">
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
    <script>
const toggle = document.getElementById("toggle");
const menu = document.getElementById("main-menu");

toggle.addEventListener("click", ()=>{
    menu.classList.toggle("active");
});
</script>


</body>
</html>
