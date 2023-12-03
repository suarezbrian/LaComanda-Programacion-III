<?php

class Encuesta
{
    public $codigo_pedido;
    public $codigo_mesa;
    public $descripcion;
    public $valoracion;

    public function InsertarEncuesta()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO encuestas (codigo_pedido, codigo_mesa, descripcion, valoracion) VALUES (:codigo_pedido, :codigo_mesa, :descripcion, :valoracion)");
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $consulta->bindValue(':valoracion', $this->valoracion, PDO::PARAM_INT);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function ObtenerMejoresComentarios($cantidad)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM encuestas ORDER BY valoracion DESC LIMIT :cantidad");
        $consulta->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
        $consulta->execute();
        $mejoresComentarios = $consulta->fetchAll(PDO::FETCH_CLASS, "Encuesta");

        return $mejoresComentarios;
    }

}