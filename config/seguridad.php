<?php

// ================= CONFIGURACIÓN GLOBAL =================
define("BASE_URL", "https://morqui.org/");

// ================= INICIAR SESIÓN =================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================= CONTROL DE INACTIVIDAD =================
require __DIR__ . '/inactividad.php';

// ================= VERIFICAR UN SOLO ROL =================
function verificarRol($rolPermitido) {

    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol'])) {
        redirigirInicio();
    }

    if ($_SESSION['id_rol'] != $rolPermitido) {
        redirigirInicio();
    }
}

// ================= VERIFICAR MÚLTIPLES ROLES =================
function verificarRoles(array $rolesPermitidos) {

    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol'])) {
        redirigirInicio();
    }

    if (!in_array($_SESSION['id_rol'], $rolesPermitidos)) {
        redirigirInicio();
    }
}

// ================= REDIRECCIÓN INTELIGENTE =================
function redirigirInicio() {

    // Si no hay sesión → login
    if (!isset($_SESSION['id_rol'])) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    // Redirigir según rol
    switch ($_SESSION['id_rol']) {

        case 1: // ADMIN
            header("Location: " . BASE_URL . "views/administrador.php");
            break;

        case 2: // CLIENTE
            header("Location: " . BASE_URL . "public/cliente.php");
            break;

        case 3: // VENDEDOR
            header("Location: " . BASE_URL . "views/vendedor.php");
            break;

        default:
            header("Location: " . BASE_URL . "index.php");
            break;
    }

    exit();
}

