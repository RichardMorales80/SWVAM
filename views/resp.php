
<?php
    session_start();
	//error_reporting(0);

	$usuario = $_SESSION['nombre'];
	$_SESSION['usuario'] = 'Richard';
	if(!isset($usuario)){
		
		header("location:loguin.php");

	}
?>




<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../public/estilos/principal.css">
        <!-- CSS only -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">

        <title>Document</title>
    </head>
    <body>
    <ul class="menu" id="main-menu" class="main-menu">
        <div>    
        <a href="#" class="enlace"> <img src="../public/imagenes/logo1.png" alt="" class="logo"></a></div>       
        
        <!-- <li> <a href="#">Inicio</a></li> -->
        <li class="main_menu_item">
            <a href="../views/productos.php" class="main_menu_link">Atras</a>
        </li>
        
        <li class="main_menu_item">
             <a href="../config/cerrar_sesion.php" class="main_menu_link">Salir</a>
        </li>

      
    </ul>


        </nav><br><br>
        
        <div class="container">

            <h1 class="text-center">Listado de Usuarios</h1>
            <br>
            <br>
            
            <table class="table table-striped">
              <thead>
                <tr>
                    <th>idproducto</th>
                    <th>nombre</th>
                    <th>descripccion</th>
                    <th>precio</th>                                   
                    <th>imagen</th>
                    <th>estado</th>
                   
                </tr>
              </thead>
              <tbody>
                <?php
                    include '../config/Conexion.php';
                    $base= Conexion::conectar();
                    $query = "SELECT * FROM tablaproductos WHERE estado = 0"; 
                    $resultado = $base->prepare($query);;
                    $resultado->execute();
                    $producto=$resultado->fetchAll(PDO::FETCH_OBJ);                                                                                              
                    if($resultado->rowCount()>0){ ?>        
                        <?php
                            foreach($producto as $producto){ ?>
                                <tr>
                 <td><?= $producto->codigoBarras ?></td>
                <td><?= htmlspecialchars($producto->nombre) ?></td>
                <td><?= htmlspecialchars($producto->descripcion) ?></td>
                
                <td>$<?= number_format($producto->precioVenta, 2) ?></td>
                <td>
                    <?php if (!empty($producto->imagen) && file_exists('uploads/' . $producto->imagen)): ?>
                        <img src="uploads/<?= htmlspecialchars($producto->imagen) ?>" width="100" height="100" alt="<?= htmlspecialchars($producto->nombre) ?>" />
                    <?php else: ?>
                        Sin imagen
                    <?php endif; ?>
                </td>
               
                 <td><?= htmlspecialchars($producto->estado) ?></td>
                                </tr>
                            <?php
                            } 
                    }?>
              </tbody>
            </table>    
        </div>


  




<script>
    $('.editbtn').on('click', function(){
        $tr=$(this).closest('tr');
        var datos=$tr.children("td").map(function(){
            return $(this).text();
        });
        
        $('#update_id').val(datos[0]);
        $('#nombre').val(datos[1]);
        $('#apellidos').val(datos[2]);
        $('#correo').val(datos[3]);
        $('#telefono').val(datos[4]);
        $('#direccion').val(datos[5]);
        $('#Password').val(datos[6]);

    });
</script> 

<script>
    $('.deletbtn').on('click', function(){
        $tr=$(this).closest('tr');
        var datos=$tr.children("td").map(function(){
            return $(this).text();
        });
        
        $('#delete_id').val(datos[0]);

    });
</script> 


</body>
</html>