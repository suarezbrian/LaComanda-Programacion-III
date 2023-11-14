<?php

class EstadoPedido
{
    public $id;

    public static function TraerUnEstadoPedido($id)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT nombre FROM estado_pedido where id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        $usuarioBuscado = $consulta->fetchObject('EstadoPedido');
       
        return $usuarioBuscado;
    }
}