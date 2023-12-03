<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class RoleMiddleware
{
    private $rolesPermitidos;

    public function __construct(array $rolesPermitidos)
    {
        $this->rolesPermitidos = $rolesPermitidos;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {       
        $rolValido = $this->rolesPermitidos['rolValido'] ?? '';
        $usuario = $request->getAttribute('usuario');
        $rolUsuario = Rol::TraerUnRol($usuario->id_rol)->nombre;
        
        if (in_array(strtolower($rolUsuario), array_map('strtolower', $rolValido))) {
            $request = $request->withAttribute('usuario', $usuario);
            return $handler->handle($request);
        }

        return $this->crearRespuesta('Acceso no autorizado para este rol.');
    }

    private function crearRespuesta($mensaje)
    {
        $response = new Response();
        $payload = json_encode(['success' => false, 'mensaje' => $mensaje]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}