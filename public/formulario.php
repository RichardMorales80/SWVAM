<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../config/Conexion.php';

/* =========================
   FUNCIÓN DE SANEAMIENTO
========================= */
function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

$alertas = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ===== SANEAR ===== */
    $nombre     = limpiar($_POST['nombre'] ?? '');
    $apellido   = limpiar($_POST['apellidos'] ?? '');
    $correo     = limpiar($_POST['correo'] ?? '');
    $telefono   = limpiar($_POST['telefono'] ?? '');
    $direccion  = limpiar($_POST['direccion'] ?? '');
    $pass       = $_POST['pas'] ?? '';
    $passrev    = $_POST['pasrev'] ?? '';
    $captcha    = $_POST['g-recaptcha-response'] ?? '';

    $errores = [];

    /* ===== VALIDACIONES ===== */
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre))
        $errores[] = "El nombre solo debe contener letras.";

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellido))
        $errores[] = "Los apellidos solo deben contener letras.";

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL))
        $errores[] = "Correo electrónico inválido.";

    if (!preg_match('/^[0-9]{7,14}$/', $telefono))
        $errores[] = "El teléfono debe tener entre 7 y 14 dígitos.";

    if (
        strlen($pass) < 8 ||
        !preg_match('/[A-Z]/', $pass) ||
        !preg_match('/[a-z]/', $pass) ||
        !preg_match('/[0-9]/', $pass) ||
        !preg_match('/[!@#$%^&*]/', $pass)
    ) {
        $errores[] = "La contraseña no cumple los requisitos.";
    }

    if ($pass !== $passrev)
        $errores[] = "Las contraseñas no coinciden.";

    /* ===== CAPTCHA ===== */
    if (empty($captcha)) {
        $errores[] = "Verifica el reCAPTCHA.";
    } else {
       $secret = '6LfDwd8rAAAAAFo0WyCcPZBVi8NxcPA8B1R-WWK8';
        $verify = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$captcha"
        );
        $captchaOK = json_decode($verify, true);
        if (!$captchaOK['success']) {
            $errores[] = "reCAPTCHA inválido.";
        }
    }

    /* ===== BD ===== */
    if (empty($errores)) {
        try {
            $db = Conexion::conectar();

            $existe = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
            $existe->execute([$correo]);

            if ($existe->rowCount() > 0) {
                $alertas[] = ['error', 'Ya existe un usuario con ese correo'];
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);

                $insert = $db->prepare("
                    INSERT INTO usuarios
                    (primer_nombre, primer_apellido, correo, telefono, lugar_residencia, password, id_rol)
                    VALUES (?, ?, ?, ?, ?, ?, 2)
                ");

                $insert->execute([
                    $nombre,
                    $apellido,
                    $correo,
                    $telefono,
                    $direccion,
                    $hash
                ]);

                $alertas[] = ['success', 'Usuario registrado correctamente'];
            }
        } catch (PDOException $e) {
            $alertas[] = ['error', $e->getMessage()];
        }
    } else {
        foreach ($errores as $e) {
            $alertas[] = ['error', $e];
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<link rel="stylesheet" href="estilos/estilos.css"> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" href="../public/imgenes/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<style>
body { padding: 2rem; }
body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: url('imagenes/logo1.png') no-repeat center center;
    background-size: 300px; opacity: 0.2; z-index: -5;
}
</style>

</head>

<body>

<!-- FONDO CON CIRCULOS -->
<div class="background-shapes">
    <div class="circle circle1"></div>
    <div class="circle circle2"></div>
    <div class="circle circle3"></div>
</div>

<!-- ================= NAV ================= -->

<nav class="">
    

    <div class="menu_toggle" id="menuToggle">☰</div>

    <ul class="menu" id="menu">

        <li class="logo-item">
            <a href="#">
                <img src="../public/imagenes/logo.png" class="logo" alt="logo">
            </a>
        </li>
        <a href="/" class="main_menu_link">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Atrás</span>
        </a>

    </ul>
</nav>
</head>


<!-- BOTÓN PARA ABRIR EL FORMULARIO -->
<button id="btnAbrirForm" class="btn-abrir">Registrarse</button>

<!-- MODAL DEL FORMULARIO -->
<div id="modalRegistro" class="modal">
  <div class="modal-contenido">
    <span id="cerrarModal" class="cerrar">&times;</span>

    <section class="formulario-modal">
      <h2>Registro de Usuario</h2><br>
      <form id="formulario" method="POST" novalidate class="form">

  <div class="form-columna">
    <div class="form-group">
      <label>Nombre</label>
      <input type="text" name="nombre" class="form-control" required>
      <small class="error text-danger"></small>
    </div>

    <div class="form-group">
      <label>Apellidos</label>
      <input type="text" name="apellidos" class="form-control" required>
      <small class="error text-danger"></small>
    </div>

    <div class="form-group">
      <label>Correo electrónico</label>
      <input type="email" name="correo" class="form-control" required>
      <small class="error text-danger"></small>
    </div>

    <div class="form-group">
      <label>Teléfono</label>
      <input type="text" name="telefono" class="form-control" required>
      <small class="error text-danger"></small>
    </div>
  </div>

  <div class="form-columna">
    <div class="form-group">
      <label>Dirección</label>
      <input type="text" name="direccion" class="form-control" required>
      <small class="error text-danger"></small>
    </div>

    <div class="form-group">
      <label>Contraseña</label>
      <input type="password" name="pas" id="pas" class="form-control" required>
      <small class="error text-danger"></small>
      <ul id="feedback-pass" class="text-danger mt-2"></ul>
    </div>

    <div class="form-group">
      <label>Confirmar contraseña</label>
      <input type="password" name="pasrev" id="pasrev" class="form-control" required>
      <small id="feedback-confirm" class="form-text text-danger"></small>
      <small class="error text-danger"></small>
    </div>

    <div class="form-group mt-2">
     <div class="g-recaptcha" data-sitekey="6LfDwd8rAAAAAO5jGdE_f9Es4QHlAH9KOzJWN7aK"></div>
    </div>

    <input type="submit" class="btn btn-primary mt-3" value="Registrar">
  </div>

</form>

    </section>
  </div>
</div>
<script>
const modal = document.getElementById("modalRegistro");
const btnAbrir = document.getElementById("btnAbrirForm");
const cerrar = document.getElementById("cerrarModal");
const form = document.getElementById("formulario");

btnAbrir.onclick = () => {
    modal.style.display = "block";
    document.body.style.overflow = "hidden";

    //  LIMPIAR FORMULARIO COMPLETO
    form.reset();

    //  QUITAR COLORES Y ERRORES
    document.querySelectorAll(".form-control").forEach(input => {
        input.classList.remove("is-valid", "is-invalid");
    });

    //  LIMPIAR MENSAJES
    document.getElementById("feedback-pass").innerHTML = "";
    document.getElementById("feedback-confirm").textContent = "";
};

cerrar.onclick = () => {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
};

window.onclick = (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
        document.body.style.overflow = "auto";
    }
};
</script>



