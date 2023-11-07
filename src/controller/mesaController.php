<?php

include_once  __DIR__ . '/../models/mesa.php';

class MesaController {

    public function insertarMesa($request, $response, $args) {
        $data = $request->getParsedBody();
    
        $codigo = $data['codigo'];
        $estado = $data['estado'];
       
        $mesa = new Mesa();
        $mesa->codigo = $codigo;
        $mesa->estado = $estado;
        
        $result = $mesa->InsertarMesaParametros();

        $response->getBody()->write(json_encode(['success' => $result ? true : false]));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function buscarMesaPorId($request, $response, $args) {
        $id = $args['id'];
        
        $mesa = Mesa::TraerUnaMesa($id);
        
        if($mesa === false) {
            $mesa = ['error' => 'No existe ese id'];
        }
        
        $response->getBody()->write(json_encode($mesa));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarMesas($request, $response, $args) {
        $mesas = Mesa::TraerTodasLasMesas();

        $response->getBody()->write(json_encode($mesas));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrarMesa($request, $response, $args) {
        $id = $args['id'];
        $mesa = new Mesa();
        $mesa->id = $id;
        $result = $mesa->BorrarMesa();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Mesa eliminada']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró la mesa']));   
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarMesa($request, $response, $args) {
        $id = $args['id'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);

        $codigo = $data['codigo'];
        $estado = $data['estado'];
        
        if (empty($codigo) || empty($estado)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Todos los campos deben completarse']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $mesa = new Mesa();
        $mesa->id = $id;
        $mesa->codigo = $codigo;
        $mesa->estado = $estado;
        
        $result = $mesa->ModificarMesaParametros();
            
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Mesa modificada']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró la mesa']));
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }
}