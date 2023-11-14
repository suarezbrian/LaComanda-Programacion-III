<?php

include_once  __DIR__ . '/../models/mesa.php';
include_once  __DIR__ . '/../models/empleado.php';
include_once  __DIR__ . '/../models/pedido.php';
include_once  __DIR__ . '/../models/estadoPedido.php';

class PedidoController {
    
    public function insertarPedido($request, $response, $args) {       
        $data = $request->getParsedBody();
    
        $id_mesa = $data['id_mesa'];
        $id_empleado = $data['id_empleado'];
        $codigo = $data['codigo'];
        $id_estado = $data['id_estado'];
        $tiempo_estimado = $data['tiempo_estimado'];        
        
        
        $validar = $this->validarDatos($data, $response);
        
        if($validar){
            
            $pedido = new Pedido(); 
            $pedido->id_mesa = $id_mesa;
            $pedido->id_empleado = $id_empleado;
            $pedido->codigo = $codigo;
            $pedido->id_estado = $id_estado;
            $pedido->tiempo_estimado = $tiempo_estimado;            
          
            $estadoMesa = Mesa::CambiarEstadoMesa($pedido->id_mesa, 6);

            $result = $pedido->InsertarPedido();
            
            if ($result) {
                $response->getBody()->write(json_encode(['success' => true, 'message' => 'El pedido se realizó con éxito.']));
            } else {
                $response->getBody()->write(json_encode(['success' => false, 'message' => 'Error al insertar el pedido']));
            }
        
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function validarDatos($data, $response){
    
        $id_mesa = $data['id_mesa'];
        $id_empleado = $data['id_empleado'];
        $codigo = $data['codigo'];
        $id_estado = $data['id_estado'];
        $tiempo_estimado = $data['tiempo_estimado'];   

        if (empty($id_mesa) || empty($id_empleado) || empty($codigo) || empty($id_estado) || empty($tiempo_estimado)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Ningún campo puede ser nulo.']));
            return false;
        }
    
        if (!is_numeric($tiempo_estimado)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El campo "tiempo_estimado" debe ser numérico.']));
            return false;
        }
    
        $mesa = $this->validarMesaExistente($id_mesa);
        $empleado = $this->validarEmpleadoExistente($id_empleado);
        $estado = $this->validarEstadoExistente($id_estado);
        
        if (!$mesa || !$empleado) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Los IDs de mesa y/o empleado no son válidos']));
            return false;
        }
        if(!$estado){
           
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El ID estado del pedido no es válido [1 = PENDIENTE, 2 = EN PROCESO, 3 = ENTREGADO, 4 = CANCELADO]']));
            return false;
        }
        

        if (Pedido::codigoRepetido($codigo)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El código del pedido ya está en uso.']));
            return false;
        }
       
        $pedidoExistente = Mesa::pedidoExistenteEnMesa($id_mesa);
        
        if ($pedidoExistente) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Esta mesa no se encuentra disponible.']));
            return false;
        }
       
        return true;
    }

    public function buscarPedidoPorId($request, $response, $args) {
        $id = $args['id'];

        $pedido = Pedido::TraerUnPedido($id);

        if ($pedido === false) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No existe ese id']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode($pedido));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function listarPedidos($request, $response, $args) {
        $pedidos = Pedido::TraerTodosLosPedidos();
    
        if (!$pedidos) {

            $payload = json_encode(['success' => false, 'message' => 'No hay pedidos disponibles']);
            $response->getBody()->write($payload);
        } else {
            $response->getBody()->write(json_encode(['success' => true, 'pedidos' => $pedidos]));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrarPedido($request, $response, $args) {

        $pedido = new Pedido();
        $pedido->id = $args['id'];
        $result = $pedido->BorrarPedido();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Pedido eliminado']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el pedido']));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarPedido($request, $response, $args) {
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
      
        $validar = $this->validarDatos($data, $response);
       
        if($validar){
            $pedido = new Pedido();
            $pedido->id = $args['id'];
            $pedido->id_mesa =  $data['id_mesa'];
            $pedido->id_empleado = $data['id_empleado'];
            $pedido->codigo = $data['codigo'];
            $pedido->id_estado = $data['id_estado'];
            $pedido->tiempo_estimado = $data['tiempo_estimado'];

            $result = $pedido->ModificarPedidoParametros();

            if ($result) {
                $response->getBody()->write(json_encode(['success' => true, 'message' => 'Pedido modificado']));
            } else {
                $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el pedido']));
            }
            return $response->withHeader('Content-Type', 'application/json');
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function cambiarEstadoPedido($request, $response, $args) {  

        $codigo_pedido = $request->getAttribute('codigo_pedido');
        $id_estado = $request->getAttribute('id_estado');  

        $pedido = Pedido::TraerUnPedidoPorCodigo($codigo_pedido);
        $nombreEstado = EstadoPedido::TraerUnEstadoPedido($pedido->id_estado);
        $cambiarPedido = Pedido::CambiarEstadoPedido($codigo_pedido, $id_estado);

        if(!$cambiarPedido){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se pudo cambiar el estado del pedido.']));                       
        }else{
          
            if($id_estado === 4){               
                $estadoMesa = Mesa::CambiarEstadoMesa($pedido->id_mesa, 5);       
            }else{
                $estadoMesa = Mesa::CambiarEstadoMesa($pedido->id_mesa, 6);               
            }
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Estado del pedido modificado a .' . $nombreEstado->nombre]));  
                             
        }
       
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function validarMesaExistente($id) {  
        if (is_numeric($id)) {    
            $mesa = Mesa::TraerUnaMesa($id);
            return $mesa;
        } else {
            return false; 
        }
    }

    private function validarEmpleadoExistente($id) {
        if (is_numeric($id)) {
            $empleado = Empleado::TraerUnEmpleado($id);
            return $empleado;
        } else {
            return false; 
        }
    }

    public function validarEstadoExistente($id) {
       
        if (is_numeric($id)) {
            $estado = EstadoPedido::TraerUnEstadoPedido($id);           
            return $estado;
        } else {
            return false; 
        }
    }
}





