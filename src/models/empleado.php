<?php

class Empleado
{
    public $id;
    public $nombre;
    public $id_usuario;
    public $contacto;
    public $activo;
    public $fecha_creacion;
    public $identificador;

    public function InsertarEmpleadoParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into empleados (nombre,id_usuario,contacto,activo,fecha_creacion) values (:nombre,:id_usuario,:contacto,:activo,:fecha_creacion)");   
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_STR);
        $consulta->bindValue(':contacto', $this->contacto, PDO::PARAM_STR);
        $consulta->bindValue(':activo', $this->activo, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_creacion', $this->fecha_creacion, PDO::PARAM_STR);     
        $consulta->execute();
    
        $idInsertado = $objetoAccesoDato->RetornarUltimoIdInsertado();    
        $nuevoIdentificador = $idInsertado . $this->nombre;    
        $this->ActualizarIdentificador($idInsertado, $nuevoIdentificador);
    
        return $idInsertado;
    }
    
    public function ActualizarIdentificador($idEmpleado, $nuevoIdentificador)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE empleados SET identificador = :nuevoIdentificador WHERE id = :idEmpleado");
        $consulta->bindValue(':nuevoIdentificador', $nuevoIdentificador, PDO::PARAM_STR);
        $consulta->bindValue(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function TraerTodoLosEmpleado()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM empleados where activo = 0");
        $consulta->execute();
       
        return $consulta->fetchAll(PDO::FETCH_CLASS, "empleado");
    }

    public static function TraerUnEmpleado($id)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM empleados where id = $id and activo = 0");
        $consulta->execute();
        $cdBuscado = $consulta->fetchObject('empleado');
        
        return $cdBuscado;
    }

    public static function TraerUnEmpleadoPorIdUsuario($id_usuario)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM empleados where id_usuario = $id_usuario and activo = 0");
        $consulta->execute();
        $cdBuscado = $consulta->fetchObject('empleado');
        
        return $cdBuscado;
    }

    public function BorrarEmpleado()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("
				delete 
				from empleados 				
				WHERE id=:id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function BajaLogicaDeLaDB($idEmpleado)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE empleados SET activo = 1 WHERE id = :idEmpleado");
        $consulta->bindValue(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function ModificarEmpleadoParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("
				update empleados 
				set nombre=:nombre,
				contacto=:contacto
				WHERE id=:id and activo = 0");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':contacto', $this->contacto, PDO::PARAM_STR);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();    
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }    
        return $this->id;
    }

    public static function TraerEmpleadoPorIdentificador($identificador)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM empleados WHERE identificador = :identificador");
        $consulta->bindValue(':identificador', $identificador, PDO::PARAM_STR);
        $consulta->execute();

        $empleado = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$empleado) {
            return false;
        }
        return $empleado;
    }

}