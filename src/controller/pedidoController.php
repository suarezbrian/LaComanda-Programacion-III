<?php

include_once  __DIR__ . '/../models/mesa.php';
include_once  __DIR__ . '/../models/empleado.php';
include_once  __DIR__ . '/../models/pedido.php';

class PedidoController {
    
    public function insertarPedido($request, $response, $args) {       
        $data = $request->getParsedBody();
    
        $id_mesa = $data['id_mesa'];
        $id_empleado = $data['id_empleado'];
        $codigo = $data['codigo'];
        $estado = $data['estado'];
        $tiempo_estimado = $data['tiempo_estimado'];        
        
       
        $validar = $this->validarDatos($data, $response);
        
        if($validar){
            $pedido = new Pedido(); 
            $pedido->id_mesa = $id_mesa;
            $pedido->id_empleado = $id_empleado;
            $pedido->codigo = $codigo;
            $pedido->estado = $estado;
            $pedido->tiempo_estimado = $tiempo_estimado;
        
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
        $estado = $data['estado'];
        $tiempo_estimado = $data['tiempo_estimado'];   

        var_dump($estado);

        if (empty($id_mesa) || empty($id_empleado) || empty($codigo) || empty($estado) || empty($tiempo_estimado)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Ningún campo puede ser nulo.']));
            return false;
        }
    
        if (!is_numeric($tiempo_estimado)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El campo "tiempo_estimado" debe ser numérico.']));
            return false;
        }
    
        $mesa = $this->validarMesaExistente($id_mesa);
        $empleado = $this->validarEmpleadoExistente($id_empleado);
    
        if (!$mesa || !$empleado) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Los IDs de mesa y/o empleado no son válidos']));
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

        $response->getBody()->write(json_encode($pedidos));
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
            $pedido->estado = $data['estado'];
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
}





