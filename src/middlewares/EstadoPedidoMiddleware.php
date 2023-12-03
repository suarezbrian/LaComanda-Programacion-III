<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

include_once  __DIR__ . '/../models/estadoPedido.php';
include_once  __DIR__ . '/../models/productoPedido.php';
class EstadoPedidoMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);

        $codigo = $data['codigo_pedido'];
        $id_estado = $data['id_estado'];

        if (!empty($codigo) && !empty($id_estado)) {    
            $pedido = Pedido::TraerUnPedidoPorCodigo($codigo);
            
            if ($pedido) {
                if($pedido->id_estado === $id_estado){
                    $nombreEstado = EstadoPedido::TraerUnEstadoPedido($pedido->id_estado);
                    return $this->crearRespuesta('El pedido ya tiene el estado '. $id_estado . ' = ' . $nombreEstado->nombre . ' asignado.');

                }else{
                    if($id_estado == 3 && $pedido->id_estado == 8){
                        $pedidosEntregados = ProductoPedido::VerificarProductosEntregados($pedido->id);
                        
                        if($pedidosEntregados){
                            $request = $request->withAttribute('codigo_pedido', $codigo);
                            $request = $request->withAttribute('id_estado', $id_estado);
            
                            return $handler->handle($request);
                        }else{
                            return $this->crearRespuesta('Todavia no se prepararon todos los productos para este menu.');          
                        }
                    }else{
                        return $this->crearRespuesta('Para que el pedido sea entregado, tiene que estar listo para servir.');  
                    }
                }
         
            } else {
                return $this->crearRespuesta('No se encontro el pedido.');
            }
             
        } 

        return $this->crearRespuesta('Falta ingresar el codigo_pedido y/o el id_estado.');          
        
    }

    private function crearRespuesta(string $mensaje): Response
    {
        $response = new Response();
        $payload = json_encode(['success' => false, 'mensaje' => $mensaje]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}