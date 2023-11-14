<?php

include_once  __DIR__ . '/../models/usuario.php';

class UsuarioController {
    
    public function insertarUsuario($request, $response, $args) {
        $data = $request->getParsedBody();
        $id_rol = $data['id_rol'];
        $nombre = $data['nombre'];
        $password = $data['pass'];
       
        $usuario = new Usuario();
        $usuario->id_rol = $id_rol;
        $usuario->nombre = $nombre;
        $usuario->pass = $password;
        
        $result = $usuario->InsertarUsuario();
      
        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Usuario creado.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El usuario no se pudo crear.']));   
        } 

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function buscarUsuarioPorId($request, $response, $args) {
        $id = $args['id'];
        
        $usuario = Usuario::TraerUnUsuario($id);
        
        if($usuario === false) {
            $usuario = ['error' => 'No existe ese id'];
        }
        
        $response->getBody()->write(json_encode($usuario));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarUsuarios($request, $response, $args) {
        $usuarios = Usuario::TraerTodosLosUsuarios();
       
        $response->getBody()->write(json_encode($usuarios));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrarUsuario($request, $response, $args) {
        $id = $args['id'];
        $usuario = new Usuario();
        $usuario->id = $id;
        $result = $usuario->BorrarUsuario();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Usuario eliminado']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el usuario']));   
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarUsuario($request, $response, $args) {
        $id = $args['id'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        
        $id_rol = $data['id_rol'];
        $nombre = $data['nombre'];
        $password = $data['pass'];
        
        $usuario = new Usuario();
        $usuario->id = $id;
        $usuario->id_rol = $id_rol;
        $usuario->nombre = $nombre;
        $usuario->pass = $password;
        
        $result = $usuario->ModificarUsuarioParametros();
            
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Usuario modificado']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el usuario']));
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }
}