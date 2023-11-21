<?php

class Pedido
{
    public $id;
    public $id_mesa;
    public $id_empleado;
    public $codigo;
    public $id_estado;
    public $tiempo_estimado;

    public function InsertarPedido()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO pedidos (id_mesa, id_empleado, codigo, id_estado, tiempo_estimado) VALUES (:id_mesa, :id_empleado, :codigo, :id_estado, :tiempo_estimado)");
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':id_empleado', $this->id_empleado, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':id_estado', $this->id_estado, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_STR);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function TraerTodosLosPedidos()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id, id_mesa, id_empleado, codigo, id_estado, tiempo_estimado FROM pedidos");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Pedido");
    }

    public static function TraerUnPedido($id)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id, id_mesa, id_empleado, codigo, id_estado, tiempo_estimado FROM pedidos where id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        $pedidoBuscado = $consulta->fetchObject('Pedido');
       
        return $pedidoBuscado;
    }

    public static function TraerUnPedidoPorCodigo($codigo_pedido)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id, id_mesa, id_empleado, codigo, id_estado, tiempo_estimado FROM pedidos where codigo = :codigo_pedido");
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_STR);
        $consulta->execute();
        $pedidoBuscado = $consulta->fetchObject('Pedido');
       
        return $pedidoBuscado;
    }

    public function ModificarPedidoParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET id_mesa = :id_mesa, id_empleado = :id_empleado, codigo = :codigo, id_estado = :id_estado, tiempo_estimado = :tiempo_estimado WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':id_empleado', $this->id_empleado, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':id_estado', $this->id_estado, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_STR);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return $this->id;
    }

    public static function CambiarEstadoPedido($codigo_pedido, $id_estado){       

        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET id_estado = :id_estado WHERE codigo = :codigo_pedido");  
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_STR);     
        $consulta->bindValue(':id_estado', $id_estado, PDO::PARAM_INT);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        
        if ($filasAfectadas === 0) {
            return false;
        }
        return $codigo_pedido;
    }

    public function BorrarPedido()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public static function codigoRepetido($codigo) {

        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT COUNT(*) FROM pedidos WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();
        $cantidad = $consulta->fetchColumn();
        return $cantidad > 0;
    }

    public static function TraerPedidosPorEstado($estado_pedido) {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id, id_mesa, id_empleado, codigo, id_estado, tiempo_estimado FROM pedidos WHERE id_estado = :estado_pedido");
        $consulta->bindValue(':estado_pedido', $estado_pedido, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Pedido");
    }

    public function AsignarEmpleado($idEmpleado, $tiempoEstimado, $codigoPedido)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET id_empleado = :idEmpleado, tiempo_estimado = :tiempoEstimado, id_estado = 2 WHERE codigo = :codigoPedido AND id_estado = 1");
        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consulta->bindValue(':tiempoEstimado', $tiempoEstimado, PDO::PARAM_INT);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return true;
    }

    public static function InsertarProductosAPedido($valuesString)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO relacion_pedido_producto (id_producto, id_pedido) VALUES $valuesString");
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return true;        
    }

    public static function ObtenerProductosPorPedido($idPedido)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id_producto FROM relacion_pedido_producto WHERE id_pedido = :idPedido");
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->execute();

        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if (!$resultados) {
            return [];
        }

        $idsProductos = array_column($resultados, 'id_producto');
        return $idsProductos;
    }
}