<script>
// Validación de contraseña en tiempo real
function validarPasswordJS(pass) {
    let errores = [];
    if (pass.length < 8) errores.push("Debe tener al menos 8 caracteres.");
    if (!/[a-z]/.test(pass)) errores.push("Debe contener una letra minúscula.");
    if (!/[A-Z]/.test(pass)) errores.push("Debe contener una letra mayúscula.");
    if (!/[0-9]/.test(pass)) errores.push("Debe contener un número.");
    if (!/[#\$%\-_&]/.test(pass)) errores.push("Debe contener un símbolo especial (# $ % - _ &).");
    return errores;
}

const passInput = document.getElementById("pas");
const passRevInput = document.getElementById("pasrev");
const feedback = document.getElementById("feedback-pass");
const feedbackConfirm = document.getElementById("feedback-confirm");

passInput.addEventListener("input", () => {
    const errores = validarPasswordJS(passInput.value);
    feedback.innerHTML = errores.length
        ? errores.map(e => `<li>${e}</li>`).join("")
        : "<li class='text-success'>Contraseña válida ✅</li>";
});

passRevInput.addEventListener("input", () => {
    feedbackConfirm.textContent = passRevInput.value !== passInput.value ? "Las contraseñas no coinciden." : "";
});

// Mostrar mensajes de PHP con SweetAlert2
<?php if(!empty($swalMessages)): ?>
    <?php foreach($swalMessages as $msg): ?>
        Swal.fire({
            icon: '<?php echo $msg['type']; ?>',
            text: '<?php echo $msg['text']; ?>'
        });
    <?php endforeach; ?>
<?php endif; ?>
</script>
<script>
  const modal = document.getElementById("modalRegistro");
  const btnAbrir = document.getElementById("btnAbrirForm");
  const cerrar = document.getElementById("cerrarModal");

  btnAbrir.onclick = () => {
    modal.style.display = "block";
    document.body.style.overflow = "hidden"; // evita scroll del fondo
  };

  cerrar.onclick = () => {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
  };

  window.onclick = (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
      document.body.style.overflow = "auto";
    }
  };
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("formulario");

    const nombre = document.querySelector('input[name="nombre"]');
    const apellidos = document.querySelector('input[name="apellidos"]');
    const correo = document.querySelector('input[name="correo"]');
    const telefono = document.querySelector('input[name="telefono"]');
    const direccion = document.querySelector('input[name="direccion"]');
    const pass = document.getElementById("pas");
    const passrev = document.getElementById("pasrev");

    // --- BLOQUEAR ENTRADA DE CARACTERES INVÁLIDOS ---
    nombre.addEventListener("keypress", e => {
        if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]$/.test(e.key)) e.preventDefault();
    });

    apellidos.addEventListener("keypress", e => {
        if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]$/.test(e.key)) e.preventDefault();
    });

    telefono.addEventListener("keypress", e => {
        if (!/[0-9]/.test(e.key)) e.preventDefault();
    });

    // --- VALIDACIÓN AL ENVIAR ---
    form.addEventListener("submit", function(e) {
        e.preventDefault();

        document.querySelectorAll(".error").forEach(el => el.textContent = "");
        document.querySelectorAll(".form-control").forEach(el => el.classList.remove("is-invalid"));

        let valido = true;

        const soloLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
        const soloNumeros = /^[0-9]+$/;
        const formatoCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!nombre.value.trim()) {
            mostrarError(nombre, "El nombre es obligatorio.");
            valido = false;
        } else if (!soloLetras.test(nombre.value.trim())) {
            mostrarError(nombre, "Solo letras permitidas.");
            valido = false;
        }

        if (!apellidos.value.trim()) {
            mostrarError(apellidos, "Los apellidos son obligatorios.");
            valido = false;
        } else if (!soloLetras.test(apellidos.value.trim())) {
            mostrarError(apellidos, "Solo letras permitidas.");
            valido = false;
        }

        if (!correo.value.trim()) {
            mostrarError(correo, "El correo es obligatorio.");
            valido = false;
        } else if (!formatoCorreo.test(correo.value.trim())) {
            mostrarError(correo, "Correo inválido.");
            valido = false;
        }

        if (!telefono.value.trim()) {
            mostrarError(telefono, "El teléfono es obligatorio.");
            valido = false;
        } else if (!soloNumeros.test(telefono.value.trim())) {
            mostrarError(telefono, "Solo números permitidos.");
            valido = false;
        } else if (telefono.value.trim().length < 7 || telefono.value.trim().length > 14) {
            mostrarError(telefono, "Debe tener entre 7 y 14 dígitos.");
            valido = false;
        }

        if (!direccion.value.trim()) {
            mostrarError(direccion, "La dirección es obligatoria.");
            valido = false;
        }

        const erroresPass = validarPasswordJS(pass.value);
        if (!pass.value.trim()) {
            mostrarError(pass, "La contraseña es obligatoria.");
            valido = false;
        } else if (erroresPass.length > 0) {
            mostrarError(pass, erroresPass.join(" "));
            valido = false;
        }

        if (!passrev.value.trim()) {
            mostrarError(passrev, "Debes confirmar la contraseña.");
            valido = false;
        } else if (passrev.value !== pass.value) {
            mostrarError(passrev, "Las contraseñas no coinciden.");
            valido = false;
        }

        if (valido) {
            form.submit();
        }
    });

    // --- MOSTRAR ERROR ---
    function mostrarError(input, mensaje) {
        const error = input.parentElement.querySelector(".error");
        if (error) error.textContent = mensaje;
        input.classList.add("is-invalid");
    }

    // --- QUITAR BORDE ROJO AL CORREGIR ---
    document.querySelectorAll(".form-control").forEach(input => {
        input.addEventListener("input", () => {
            if (input.classList.contains("is-invalid") && input.value.trim() !== "") {
                input.classList.remove("is-invalid");
                const error = input.parentElement.querySelector(".error");
                if (error) error.textContent = "";
            }
        });
    });

    // --- VALIDAR CONTRASEÑA (misma lógica que PHP) ---
    function validarPasswordJS(password) {
        const errores = [];
        if (password.length < 8) errores.push("Debe tener al menos 8 caracteres.");
        if (!/[A-Z]/.test(password)) errores.push("Debe tener al menos una letra mayúscula.");
        if (!/[a-z]/.test(password)) errores.push("Debe tener al menos una letra minúscula.");
        if (!/[0-9]/.test(password)) errores.push("Debe tener al menos un número.");
        if (!/[!@#$%^&*(),.?\":{}|<>]/.test(password)) errores.push("Debe tener al menos un símbolo.");
        return errores;
    }
});
</script>
<?php if (!empty($alertas)): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
<?php foreach ($alertas as $a): ?>
    Swal.fire({
        icon: '<?= $a[0] ?>',
        text: '<?= $a[1] ?>'
    });
<?php endforeach; ?>
});
</script>
<?php endif; ?>



</body>
</html>