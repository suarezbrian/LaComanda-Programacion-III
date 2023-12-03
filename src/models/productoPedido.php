<?php

class ProductoPedido
{
    public $id;
    public $tiempo_estimado;
    public $id_estado;
    public $id_empleado;

    public static function TraerProductoPorEstado($id_estado)
    {     
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedido_producto where id_estado = :id_estado");
        $consulta->bindValue(':id_estado', $id_estado, PDO::PARAM_INT);
        $consulta->execute();
        $productos = $consulta->fetchAll(PDO::FETCH_CLASS, "ProductoPedido");
        
        return $productos;
    }

    public static function TraerProductoPorCodigoPreparacion($codigo_preparacion)
    {     
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedido_producto where codigo_preparacion = :codigo_preparacion");
        $consulta->bindValue(':codigo_preparacion', $codigo_preparacion, PDO::PARAM_STR);
        $consulta->execute();
        $producto = $consulta->fetchObject('ProductoPedido');
        
        return $producto;
    }

    public static function TraerTodosProductoPorIdPedido($id_pedido)
    {     
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedido_producto WHERE id_pedido = :id_pedido");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->execute();
        $producto = $consulta->fetchAll(PDO::FETCH_CLASS, "ProductoPedido");
        
        return $producto;
    }

    public static function TraerTodosProducto()
    {     
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedido_producto");
        $consulta->execute();
        $producto = $consulta->fetchAll(PDO::FETCH_CLASS, "ProductoPedido");
        
        return $producto;
    }

    public function ModificarProductoPedido()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedido_producto SET tiempo_estimado = :tiempo_estimado, id_estado = :id_estado, id_empleado = :id_empleado WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
        $consulta->bindValue(':id_estado', $this->id_estado, PDO::PARAM_INT);
        $consulta->bindValue(':id_empleado', $this->id_empleado, PDO::PARAM_INT);
        $resultado = $consulta->execute();
    
        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return $this->id;
    }

    public static function VerificarProductosEntregados($id_pedido)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id_estado FROM pedido_producto WHERE id_pedido = :id_pedido AND activo = 0");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->execute();

        $estados = $consulta->fetchAll(PDO::FETCH_COLUMN);
        
        $todosEntregados = empty(array_diff($estados, [3]));       
        return $todosEntregados;
    }


}