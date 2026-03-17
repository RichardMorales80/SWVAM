<?php
session_start();

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/bitacora.php';

/* =========================
   REGISTRAR CIERRE SESION
========================= */

if(isset($_SESSION['id_usuario'])){

    $pdo = Conexion::conectar();

    /* CALCULAR TIEMPO */
    if(isset($_SESSION['login_time'])){

        $inicio = $_SESSION['login_time'];
        $fin = time();

        $segundos = $fin - $inicio;

        $min = floor($segundos / 60);
        $seg = $segundos % 60;

        $tiempo = $min . " min " . $seg . " seg";

        $accion = "Cerró sesión | Tiempo en sistema: " . $tiempo;

    }else{
        $accion = "Cerró sesión";
    }

    registrarBitacora(
        $pdo,
        $_SESSION['id_usuario'],
        $accion
    );
}

/* =========================
   DESTRUIR SESION
========================= */

$_SESSION = [];

if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

/* =========================
   EVITAR CACHE
========================= */

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/* =========================
   REDIRECCION
========================= */

header("Location: ../index.php");
exit();
?>



