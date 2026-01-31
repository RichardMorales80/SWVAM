<?php
require_once '../config/Conexion.php';

$pdo = Conexion::conectar();

// ============================
// OBTENER TOKEN DE LA URL
// ============================
$token = $_GET['token'] ?? '';

// Si no hay token
if(empty($token)){
    echo "Token inválido";
    exit;
}

// ============================
// VALIDAR TOKEN EN BD
// ============================
$sql = "
    SELECT id_usuario 
    FROM usuarios 
    WHERE token_recuperacion = ?
    AND token_expira > NOW()
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$token]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si token no existe o venció
if(!$usuario){
    echo "Token expirado o inválido";
    exit;
}

// ============================
// SI ENVÍAN NUEVA CONTRASEÑA
// ============================
if(isset($_POST['password'])){

    $nueva = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Actualizar password y limpiar token
    $update = $pdo->prepare("
        UPDATE usuarios 
        SET password = ?, 
            token_recuperacion = NULL, 
            token_expira = NULL
        WHERE id_usuario = ?
    ");

    $update->execute([$nueva, $usuario['id_usuario']]);

    echo "Contraseña actualizada correctamente";
    exit;
}
?>

<h2>Nueva contraseña</h2>

<form method="post">

<label>Nueva contraseña</label>
<input type="password" name="password" required>

<button type="submit">
Guardar contraseña
</button>

</form>
