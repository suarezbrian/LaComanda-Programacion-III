<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
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
        $rol = $data['rol'];
        $contacto = $data['contacto'];

        if (empty($nombre) || empty($rol) || empty($contacto)) {
            return false;
        }

        $request = $request->withAttribute('nombre', $nombre);
        $request = $request->withAttribute('rol', $rol);
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