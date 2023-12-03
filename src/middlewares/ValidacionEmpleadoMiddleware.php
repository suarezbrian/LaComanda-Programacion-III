<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
include_once  __DIR__ . '/../models/usuario.php';


class ValidacionEmpleadoMiddleware {

    public function __invoke(Request $request, RequestHandler $handler): Response {

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $name = $route->getName();
       
        switch ($name) {
            case 'insertarEmpleado':
                return $this->validarInsertarEmpleado($request, $handler);
                break;
            case 'modificarEmpleado':
                return $this->validarModificarEmpleado($request, $handler);
                break;
            default:
                return $handler->handle($request);
                break;
        }
    }
    
    private function validarInsertarEmpleado(Request $request, RequestHandler $handler): Response {
        $data = $request->getParsedBody();
   
        $validationResult = $this->validarCampos($request, $data);

        $id_usuario = $data['id_usuario'];
        $usuario = Usuario::TraerUnUsuario($id_usuario);
        if($usuario === false) {
            return $this->crearRespuesta('El usuario no existe o esta dado de baja');
        }

        if ($validationResult) {
            return $handler->handle($validationResult);
        }
    
        return $this->crearRespuesta('Todos los campos deben completarse');
    }
    
    private function validarModificarEmpleado(Request $request, RequestHandler $handler): Response {
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        
        $validationResult = $this->validarCampos($request, $data);
        
        if ($validationResult) {
            return $handler->handle($validationResult);
        }
    
        return $this->crearRespuesta('Todos los campos deben completarse');
    }

    private function validarCampos(Request $request, $data) {
        $nombre = $data['nombre'];
        $id_usuario = $data['id_usuario'];
        $contacto = $data['contacto'];

        if (empty($nombre) || empty($contacto)) {
            return false;
        }

        $request = $request->withAttribute('nombre', $nombre);
        $request = $request->withAttribute('id_usuario', $id_usuario);
        $request = $request->withAttribute('contacto', $contacto);
    
        return $request;
    }
    
    private function crearRespuesta(string $mensaje): Response {
        $response = new Response();
        $payload = json_encode(['success' => false, 'mensaje' => $mensaje]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}