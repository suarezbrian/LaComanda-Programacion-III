<?php

class Rol
{
    public $id;

    public static function TraerUnRol($id)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT nombre FROM rol where id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        $usuarioBuscado = $consulta->fetchObject('Rol');
        
        return $usuarioBuscado;
    }
}