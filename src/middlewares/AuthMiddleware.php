<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $token = $request->getHeaderLine('Authorization');

        try {
            AutentificadorJWT::VerificarToken($token);
            $payload = AutentificadorJWT::ObtenerPayLoad($token);
            $usuario = $payload->data;

            $request = $request->withAttribute('usuario', $usuario);

            return $handler->handle($request);

        } catch (Exception $e) {
            return $this->crearRespuesta('Token inválido o expirado');
        }
    }

    private function crearRespuesta(string $mensaje): Response
    {
        $response = new Response();
        $payload = json_encode(['success' => false, 'mensaje' => $mensaje]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
