
<?php

class Conexion {
    
    public static function conectar() {
        try {
            $base = new PDO(
                'mysql:host=sql201.byethost7.com;dbname=b7_40012077_Matthew;charset=utf8', 
                'b7_40012077', 
                'Negrito123' // Reemplaza con tu contraseña real
            );
             echo 'Conexion Ok';
            return $base;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>
