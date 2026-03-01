<?php
// public/componentes/form_registro.php
require 'config/Conexion.php';

function limpiar($dato){
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

$erroresPHP = [];
$successPHP = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = limpiar($_POST['nombre'] ?? '');
    $apellido   = limpiar($_POST['apellidos'] ?? '');
    $correo     = limpiar($_POST['correo'] ?? '');
    $telefono   = limpiar($_POST['telefono'] ?? '');
    $direccion  = limpiar($_POST['direccion'] ?? '');
    $pass       = $_POST['pas'] ?? '';
    $passrev    = $_POST['pasrev'] ?? '';
    $captcha    = $_POST['g-recaptcha-response'] ?? '';

    // ===== VALIDACIONES PHP =====
    if(!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre)) $erroresPHP[] = "El nombre solo debe contener letras.";
    if(!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellido)) $erroresPHP[] = "Los apellidos solo deben contener letras.";
    if(!filter_var($correo, FILTER_VALIDATE_EMAIL)) $erroresPHP[] = "Correo electrónico inválido.";
    if(!preg_match('/^[0-9]{7,14}$/', $telefono)) $erroresPHP[] = "El teléfono debe tener entre 7 y 14 dígitos.";
    if(strlen($pass) < 8 || !preg_match('/[A-Z]/',$pass) || !preg_match('/[a-z]/',$pass) || !preg_match('/[0-9]/',$pass) || !preg_match('/[!@#$%^&*]/',$pass)) $erroresPHP[] = "La contraseña no cumple los requisitos.";
    if($pass !== $passrev) $erroresPHP[] = "Las contraseñas no coinciden.";
    if(empty($captcha)) $erroresPHP[] = "Verifica el reCAPTCHA.";
    else {
        $secret = 'TU_SECRET_KEY';
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$captcha");
        $captchaOK = json_decode($verify,true);
        if(!$captchaOK['success']) $erroresPHP[] = "reCAPTCHA inválido.";
    }

    // ===== INSERCIÓN BD =====
    if(empty($erroresPHP)){
        try{
            $db = Conexion::conectar();
            $existe = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo=?");
            $existe->execute([$correo]);
            if($existe->rowCount()>0) $erroresPHP[] = "Ya existe un usuario con ese correo.";
            else {
                $hash = password_hash($pass,PASSWORD_DEFAULT);
                $insert = $db->prepare("INSERT INTO usuarios (primer_nombre, primer_apellido, correo, telefono, lugar_residencia, password, id_rol) VALUES (?,?,?,?,?,?,2)");
                $insert->execute([$nombre,$apellido,$correo,$telefono,$direccion,$hash]);
                $successPHP = "Usuario registrado correctamente ✅";
            }
        }catch(PDOException $e){
            $erroresPHP[] = "Error servidor: ".$e->getMessage();
        }
    }
}
?>

<!-- ================= FORMULARIO ================= -->
<form id="formRegistro" method="POST" novalidate>
    <?php if(!empty($erroresPHP)): ?>
        <div class="mb-3 text-danger" id="errorPHP">
            <?php foreach($erroresPHP as $err) echo "<div>$err</div>"; ?>
        </div>
    <?php elseif(!empty($successPHP)): ?>
        <div class="mb-3 text-success"><?= $successPHP ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
        <small class="text-danger error"></small>
    </div>
    <div class="mb-3">
        <label>Apellidos</label>
        <input type="text" name="apellidos" class="form-control" required>
        <small class="text-danger error"></small>
    </div>
    <div class="mb-3">
        <label>Correo</label>
        <input type="email" name="correo" class="form-control" required>
        <small class="text-danger error"></small>
    </div>
    <div class="mb-3">
        <label>Teléfono</label>
        <input type="text" name="telefono" class="form-control" required>
        <small class="text-danger error"></small>
    </div>
    <div class="mb-3">
        <label>Dirección</label>
        <input type="text" name="direccion" class="form-control" required>
        <small class="text-danger error"></small>
    </div>
    <div class="mb-3">
        <label>Contraseña</label>
        <input type="password" name="pas" id="pas" class="form-control" required>
        <small class="text-danger error"></small>
    </div>
    <div class="mb-3">
        <label>Confirmar contraseña</label>
        <input type="password" name="pasrev" id="pasrev" class="form-control" required>
        <small class="text-danger error"></small>
    </div>
    <div class="mb-3">
        <div class="g-recaptcha" data-sitekey="TU_SITE_KEY"></div>
    </div>
    <input type="submit" class="btn btn-success" value="Registrar">
</form>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const form = document.getElementById("formRegistro");

    function mostrarError(input, msg){
        const error = input.parentElement.querySelector(".error");
        if(error) error.textContent = msg;
        input.classList.add("is-invalid");
    }

    form.addEventListener("submit", function(e){
        let valido = true;
        document.querySelectorAll(".error").forEach(el => el.textContent="");
        document.querySelectorAll(".form-control").forEach(el => el.classList.remove("is-invalid"));

        const nombre = form.nombre.value.trim();
        const apellidos = form.apellidos.value.trim();
        const correo = form.correo.value.trim();
        const telefono = form.telefono.value.trim();
        const direccion = form.direccion.value.trim();
        const pass = form.pas.value;
        const passrev = form.pasrev.value;

        const soloLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
        const soloNumeros = /^[0-9]+$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if(!nombre){ mostrarError(form.nombre,"Nombre obligatorio"); valido=false; }
        else if(!soloLetras.test(nombre)){ mostrarError(form.nombre,"Solo letras"); valido=false; }

        if(!apellidos){ mostrarError(form.apellidos,"Apellidos obligatorios"); valido=false; }
        else if(!soloLetras.test(apellidos)){ mostrarError(form.apellidos,"Solo letras"); valido=false; }

        if(!correo){ mostrarError(form.correo,"Correo obligatorio"); valido=false; }
        else if(!emailRegex.test(correo)){ mostrarError(form.correo,"Correo inválido"); valido=false; }

        if(!telefono){ mostrarError(form.telefono,"Teléfono obligatorio"); valido=false; }
        else if(!soloNumeros.test(telefono)){ mostrarError(form.telefono,"Solo números"); valido=false; }
        else if(telefono.length<7 || telefono.length>14){ mostrarError(form.telefono,"Entre 7 y 14 dígitos"); valido=false; }

        if(!direccion){ mostrarError(form.direccion,"Dirección obligatoria"); valido=false; }

        let erroresPass = [];
        if(pass.length<8) erroresPass.push("Min 8 caracteres");
        if(!/[A-Z]/.test(pass)) erroresPass.push("Mayúscula");
        if(!/[a-z]/.test(pass)) erroresPass.push("Minúscula");
        if(!/[0-9]/.test(pass)) erroresPass.push("Número");
        if(!/[!@#$%^&*]/.test(pass)) erroresPass.push("Símbolo");

        if(erroresPass.length>0){ mostrarError(form.pas, erroresPass.join(", ")); valido=false; }
        if(passrev!==pass){ mostrarError(form.pasrev,"Las contraseñas no coinciden"); valido=false; }

        if(!valido) e.preventDefault();
    });
});
</script>
