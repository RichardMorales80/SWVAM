<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   BLOQUEAR CACHÉ
========================= */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

/* =========================
   VALIDAR SESIÓN
========================= */
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../public/login.php");
    exit();
}

/* =========================
   EXPIRACIÓN (15 MIN)
========================= */
$tiempo_inactividad = 900;

if (isset($_SESSION['ultimo_acceso'])) {
    if (time() - $_SESSION['ultimo_acceso'] > $tiempo_inactividad) {
        session_unset();
        session_destroy();
        header("Location: ../public/login.php?expirada=1");
        exit();
    }
}

$_SESSION['ultimo_acceso'] = time();
?>
