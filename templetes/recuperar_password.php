<?php
require_once '../config/Conexion.php';
?>

<h2>Recuperar contraseña</h2>

<form action="../templetes/procesar_recuperacion.php" method="post">

<label>Correo:</label>
<input type="email" name="correo" required>

<button type="submit">
Enviar enlace de recuperación
</button>

</form>
