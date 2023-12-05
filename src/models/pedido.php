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
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO pedidos (id_mesa, codigo, id_estado, tiempo_estimado) VALUES (:id_mesa, :codigo, :id_estado, :tiempo_estimado)");
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':id_estado', $this->id_estado, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_STR);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function TraerTodosLosPedidos()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Pedido");
    }

    public static function TraerUnPedido($id)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos where id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        $pedidoBuscado = $consulta->fetchObject('Pedido');
       
        return $pedidoBuscado;
    }

    public static function TraerUnPedidoPorCodigo($codigo_pedido)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos where codigo = :codigo_pedido");
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_STR);
        $consulta->execute();
        $pedidoBuscado = $consulta->fetchObject('Pedido');
       
        return $pedidoBuscado;
    }
    public static function TraerUnPedidoPorIdMesa($id_mesa)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos where id_mesa = :id_mesa");
        $consulta->bindValue(':id_mesa', $id_mesa, PDO::PARAM_STR);
        $consulta->execute();
        $pedidoBuscado = $consulta->fetchObject('Pedido');
       
        return $pedidoBuscado;
    }

    public function ModificarPedidoParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET id_mesa = :id_mesa, codigo = :codigo, id_estado = :id_estado, tiempo_estimado = :tiempo_estimado WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
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

    public function ModificarPedidoTiempoEstimado($tiempoAdicional)
    {
        $tiempoEstimadoActual = $this->ObtenerTiempoEstimadoActual();    
        $nuevoTiempoEstimado = $tiempoEstimadoActual + $tiempoAdicional;
    
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET tiempo_estimado = :tiempo_estimado WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_estimado', $nuevoTiempoEstimado, PDO::PARAM_STR);
        $resultado = $consulta->execute();
    
        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
    
        return $this->id;
    }

    public function ModificarPedidoFechaInicio($nuevaFechaInicio)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET fecha_inicio = :fecha_inicio WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_inicio', $nuevaFechaInicio, PDO::PARAM_STR);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }

        return $this->id;
    }
    
    private function ObtenerTiempoEstimadoActual()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT tiempo_estimado FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    
        $tiempoEstimadoActual = $consulta->fetch(PDO::FETCH_ASSOC)['tiempo_estimado'];
    
        return $tiempoEstimadoActual;
    }

    public static function CambiarEstadoPedido($codigo_pedido, $id_estado, $tiempoRetraso){       

        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET id_estado = :id_estado, fecha_entrega = :fecha_entrega, tiempo_retraso = :tiempo_retraso WHERE codigo = :codigo_pedido");  
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_STR);     
        $consulta->bindValue(':id_estado', $id_estado, PDO::PARAM_INT);
        if($tiempoRetraso === null){
            $dateFormatted = null;
        }else{
            $ahora = time();
            $dateFormatted = date('Y-m-d H:i:s', $ahora);
        }
        $consulta->bindValue(':fecha_entrega', $dateFormatted, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_retraso', $tiempoRetraso, PDO::PARAM_STR);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        
        if ($filasAfectadas === 0) {
            return false;
        }
        return $codigo_pedido;
    }

    public function BajaLogicaPedido()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET id_estado = 4 WHERE id = :id");        
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
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos WHERE id_estado = :estado_pedido");
        $consulta->bindValue(':estado_pedido', $estado_pedido, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Pedido");
    }

    public function AsignarEmpleado($idEmpleado, $tiempoEstimado, $codigoPedido)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET id_empleado = :idEmpleado, tiempo_estimado = :tiempoEstimado, id_estado = 2, fecha_inicio = :fecha_inicio WHERE codigo = :codigoPedido AND id_estado = 2");
        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consulta->bindValue(':tiempoEstimado', $tiempoEstimado, PDO::PARAM_INT);
        $ahora = time();
        $dateFormatted = date('Y-m-d H:i:s', $ahora);
        $consulta->bindValue(':fecha_inicio', $dateFormatted, PDO::PARAM_STR);
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
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO pedido_producto (id_producto, id_pedido, codigo_preparacion) VALUES $valuesString");
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }

        return true;        
    }

    public static function IncrementarCantPedido($valores) {

        foreach ($valores as $item) {        
            $objetoAccesoDato = db::ObjetoAcceso();
            $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE productos SET cant_pedido = cant_pedido + 1 WHERE id = :productoId");
            $consulta->bindValue(':productoId', $item, PDO::PARAM_INT);
            $consulta->execute();
        }
    }

    public static function ObtenerProductosPorPedido($idPedido)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id_producto FROM pedido_producto WHERE id_pedido = :idPedido");
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->execute();

        $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if (!$resultados) {
            return [];
        }

        $idsProductos = array_column($resultados, 'id_producto');
        return $idsProductos;
    }

    public static function ObtenerPedidosConRetraso()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos WHERE tiempo_retraso != '00:00:00'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function ObtenerPedidosSinRetraso()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos WHERE tiempo_retraso = '00:00:00'");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function ModificarPedidoImporte($id,$importe)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET importe = :importe WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':importe', $importe, PDO::PARAM_INT);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return $id;
    }

    public static function ModificarPedidoRutaImagen($id,$img_ruta)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos SET mesa_img = :img_ruta WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':img_ruta', $img_ruta, PDO::PARAM_INT);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return $id;
    }

}