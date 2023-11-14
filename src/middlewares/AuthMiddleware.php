<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

include_once  __DIR__ . '/../models/rol.php';

class AuthMiddleware
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $parametros = $request->getQueryParams();

        $idUsuario = $parametros['id_usuario'];

        if (!is_numeric($idUsuario) || $idUsuario <= 0) {
            return $this->crearRespuesta('ID de usuario no válido');
        }

        $usuario = Usuario::TraerUnUsuario($idUsuario);

        if (!$usuario) {
            return $this->crearRespuesta('Usuario no encontrado');
        }

        return $this->validarRol($request, $handler, $usuario);
    }
    
    private function validarRol(Request $request, RequestHandler $handler, $usuario): Response
    {
        $rolValido = $this->config['rolValido'] ?? '';
        $rolUsuario = Rol::TraerUnRol($usuario->id_rol)->nombre;
      
        if (in_array(strtolower($rolUsuario), array_map('strtolower', $rolValido))) {
            return $handler->handle($request);
        }
    
        return $this->crearRespuesta('Usuario no tiene permisos para manipular estos datos');
    }
    
    private function crearRespuesta(string $mensaje): Response
    {
        $response = new Response();
        $payload = json_encode(['mensaje' => $mensaje]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
