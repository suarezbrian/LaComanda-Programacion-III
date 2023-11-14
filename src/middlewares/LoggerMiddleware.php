<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class LoggerMiddleware
{

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        // gestionar acciones de mi app.
        // crear una tabla en la db de logs.
    }

}
