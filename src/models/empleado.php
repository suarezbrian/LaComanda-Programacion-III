<?php

class Empleado
{
    public $id;
    public $nombre;
    public $rol;
    public $contacto;

    public function mostrarDatos()
    {
        return "Metodo mostar:" . $this->id . "  " . $this->nombre . "  " . $this->rol . " " . $this->contacto;
    }

    public function InsertarEmpleado()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into empleados (nombre,rol,contacto)values('$this->nombre','$this->rol','$this->contacto')");
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function InsertarEmpleadoParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into empleados (nombre,rol,contacto)values(:nombre,:rol,:contacto)");   
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':contacto', $this->contacto, PDO::PARAM_STR);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function TraerTodoLosEmpleado()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id,nombre, rol,contacto FROM empleados");
        $consulta->execute();
       
        return $consulta->fetchAll(PDO::FETCH_CLASS, "empleado");
    }

    public static function TraerUnEmpleado($id)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id,nombre, rol,contacto FROM empleados where id = $id");
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

    public function ModificarEmpleadoParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("
				update empleados 
				set nombre=:nombre,
				rol=:rol,
				contacto=:contacto
				WHERE id=:id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':contacto', $this->contacto, PDO::PARAM_STR);
        $resultado = $consulta->execute();
        
        $filasAfectadas = $consulta->rowCount();    
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }    
        return $this->id;
    }

}