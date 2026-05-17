<?php

require_once '../config/Conexion.php';

$pdo = Conexion::conectar();

/* =========================
   OBTENER TOKEN
========================= */

$token = $_GET['token'] ?? '';

if(empty($token)){

    exit('Token inválido');
}

/* =========================
   VALIDAR TOKEN
========================= */

$sql = "SELECT id_usuario
        FROM usuarios
        WHERE token_recuperacion = :token
        AND token_expira > NOW()
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':token' => $token
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$usuario){

    exit('Token expirado o inválido');
}

/* =========================
   ACTUALIZAR PASSWORD
========================= */

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $password         = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    /* VALIDAR */

    if(empty($password) || empty($confirm_password)){

        $error = "Todos los campos son obligatorios";

    }elseif(strlen($password) < 8){

        $error = "La contraseña debe tener mínimo 8 caracteres";

    }elseif($password !== $confirm_password){

        $error = "Las contraseñas no coinciden";

    }else{

        /* ENCRIPTAR */

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        /* ACTUALIZAR */

        $update = $pdo->prepare("UPDATE usuarios
                                 SET password = :password,
                                     token_recuperacion = NULL,
                                     token_expira = NULL
                                 WHERE id_usuario = :id_usuario");

        $update->execute([
            ':password'   => $passwordHash,
            ':id_usuario' => $usuario['id_usuario']
        ]);

        echo "
        <!DOCTYPE html>
        <html lang='es'>
        <head>

        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>

        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

        </head>

        <body>

        <script>

        Swal.fire({
            icon: 'success',
            title: 'Contraseña actualizada',
            text: 'Tu contraseña fue cambiada correctamente',
            confirmButtonColor: '#4f46e5'
        }).then(() => {

            window.location.href = '../index.php';

        });

        </script>

        </body>
        </html>
        ";

        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nueva contraseña</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family: Arial, sans-serif;

    background: linear-gradient(135deg,#eef2ff,#f5f3ff);

    display:flex;

    justify-content:center;

    align-items:center;

    min-height:100dvh;

    overflow-y:auto;

    padding:20px;
}

.container{

    position:relative;

    width:100%;

    max-width:420px;

    background:white;

    padding:35px;

    border-radius:20px;

    box-shadow:0 10px 25px rgba(0,0,0,0.15);

    text-align:center;
}

/* ======================================
   BOTON CERRAR
====================================== */

.cerrar-btn{

    position:absolute;

    top:15px;

    right:15px;

    width:35px;

    height:35px;

    border-radius:50%;

    background:#ef4444;

    color:white;

    text-decoration:none;

    display:flex;

    justify-content:center;

    align-items:center;

    font-size:20px;

    font-weight:bold;

    transition:0.3s;
}

.cerrar-btn:hover{

    background:#dc2626;

    transform:scale(1.05);
}

.logo{

    width:170px;

    margin-bottom:20px;
}

h2{

    color:#1f2937;

    margin-bottom:10px;

    font-size:20px;
}

.texto{

    color:#6b7280;

    font-size:15px;

    margin-bottom:25px;
}

input{

    width:100%;

    padding:14px;

    margin-bottom:18px;

    border:1px solid #d1d5db;

    border-radius:10px;

    font-size:15px;

    outline:none;

    transition:0.3s;
}

input:focus{

    border-color:#4f46e5;

    box-shadow:0 0 5px rgba(79,70,229,0.3);
}

button{

    width:100%;

    padding:14px;

    border:none;

    border-radius:10px;

    background:#4f46e5;

    color:white;

    font-size:16px;

    font-weight:bold;

    cursor:pointer;

    transition:0.3s;
}

button:hover{

    background:#4338ca;
}

.error{

    background:#fee2e2;

    color:#991b1b;

    padding:12px;

    border-radius:10px;

    margin-bottom:18px;

    font-size:14px;
}

/* ======================================
   RESPONSIVE
====================================== */

@media(max-width:768px){

    body{

        padding:15px;

        align-items:flex-start;
    }

    .container{

        padding:25px 20px;

        border-radius:18px;
    }

    .logo{

        width:140px;
    }

    h2{

        font-size:18px;
    }

    .texto{

        font-size:14px;
    }

    input{

        padding:12px;

        font-size:14px;
    }

    button{

        padding:12px;

        font-size:15px;
    }

    .cerrar-btn{

        width:32px;

        height:32px;

        font-size:18px;
    }

}

</style>

</head>

<body>

<div class="container">

    <!-- BOTON CERRAR -->

    <a
        href="../index.php"
        class="cerrar-btn">

        ✕

    </a>

    <!-- LOGO -->

    <img
        src="../img/logo.png"
        alt="Logo"
        class="logo"
    >

    <h2>Nueva contraseña</h2>

    <p class="texto">
        Ingresa y confirma tu nueva contraseña
    </p>

    <?php if(isset($error)): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="password"
            name="password"
            placeholder="Nueva contraseña"
            required
        >

        <input
            type="password"
            name="confirm_password"
            placeholder="Confirmar contraseña"
            required
        >

        <button type="submit">
            Guardar contraseña
        </button>

    </form>

</div>

</body>
</html>