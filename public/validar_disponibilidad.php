<?php
header('Content-Type: application/json');
require '../config/Conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([['error','Método no permitido']]);
    exit;
}

$campo = $_POST['campo'] ?? '';
$valor = trim($_POST['valor'] ?? '');

if(empty($campo) || empty($valor)){
    echo json_encode([['error','Datos incompletos']]);
    exit;
}

try {
    $db = Conexion::conectar();

    if($campo === 'correo'){
        $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo=?");
        $stmt->execute([$valor]);

        if($stmt->rowCount() > 0){
            echo json_encode([['error','El correo ya está registrado']]);
        } else {
            echo json_encode([['ok','Disponible']]);
        }
    }

    if($campo === 'telefono'){
        $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE telefono=?");
        $stmt->execute([$valor]);

        if($stmt->rowCount() > 0){
            echo json_encode([['error','El teléfono ya está registrado']]);
        } else {
            echo json_encode([['ok','Disponible']]);
        }
    }

} catch(PDOException $e){
    echo json_encode([['error','Error en servidor']]);
}