<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

class ValidacionMesaMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $name = $route->getName();

        switch ($name) {
            case 'insertarMesa':
                return $this->validarInsertarMesa($request, $handler);
                break;
            case 'modificarMesa':
                return $this->validarModificarMesa($request, $handler);
                break;
            default:
                return $handler->handle($request);
                break;
        }
    }

    private function validarInsertarMesa(Request $request, RequestHandler $handler): Response
    {
        $data = $request->getParsedBody();

        $validationResult = $this->validarCampos($request, $data);

        if ($validationResult) {
            return $handler->handle($validationResult);
        }

        return $this->crearRespuesta('Todos los campos deben completarse');
    }

    private function validarModificarMesa(Request $request, RequestHandler $handler): Response
    {
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);

        $validationResult = $this->validarCampos($request, $data);

        if ($validationResult) {
            return $handler->handle($validationResult);
        }

        return $this->crearRespuesta('Todos los campos deben completarse');
    }

    private function validarCampos(Request $request, $data)
    {
        $codigo = $data['codigo'];
        $estado = $data['id_estado'];

        if (empty($codigo) || empty($estado)) {
            return false;
        }

        $request = $request->withAttribute('codigo', $codigo);
        $request = $request->withAttribute('id_estado', $estado);

        return $request;
    }

    private function crearRespuesta(string $mensaje): Response
    {
        $response = new Response();
        $payload = json_encode(['success' => false, 'mensaje' => $mensaje]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}