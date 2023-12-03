<?php

include_once  __DIR__ . '/../models/usuario.php';
include_once  __DIR__ . '/../models/rol.php';
include_once  __DIR__ . '/../middlewares/AutentificadorJWT.php';

class UsuarioController {

    public function login($request, $response, $args)
    {
        $params = $request->getParsedBody();

        $nombreUsuario = $params['nombre_usuario'];
        $password = $params['password'];

        $usuario = $this->AutenticarUsuario($nombreUsuario, $password);
      
        if (!$usuario) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Credenciales inválidas']));
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        $token = AutentificadorJWT::CrearToken(['id_usuario' => $usuario->id, 'id_rol' => $usuario->id_rol, 'nombre' => $usuario->nombre, 'esEmpleado' => $usuario->esEmpleado]);
        $response->getBody()->write(json_encode(['success' => true, 'token' => $token]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function AutenticarUsuario($nombreUsuario, $password){
        $usuarios = Usuario::TraerTodosLosUsuarios();
        
        foreach ($usuarios as $usuario) {
          
            if ($usuario->nombre === $nombreUsuario && $usuario->pass === $password) {
                return $usuario;
                break;
            }
        }
    
        return false;
    }
    
    public function insertarUsuario($request, $response, $args) {
        $data = $request->getParsedBody();
        $id_rol = $data['id_rol'];
        $nombre = $data['nombre'];
        $password = $data['pass'];

        if (empty($id_rol) || empty($nombre) || empty($password)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Todos los campos deben completarse']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $rol = Rol::TraerUnRol($id_rol);
       
        if(!$rol){
            $response->getBody()->write(json_encode(['success' => false, 'id_rol' => $id_rol,'message' => 'El rol no existe.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $usuario = Usuario::TraerUnUsuarioPorNombre($nombre);

        if($usuario->id > 0){
            $response->getBody()->write(json_encode(['success' => false, 'nombre' => $nombre,'message' => 'El nombre del usuario ya esta en uso.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $usuario = new Usuario();
        $usuario->id_rol = $id_rol;
        $usuario->nombre = $nombre;
        $usuario->pass = $password;
        
        $result = $usuario->InsertarUsuario();
      
        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'id_usuario' => $result,'message' => 'Usuario creado.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El usuario no se pudo crear.']));   
        } 

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function buscarUsuarioPorId($request, $response, $args) {
        $id = $args['id'];
        
        $usuario = Usuario::TraerUnUsuario($id);
        
        if($usuario === false) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El usuario no existe o esta dado de baja.']));   
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($usuario));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarUsuarios($request, $response, $args) {
        $usuarios = Usuario::TraerTodosLosUsuarios();

        if ( count($usuarios) > 0) {
            $response->getBody()->write(json_encode($usuarios)); 
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No hay ningun empleado activo registrado.']));   
        } 
           
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrarUsuario($request, $response, $args) {
        $id = $args['id'];

        $usuario = Usuario::TraerUnUsuario($id);       
        if($usuario === false) {
            $response->getBody()->write(json_encode(['success' => false, 'id_usuario' => $id,'message' => 'El usuario no existe o esta dado de baja.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $result = Usuario::BajaLogicaDeLaDB($usuario->id);

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

        if (empty($id_rol) || empty($nombre) || empty($password)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Todos los campos deben completarse']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $usuario = Usuario::TraerUnUsuarioPorNombre($nombre);

        if($usuario->id > 0){
            $response->getBody()->write(json_encode(['success' => false, 'nombre' => $nombre,'message' => 'El nombre del usuario ya esta en uso.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $usuario = new Usuario();
        $usuario->id = $id;
        $usuario->id_rol = $id_rol;
        $usuario->nombre = $nombre;
        $usuario->pass = $password;

        $result = $usuario->ModificarUsuarioParametros();
            
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Usuario modificado']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el usuario o esta dado de baja']));
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function cargarUsuarios($request, $response, $args) {
        $archivoSubido = $request->getUploadedFiles();
        $contador = 0;
        $nombresUsuario = []; 

        if (isset($archivoSubido['archivo']) && $archivoSubido['archivo']->getError() === UPLOAD_ERR_OK) {
            $archivo = $archivoSubido['archivo'];

            $usuarios = Usuario::CargarUsuariosDesdeCSV($archivo);

            foreach ($usuarios as $item) {

                $usuario = Usuario::TraerUnUsuarioPorNombre($item->nombre);
                if($usuario->id > 0){
                    $contador = 1+$contador;
                    $nombresUsuario[] = $item->nombre;
                }else{
                    $item->InsertarUsuario();    
                }                            
            }

            $nombreArchivo = $archivo->getClientFilename();
            $tipoArchivo = $archivo->getClientMediaType();

            $responseArray = [
                'success' => true,
                'message' => 'Archivo cargado con éxito',
                'filename' => $nombreArchivo,
            ];
        
            if ($contador > 0) {
                $responseArray['error_insercion'] = $contador;
                $responseArray['error_nombres'] = $nombresUsuario;
            }
        
            $response->getBody()->write(json_encode($responseArray));
           
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se proporcionó ningún archivo o hubo un error en la carga.']));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function descargarUsuarios($request, $response, $args)
    {
        try {
            $ruta = 'E:\Programas\xamp\htdocs\LaComanda\src\descargas\usuarios.csv';
            $usuarios = Usuario::TraerTodosLosUsuarios();
            $csvContenido = "id,id_rol,nombre,pass,activo\n";
    
            foreach ($usuarios as $usuario) {
                $csvContenido .= "{$usuario->id},{$usuario->id_rol},{$usuario->nombre},{$usuario->pass},{$usuario->activo}\n";
            }
    
            $resultado = file_put_contents($ruta, $csvContenido);
    
            if ($resultado !== false) {
                $response->getBody()->write(json_encode(['success' => true, 'message' => 'El archivo se ha descargado en la siguiente ruta: ' . $ruta]));
            } else {
                throw new Exception('Error al escribir en el archivo.');
            }
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Error al descargar el archivo: ' . $e->getMessage()]));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    
}