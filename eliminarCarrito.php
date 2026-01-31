<?php
    session_start();
    $arreglo = $_SESSION['carrito'];
    for($i=0;$i<count($arreglo);$i++){
        if($arreglo[$i]['Id'] != $_POST['id']){
            $arreglonuevo[]= array(
                'Id'=>$arreglo[$i]['Id'],
                'Nombre'=>$arreglo[$i]['Nombre'],
                'Precio'=>$arreglo[$i]['Precio'],
                'Imagen'=>$arreglo[$i]['Imagen'],
                'Cantidad'=>$arreglo[$i]['Cantidad']
            );
        }
    }
    if(isset($arreglonuevo)){
        $_SESSION['carrito']=$arreglonuevo;
    }else{
        unset($_SESSION['carrito']);
    }
    echo "listo";
?>