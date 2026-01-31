<?php
session_start();

// Verificar si el usuario está logueado
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    // Redirigir al login si no está autorizado
    header("Location: ../public/login.php"); // Ajusta la ruta según tu proyecto
    
    exit();
}
?>
