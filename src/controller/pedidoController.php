<?php

include_once  __DIR__ . '/../models/mesa.php';
include_once  __DIR__ . '/../models/empleado.php';
include_once  __DIR__ . '/../models/pedido.php';
include_once  __DIR__ . '/../models/producto.php';
include_once  __DIR__ . '/../models/rol.php';
include_once  __DIR__ . '/../models/estadoPedido.php';

class PedidoController {
    
    public function insertarPedido($request, $response, $args) {       
        $data = $request->getParsedBody();
    
        $id_mesa = $data['id_mesa'];
        $codigo = $data['codigo'];
        $id_estado = 7; // 7= PIDIENDO.  

        $validar = $this->validarDatos($data, $response);

        if($validar){
            
            $pedido = new Pedido(); 
            $pedido->id_mesa = $id_mesa;
            $pedido->codigo = $codigo;
            $pedido->id_estado = $id_estado;
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
        $codigo = $data['codigo'];
        $id_estado = 1;
        
        if (empty($id_mesa) || empty($codigo)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Ningún campo puede ser nulo.']));
            return false;
        }
    
        $mesa = $this->validarMesaExistente($id_mesa);
        $estado = $this->validarEstadoExistente($id_estado);
        
        if (!$mesa) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Los IDs de mesa y/o empleado no son válidos']));
            return false;
        }
        if(!$estado){
           
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El ID estado del pedido no es válido [1 = PENDIENTE, 2 = EN PROCESO, 3 = ENTREGADO, 4 = CANCELADO]']));
            return false;
        }

               
        $pedidoExistente = Mesa::pedidoExistenteEnMesa($id_mesa);
        
        if ($pedidoExistente) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Esta mesa no se encuentra disponible.']));
            return false;
        }
        

