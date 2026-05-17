<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* CONEXION + BITACORA */
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/bitacora.php';

/* BLOQUEAR CACHÉ */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

/* TIEMPO DE INACTIVIDAD (900 segundos / 60= 15 MIN) */
$tiempo_inactividad = 1200;

/* SI NO HAY SESION */
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

/* VERIFICAR INACTIVIDAD */
if (isset($_SESSION['ultimo_acceso'])) {

    if (time() - $_SESSION['ultimo_acceso'] > $tiempo_inactividad) {

        /* REGISTRAR SALIDA POR INACTIVIDAD */
        $pdo = Conexion::conectar();

        if(isset($_SESSION['login_time'])){

            $inicio = $_SESSION['login_time'];
            $fin = time();

            $segundos = $fin - $inicio;

            $min = floor($segundos / 60);
            $seg = $segundos % 60;

            $tiempo = $min . " min " . $seg . " seg";

            $accion = "Cierre por inactividad | Tiempo en sistema: " . $tiempo;

        }else{
            $accion = "Cierre por inactividad";
        }

        registrarBitacora(
            $pdo,
            $_SESSION['id_usuario'],
            $accion
        );

        /* CERRAR SESION */
        session_unset();
        session_destroy();

        header("Location: /index.php?expirada=1");
        exit();
    }
}

/* ACTUALIZAR ACTIVIDAD */
$_SESSION['ultimo_acceso'] = time();