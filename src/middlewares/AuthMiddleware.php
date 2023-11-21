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
        $token = $request->getHeaderLine('Authorization');
        $esValido = false;

        try {
            AutentificadorJWT::VerificarToken($token);
            $esValido = true;

            if ($esValido) {
                $payload = AutentificadorJWT::ObtenerPayLoad($token);

                return $this->validarRol($request, $handler, $payload->data);
            }

        } catch (Exception $e) {
            return $this->crearRespuesta('Token inválido o expirado');
        }
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
        $payload = json_encode(['success' => false, 'mensaje' => $mensaje]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
