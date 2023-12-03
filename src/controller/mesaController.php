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

    public function modificarEstadoMesa($request, $response, $args) {
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $id = $args['id'];

        $existeMesa = Mesa::TraerUnaMesa($id);
       
        if($existeMesa === false) {
            $response->getBody()->write(json_encode(['success' => false, 'id_empleado' => $id,'message' => 'la mesa no existe o no esta disponible para el uso.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        if($existeMesa->codigo != $data["codigo"]) {
            
            $response->getBody()->write(json_encode(['success' => false, 'id_mesa' => $id,'message' => 'El codigo no corresponde a la mesa.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $mesa = new Mesa();
        $mesa->id = $id;
        $mesa->codigo = $data["codigo"];
        $mesa->id_estado = $data["id_estado"];
       
        $result = $mesa->ModificarMesaParametros();
            
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Estado de la mesa modificada']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El estado de la mesa no se modifico']));
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarMesaYEstado($request, $response, $args) {
        $mesas = Mesa::TraerTodasLasMesas();
    
        if (count($mesas) > 0) {
            $mesaData = [];
            foreach ($mesas as $mesa) {
                $mesaData[] = [
                    'id' => $mesa->id,
                    'codigo' => $mesa->codigo,
                    'id_estado' => $mesa->id_estado,
                ];
            }
            $response->getBody()->write(json_encode(['success' => true, 'mesas' => $mesaData]));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No hay ninguna mesa activa registrada.']));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CalcularImportePedidoPorCodigoMesa($request, $response, $args) {
        $codigo_mesa = $args['codigo_mesa'];

        $mesa = Mesa::TraerUnaMesaPorCodigo($codigo_mesa);
        if($mesa === false) {
            $response->getBody()->write(json_encode(['success' => false, 'codigo_mesa' => $codigo_mesa,'message' => 'la mesa no existe o no esta disponible para el uso.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $pedido = Pedido::TraerUnPedidoPorIdMesa($mesa->id);
        if($pedido === false || $pedido->id_estado != 8) {
            $response->getBody()->write(json_encode(['success' => false, 'id_empleado' => $id,'message' => 'No hay pedidos activos para esta mesa.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $productos = ProductoPedido::TraerTodosProducto();     
        $importeTotal = 0;
        foreach ($productos as $item) {
            
            $producto = Producto::TraerUnProducto($item->id_producto);                        
            $idsProducto[] = $item->id_producto;
            $codigo_preparacion = "PROD_".$item->id_producto . $pedido->id;   
            
            if($item->codigo_preparacion == $codigo_preparacion){
                $importeTotal += $producto->precio;                
            }
            
        }

        $response->getBody()->write(json_encode(['success' => true,'message' => 'Importe a cobrar para la mesa.', 'codigo_mesa' => $codigo_mesa, 'importe_total' => $importeTotal]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function obtenerMesaMasUsada($request, $response, $args)
    {
        $mesaMasUsada = Mesa::ObtenerMesaMasUsada();
        
        if ($mesaMasUsada->cant_uso != 0) {
            $response->getBody()->write(json_encode(['success' => true, 'mesa_mas_usada' => $mesaMasUsada]));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No hay mesas mas usadas disponibles.']));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }
}