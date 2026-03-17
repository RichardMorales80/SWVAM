<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar control de inactividad
require __DIR__ . '/inactividad.php';

/**
 * Verifica que el usuario tenga un rol específico
 */
function verificarRol($rolPermitido) {
    if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != $rolPermitido) {
        redirigirInicio();
    }
}

/**
 * Verifica que el usuario tenga uno de varios roles permitidos
 */
function verificarRoles(array $rolesPermitidos) {
    if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], $rolesPermitidos)) {
        redirigirInicio();
    }
}

/**
 * Redirección centralizada
 */
function redirigirInicio() {
    header("Location: /SWVAM/index.php");
    exit();
}