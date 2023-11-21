<?php

class Mesa
{
    public $id;
    public $codigo;
    public $id_estado;
    public $activo;


    public function InsertarMesaParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO mesas (codigo, id_estado,activo) VALUES (:codigo, :estado, 0)");
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->id_estado, PDO::PARAM_INT);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function TraerTodasLasMesas()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM mesas where activo = 0");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "mesa");
    }
    
    public static function TraerUnaMesa($id)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM mesas where id = $id and activo = 0");
        $consulta->execute();
        $mesaBuscada = $consulta->fetchObject('mesa');
        
        return $mesaBuscada;
    }

    public static function TraerUnaMesaPorCodigo($codigo_mesa)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM mesas where codigo = :codigo_mesa and activo = 0");
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
        $consulta->execute();
        $mesaBuscada = $consulta->fetchObject('mesa');
        
        return $mesaBuscada;
    }

    public function ModificarMesaParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE mesas SET codigo = :codigo, id_estado = :estado WHERE id = :id and activo = 0");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->id_estado, PDO::PARAM_INT);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return $this->id;
    }

    public function BorrarMesa()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM mesas WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function BajaLogicaMesa()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE mesas SET activo = 1 WHERE id = :id and activo = 0");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public static function CambiarEstadoMesa($id, $estado){       
        
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE mesas SET id_estado = :estado WHERE id = $id and activo = 0");       
        $consulta->bindValue(':estado', $estado, PDO::PARAM_INT);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return $id;
    }

    public static function CambiarEstadoMesaPorCodigo($codigo_mesa, $estado){       
        
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE mesas SET id_estado = :estado WHERE codigo = :codigo_mesa and activo = 0");  
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);     
        $consulta->bindValue(':estado', $estado, PDO::PARAM_INT);
        $resultado = $consulta->execute();
        
        $filasAfectadas = $consulta->rowCount();
        
        if ($filasAfectadas === 0) {
            return false;
        }
        return $codigo_mesa;
    }

    public static function pedidoExistenteEnMesa($id_mesa) {

        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id_estado FROM pedidos WHERE id_mesa = :id_mesa");
        $consulta->bindValue(':id_mesa', $id_mesa, PDO::PARAM_INT);
        $consulta->execute();
    
        $pedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);
       
        foreach ($pedidos as $pedido) {
           
            if (strtolower($pedido['id_estado']) !== 5) {
                return true;
            }
        }
    
        return false;
    }
}