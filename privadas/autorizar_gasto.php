<?php

session_start();

require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

/* SOLO ADMIN */
verificarRoles([1]);

if(!isset($_SESSION['id_usuario'])){
    exit('No autorizado');
}

$pdo = Conexion::conectar();

/* ID DEL USUARIO A AUTORIZAR */
$id_usuario = intval($_POST['id'] ?? 0);

if($id_usuario <= 0){
    exit('ID inválido');
}

/* AUTORIZAR GASTO EXTRA */
$sql = "UPDATE usuarios
        SET permiso_gasto_extra = 1
        WHERE id_usuario = :id_usuario";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id_usuario' => $id_usuario
]);

echo "ok";