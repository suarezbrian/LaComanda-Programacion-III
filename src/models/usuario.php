<?php

class Usuario
{
    public $id;
    public $id_rol;
    public $nombre;
    public $pass;

    const DADO_DE_BAJA = 1;
    const USUARIO_ACTIVO = 0;

    public function InsertarUsuario()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO usuarios (id_rol, nombre, pass, activo) VALUES (:id_rol, :nombre, :pass, " . self::USUARIO_ACTIVO . ")");
        $consulta->bindValue(':id_rol', $this->id_rol, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':pass', $this->pass, PDO::PARAM_STR);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function TraerTodosLosUsuarios()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM usuarios where activo = " . self::USUARIO_ACTIVO);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Usuario");
    }

    public static function TraerUnUsuario($id)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM usuarios where id = :id and activo = " . self::USUARIO_ACTIVO);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        $usuarioBuscado = $consulta->fetchObject('Usuario');
        
        return $usuarioBuscado;
    }

    public static function TraerUnUsuarioPorNombre($nombreUsuario)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM usuarios where nombre = :nombreUsuario and activo = " . self::USUARIO_ACTIVO);
        $consulta->bindValue(':nombreUsuario', $nombreUsuario, PDO::PARAM_STR);
        $consulta->execute();
        $usuarioBuscado = $consulta->fetchObject('Usuario');
        
        return $usuarioBuscado;
    }

    public function ModificarUsuarioParametros()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE usuarios SET id_rol = :id_rol, nombre = :nombre, pass = :pass WHERE id = :id AND activo = :activo");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':id_rol', $this->id_rol, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':pass', $this->pass, PDO::PARAM_STR);
        $consulta->bindValue(':activo', self::USUARIO_ACTIVO, PDO::PARAM_INT);
        $resultado = $consulta->execute();

        $filasAfectadas = $consulta->rowCount();
        
        if ($filasAfectadas === 0 || !$resultado) {
            return false;
        }
        return $resultado;
    }

    public function BorrarUsuario()
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM usuarios WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    
    public static function BajaLogicaDeLaDB($idUsuario)
    {
        $objetoAccesoDato = db::ObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE usuarios SET activo = 1 WHERE id = :idUsuario");
        $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }
    public static function CargarUsuariosDesdeCSV($archivo)
    {
        $usuarios = [];
        $contador = 0;

        $stream = $archivo->getStream();
        $stream->rewind();

        $data = $stream->getContents();

        $lineas = explode("\n", $data);

        foreach ($lineas as $linea) {
            if (empty($linea) || $contador === 0) {
                $contador = 1 + $contador;
                continue; 
            }

            $campos = str_getcsv($linea, ",");
            $usuario = new Usuario();
            $usuario->id_rol = $campos[0];
            $usuario->nombre = $campos[1];
            $usuario->pass = $campos[2];

            $usuarios[] = $usuario;
        }

        return $usuarios;
    }

}