<?php

include_once  __DIR__ . '/../models/mesa.php';
include_once  __DIR__ . '/../controller/PedidoController.php';

class MesaController {

    public function insertarMesa($request, $response, $args) {

        $pedido = new PedidoController();
        $validarEstado = $pedido->validarEstadoExistente($request->getAttribute('id_estado'));
        $validarMesa = $this->validarMesaExistentePorCodigo($request->getAttribute('codigo'));
        
        if(!$validarEstado){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El ID estado de la mesa no es válido']));
            return $response->withHeader('Content-Type', 'application/json');
        }
  
        if($validarMesa->codigo === $request->getAttribute('codigo')){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El codigo de la mesa ya está registrado.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $mesa = new Mesa();
        $mesa->codigo = $request->getAttribute('codigo');
        $mesa->id_estado = $request->getAttribute('id_estado');
        
        $result = $mesa->InsertarMesaParametros();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Mesa agregada de forma exitosa.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se pudo agregar la mesa']));   
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function buscarMesaPorId($request, $response, $args) {
        $id = $args['id'];
        
        $mesa = Mesa::TraerUnaMesa($id);
        
        if($mesa === false) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'La mesa no existe o no esta disponible para el uso.']));
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($mesa));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarMesas($request, $response, $args) {
        $mesas = Mesa::TraerTodasLasMesas();

        if ( count($mesas) > 0) {
            $response->getBody()->write(json_encode($mesas)); 
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No hay ninguna mesa activa registrada.']));   
        } 

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrarMesa($request, $response, $args) {
        $id = $args['id'];

        $existeMesa = Mesa::TraerUnaMesa($id);
       
        if($existeMesa === false) {
            $response->getBody()->write(json_encode(['success' => false, 'id_empleado' => $id,'message' => 'la mesa no existe o no esta disponible para el uso.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $mesa = new Mesa();
        $mesa->id = $id;
        $result = $mesa->BajaLogicaMesa();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'codigo_mesa' => $existeMesa->codigo,'message' => 'La mesa fue dada de baja de forma correcta.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró la mesa']));   
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarMesa($request, $response, $args) {
        $id = $args['id'];

        $existeMesa = Mesa::TraerUnaMesa($id);
       
        if($existeMesa === false) {
            $response->getBody()->write(json_encode(['success' => false, 'id_empleado' => $id,'message' => 'la mesa no existe o no esta disponible para el uso.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $existeMesa = Mesa::TraerUnaMesaPorCodigo($request->getAttribute('codigo'));
       
        if($existeMesa->id > 0) {
            
            $response->getBody()->write(json_encode(['success' => false, 'id_mesa' => $id,'message' => 'El codigo de la mesa ya esta en uso.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $mesa = new Mesa();
        $mesa->id = $id;
        $mesa->codigo = $request->getAttribute('codigo');
        $mesa->id_estado = $request->getAttribute('id_estado');
       
        $result = $mesa->ModificarMesaParametros();
            
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Mesa modificada']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró la mesa']));
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function validarMesaExistentePorCodigo($codigo) {  
        
        if (!empty($codigo)) {         
            $mesa = Mesa::TraerUnaMesaPorCodigo($codigo);                  
            return $mesa;
        } else {
            return false; 
        }
    }
}