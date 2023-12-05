<?php


class ArchivoController {

    public static function cargarImagen($response, $rutaImagen){
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'],  $rutaImagen)){
            return true;
        }else{
            return false;
        }
    
    }

    public static function removerImagen($cliente, $rutaImagen)
    {
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);
            return true;
        } else {
            return false;
        }
    }

    public static function validarImagen(){

        $tipoArchivo = $_FILES['imagen']['type'];
        $tamanoArchivo = $_FILES['imagen']['size'];

        if (!((strpos($tipoArchivo, "png") || strpos($tipoArchivo, "jpeg")) && ($tamanoArchivo < 100000))) {
            return false;
        }

        return true;
    }
}