        if (Pedido::codigoRepetido($codigo)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El código del pedido ya está en uso.']));
            return false;
        }

       
        return true;
    }

    public function listarPedidosPorEstado($request, $response, $args) {
        $estado_pedido = $args['id'];

        if (!is_numeric($estado_pedido)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El campo "estado_pedido" debe ser numérico.']));
            return $response->withHeader('Content-Type', 'application/json');
        }
    
        $pedidos = Pedido::TraerPedidosPorEstado($estado_pedido);
        $nombreEstado = EstadoPedido::TraerUnEstadoPedido($estado_pedido);
        
        if (!$pedidos) {
            $response->getBody()->write(json_encode(['success' => false, 'Estado ' => $nombreEstado->nombre, 'message' => 'No hay pedidos disponibles para el estado especificado.']));
        } else {
            $response->getBody()->write(json_encode(['success' => true, 'Estado ' => $nombreEstado->nombre,'pedidos' => $pedidos]));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
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
        $result = $pedido->BajaLogicaPedido();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Pedido cancelado.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el pedido']));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarPedido($request, $response, $args) {
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
             
      
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
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function cambiarEstadoPedido($request, $response, $args) {  

        $codigo_pedido = $request->getAttribute('codigo_pedido');
        $id_estado = $request->getAttribute('id_estado');  


        $pedido = Pedido::TraerUnPedidoPorCodigo($codigo_pedido);
        $nombreEstado = EstadoPedido::TraerUnEstadoPedido($pedido->id_estado);

        
        $ahora = new DateTime();
        $fechaInicio = new DateTime($pedido->fecha_inicio);
        $diferencia = $ahora->getTimestamp() - $fechaInicio->getTimestamp();

        if ($diferencia > $pedido->tiempo_estimado) {
            $tiempoRetraso = $diferencia - $pedido->tiempo_estimado;            
            $retraso = date('H:i:s', $tiempoRetraso);
        } else {
            $retraso = '00:00:00';
        }

        $cambiarPedido = Pedido::CambiarEstadoPedido($codigo_pedido, $id_estado, $retraso);
        $nombreEstado = EstadoPedido::TraerUnEstadoPedido($id_estado); 

        if(!$cambiarPedido){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se pudo cambiar el estado del pedido.']));                       
        }else{
          
            if($id_estado === 4)
            {               
                $estadoMesa = Mesa::CambiarEstadoMesa($pedido->id_mesa, 5);       
            }
            if($id_estado === 3){
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

    public function asignarEmpleadoPedido($request, $response, $args)
    {
        $data = $request->getParsedBody();

        $idEmpleado = $data['id_empleado'];
        $tiempoEstimado = $data['tiempo_estimado'];
        $codigoPedido = $data['codigo_pedido'];

        $empleado = Empleado::TraerUnEmpleado($idEmpleado);
        if (!$empleado) {
            $response->getBody()->write(json_encode(['success' => false, 'id' => $idEmpleado,'message' => 'El empleado no existe']));
            return $response->withHeader('Content-Type', 'application/json');
        }   

        $pedido = Pedido::TraerUnPedidoPorCodigo($codigoPedido);
        if (!$pedido) {
            $response->getBody()->write(json_encode(['success' => false, 'codigo' => $codigoPedido,'message' => 'No existe un pedido.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $producto = Pedido::ObtenerProductosPorPedido($pedido->id);

        if (empty($producto)) {
            $response->getBody()->write(json_encode(['success' => false, 'codigo' => $codigoPedido,'message' => 'Aún no se pidieron los productos, para este pedido.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $result = $pedido->AsignarEmpleado($idEmpleado, $tiempoEstimado, $codigoPedido);


        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'codigo' => $codigoPedido, 'empleado' => $empleado->nombre, 'timepo_estimado' => $tiempoEstimado,
            'message' => 'Pedido asignado al empleado']));
        } else {
            $estado = EstadoPedido::TraerUnEstadoPedido($pedido->id_estado)->nombre;  
            $response->getBody()->write(json_encode(['success' => false, 'codigo' => $codigoPedido,'estado' => $estado, 'message' => 'No se pudo asignar el pedido al empleado']));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function agregarProductosAlPedido($request, $response, $args) {
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
    
        $codigoPedido = $data['codigo_pedido'];
        $productos = $data['productos'];

        $pedido = Pedido::TraerUnPedidoPorCodigo($codigoPedido);
        if (!$pedido) {
            $response->getBody()->write(json_encode(['success' => false, 'codigo_pedido' => $codigoPedido,'message' => 'El pedido no existe']));
            return $response->withHeader('Content-Type', 'application/json');
        } 

        if($pedido->id_estado === 3){
            $response->getBody()->write(json_encode(['success' => false, 'codigo_pedido' => $codigoPedido,'message' => 'No puedes agregar productos a pedidos entregados.']));
            return $response->withHeader('Content-Type', 'application/json');
        }
       
        foreach ($productos as $item) {
        
            $producto = Producto::TraerUnProducto($item["id"]);
            $idsProducto[] = $item['id'];
            $values[] = "({$item['id']}, $pedido->id)";

            if (!$producto) {
                $response->getBody()->write(json_encode(['success' => false, 'id_producto' => $item["id"],'message' => 'El producto no existe']));
                return $response->withHeader('Content-Type', 'application/json');
            } 
        }      
        $valuesString = implode(", ", $values);
        $agregarProducto = Pedido::InsertarProductosAPedido($valuesString);
        $contProducto = Pedido::IncrementarCantPedido($idsProducto);
        $estadoPedido = Pedido::CambiarEstadoPedido($pedido->codigo, 2, null);

        if($agregarProducto){
            $response->getBody()->write(json_encode(['success' => true, 'pedido' => $codigoPedido ,'message' => 'Productos agregados al pedido']));
        }else{
            $response->getBody()->write(json_encode(['success' => false, 'pedido' => $codigoPedido ,'message' => 'Problema a la hora de agregar los productos.']));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarProductosPorPedido($request, $response, $args) {
        $codigoPedido = $args['codigo_pedido'];    
        
        $pedido = Pedido::TraerUnPedidoPorCodigo($codigoPedido);
        if (!$pedido) {
            $response->getBody()->write(json_encode(['success' => false, 'codigo_pedido' => $codigoPedido,'message' => 'El pedido no existe']));
            return $response->withHeader('Content-Type', 'application/json');
        } 
        
        $listaProductos = Pedido::ObtenerProductosPorPedido($pedido->id);
        
        if (!$listaProductos) {
            $response->getBody()->write(json_encode(['success' => false,'pedido' => $codigoPedido ,'message' => 'No hay productos para el pedido especificado']));
        } else {            
            foreach($listaProductos as $id){           
                $producto = Producto::TraerUnProducto($id);
                $arrayProducto[] = [
                    'id' => $id,
                    'nombre' => $producto->nombre,
                    'precio' => $producto->precio
                ];
            }
            $response->getBody()->write(json_encode(['success' => true, 'pedido' => $codigoPedido ,'productos' => $arrayProducto]));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function pedidosConRetraso($request, $response, $args)
    {
        $pedidosConRetraso = Pedido::ObtenerPedidosConRetraso();

        if (!$pedidosConRetraso) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontraron pedidos con retraso']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['success' => true, 'pedidos' => $pedidosConRetraso]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function pedidosSinRetraso($request, $response, $args)
    {
        $pedidosSinRetraso = Pedido::ObtenerPedidosSinRetraso();

        if (!$pedidosSinRetraso) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontraron pedidos sin retraso']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['success' => true, 'pedidos' => $pedidosSinRetraso]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}





