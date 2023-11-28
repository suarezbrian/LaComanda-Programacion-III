<?php

class Producto
{
    public $id;
    public $nombre;
    public $precio;
    public $categoria;
    public $cant_pedido;
    public $activo = 0;

    public function InsertarProductoParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO productos (nombre, precio, categoria, cant_pedido, activo) 
        VALUES (:nombre, :precio, :categoria, :cant_pedido, :activo)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':categoria', $this->categoria, PDO::PARAM_STR);
        $consulta->bindValue(':cant_pedido', $this->cant_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':activo', $this->activo, PDO::PARAM_INT);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function TraerTodosLosProductos()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id, nombre, precio, categoria FROM productos WHERE activo = 0");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Producto");
    }

    public static function TraerUnProducto($id)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id, nombre, precio, categoria FROM productos WHERE id = $id AND activo = 0");
        $consulta->execute();
        $productoBuscado = $consulta->fetchObject('Producto');
        
        return $productoBuscado;
    }

    public function ModificarProductoParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE productos SET nombre = :nombre, precio = :precio, categoria = :categoria WHERE id = :id AND activo = 0");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':categoria', $this->categoria, PDO::PARAM_STR);
        $resultado = $consulta->execute();
    
        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return $this->id;
    }

    public function BajaLogicaProducto()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE productos SET activo = 1 WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public static function ObtenerProductosMasPedido() 
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT *
        FROM productos
        WHERE cant_pedido = (
            SELECT cant_pedido
            FROM productos
            WHERE activo = 0
            ORDER BY cant_pedido DESC
            LIMIT 1
        )
        AND activo = 0
        ORDER BY cant_pedido DESC");
        $consulta->execute();
        $productosMasPedido = $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    
        return $productosMasPedido;
    }
    
    public static function ObtenerProductosMenosPedido() 
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT *
        FROM productos
        WHERE cant_pedido = (
            SELECT cant_pedido
            FROM productos
            WHERE activo = 0
            ORDER BY cant_pedido ASC
            LIMIT 1
        )
        AND activo = 0
        ORDER BY cant_pedido ASC");
        $consulta->execute();
        $productosMenosPedido = $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    
        return $productosMenosPedido;
    }
}