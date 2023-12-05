<?php

include_once  __DIR__ . '/../models/mesa.php';
include_once  __DIR__ . '/../models/empleado.php';
include_once  __DIR__ . '/../models/pedido.php';
include_once  __DIR__ . '/../models/producto.php';
include_once  __DIR__ . '/../models/rol.php';
include_once  __DIR__ . '/../models/estadoPedido.php';
include_once  __DIR__ . '/../models/productoPedido.php';
include_once  __DIR__ . '/../models/encuesta.php';
include_once  __DIR__ . '/../controller/archivoController.php';

class PedidoController {

    const RUTA_PROYECTO = __DIR__ . '/../..';
    const RUTA_IMAGEN = self::RUTA_PROYECTO . '/imagen_pedidos/2023/';

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
            
            $mesa = Mesa::TraerUnaMesa($pedido->id_mesa);
            $contMesa= Mesa::IncrementarCantMesa($mesa->codigo);

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


            foreach($pedidos as $item){
                $prodPedido = ProductoPedido::TraerTodosProductoPorIdPedido($item->id);
                $item->id_empleado = $prodPedido[0]->id_empleado;                
            }

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
        $prodPedido = ProductoPedido::TraerTodosProductoPorIdPedido($id);
        $pedido->id_empleado = $prodPedido[0]->id_empleado;
        $response->getBody()->write(json_encode($pedido));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarPedidos($request, $response, $args) {
        $pedidos = Pedido::TraerTodosLosPedidos();
    
        if (!$pedidos) {

            $payload = json_encode(['success' => false, 'message' => 'No hay pedidos disponibles']);
            $response->getBody()->write($payload);
        } else {
            $pedidoData = [];
            foreach ($pedidos as $pedido) {
                $pedidoData[] = [
                    'id_pedido' => $pedido->id,
                    'id_mesa' => $pedido->id_mesa,
                    'codigo' => $pedido->codigo,
                    'tiempo_estimado' => $pedido->tiempo_estimado,
                    'estado' => $pedido->id_estado,
                    'importe' => $pedido->importe,
                ];
            }
            $response->getBody()->write(json_encode(['success' => true, 'pedidos' => $pedidoData]));
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
        $importeTotal = 0;
        foreach ($productos as $item) {
        
            $producto = Producto::TraerUnProducto($item["id"]);
            $importeTotal = $importeTotal + $producto->precio;
            $idsProducto[] = $item['id'];
            $codigo_preparacion = "PROD_".$item['id'] . $pedido->id;
            $values[] = "({$item['id']}, $pedido->id,'$codigo_preparacion')";
            
            if (!$producto) {
                $response->getBody()->write(json_encode(['success' => false, 'id_producto' => $item["id"],'message' => 'El producto no existe']));
                return $response->withHeader('Content-Type', 'application/json');
            } 
        }  

        $valuesString = implode(", ", $values);
        $agregarProducto = Pedido::InsertarProductosAPedido($valuesString);     
        $contProducto = Pedido::IncrementarCantPedido($idsProducto);
        $estadoPedido = Pedido::CambiarEstadoPedido($pedido->codigo, 2, null);
        $importe = Pedido::ModificarPedidoImporte($pedido->id,$importeTotal);
        
        if($agregarProducto){
            $response->getBody()->write(json_encode(['success' => true, 'pedido' => $codigoPedido ,'message' => 'Productos agregados al pedido']));
        }else{
            $response->getBody()->write(json_encode(['success' => false, 'pedido' => $codigoPedido ,'message' => 'Problema a la hora de agregar los productos.']));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    
    public function listarProductosPorEstado($request, $response, $args) {
        $id_estado = $args['id_estado'];    
        $usuario = $request->getAttribute('usuario');

        $listaProductos = ProductoPedido::TraerProductoPorEstado($id_estado);   
        
        $rolUsuario = Rol::TraerUnRol($usuario->id_rol)->nombre;
        $categoriasPermitidas =$this->obtenerCategoriasPorRol($rolUsuario);

        if (!$listaProductos) {
            $response->getBody()->write(json_encode(['success' => false,'id_estado' => $id_estado ,'message' => 'No hay productos para el estado.']));
        } else {   
            $nombreRol = EstadoPedido::TraerUnEstadoPedido($id_estado);   
                
            foreach($listaProductos as $item){   
              
                $producto = Producto::TraerUnProducto($item->id_producto);                
                $pedido = Pedido::TraerUnPedido($item->id_pedido);            
                $codigo_preparacion = "PROD_". $item->id_producto . $item->id_pedido;
                if (in_array($producto->categoria, $categoriasPermitidas)) {
                    $arrayProducto[] = [
                        'id' => $item->id,
                        'nombre' => $producto->nombre,
                        'precio' => $producto->precio,
                        'categoria' => $producto->categoria,
                        'codigo_pedido' => $pedido->codigo,
                        'codigo_prepracion' => $codigo_preparacion
                    ];
                }
            }
            $response->getBody()->write(json_encode(['success' => true, 'estado' => $nombreRol->nombre ,'productos' => $arrayProducto]));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function obtenerCategoriasPorRol($idRol)
    {
        $categoriasPorRol = [
            'Bartender' => ['Bebida'],
            'Cervecero' => ['Bebida con alcohol'],
            'Cocinero' => ['Comida'],
        ];

        $categoriasPermitidas = $categoriasPorRol[$idRol] ?? [];

        return $categoriasPermitidas;
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

    public function cambiarEstadoProductoDeUnPedido($request, $response, $args) {  

        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $usuario = $request->getAttribute('usuario');
        
        $codigo_preparacion = $data['codigo_preparacion'];
        $tiempo_estimado = $data['tiempo_estimado'];
        $id_estado = $data['id_estado'];

        $emp = Empleado::TraerUnEmpleadoPorIdUsuario($usuario->id_usuario);        
        $productoPedido = ProductoPedido::TraerProductoPorCodigoPreparacion($codigo_preparacion);  

        $producto = Producto::TraerUnProducto($productoPedido->id_producto);        
        $rolUsuario = Rol::TraerUnRol($usuario->id_rol)->nombre;
        $categoriasPermitidas =$this->obtenerCategoriasPorRol($rolUsuario);

        if(!in_array($producto->categoria, $categoriasPermitidas)){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El empleado no puede manejar este producto.']));  
            return $response->withHeader('Content-Type', 'application/json');
        }

        if(!$productoPedido){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se pudo cambiar el estado del producto.']));                       
        }else{
            $prodPedido = new ProductoPedido();
            $prodPedido->id = $productoPedido->id;
            $prodPedido->tiempo_estimado = $tiempo_estimado;
            $prodPedido->id_estado = $id_estado;
            $prodPedido->id_empleado = $emp->id;
            $prodPedido->ModificarProductoPedido();

            if($id_estado == 2){
                $pedido = new Pedido();
                $pedido->id = $productoPedido->id_pedido;                   
                $pedido->ModificarPedidoTiempoEstimado($prodPedido->tiempo_estimado);
            }

            $fechaExiste = Pedido::TraerUnPedido($productoPedido->id_pedido);
         
            if($fechaExiste->fecha_inicio == NULL){
                $ahora = time();
                $dateFormatted = date('Y-m-d H:i:s', $ahora);    
                $pedido->ModificarPedidoFechaInicio($dateFormatted);
            }

            $nombreRol = EstadoPedido::TraerUnEstadoPedido($id_estado);

            $pedidosEntregados = ProductoPedido::VerificarProductosEntregados($productoPedido->id_pedido);
            if($pedidosEntregados){
                $pedido = Pedido::TraerUnPedido($productoPedido->id_pedido);             
                
                /* TODO: TESTEAR
                $ahora = new DateTime();
                $fechaInicio = new DateTime($pedido->fecha_inicio);
                $diferencia = $ahora->getTimestamp() - $fechaInicio->getTimestamp();
        
                if ($diferencia > $pedido->tiempo_estimado) {
                    $tiempoRetraso = $diferencia - $pedido->tiempo_estimado;            
                    $retraso = date('H:i:s', $tiempoRetraso);
                } else {
                    $retraso = '00:00:00';
                }
                */
                // EN EL NULL LE PASA EL TIEMPO RETRASO
                $cambiarPedido = Pedido::CambiarEstadoPedido($pedido->codigo, 8, null);
            }
            
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Estado del pedido modificado a .' . $nombreRol->nombre]));                             
        }
       
        return $response->withHeader('Content-Type', 'application/json');
    }

    
    public function listarTiempoDemoraPedido($request, $response, $args) {     
    
        $codigo_mesa = $args['codigo_mesa'];
        $codigo_pedido = $args['codigo_pedido'];        
        
        $pedido = Pedido::TraerUnPedidoPorCodigo($codigo_pedido);        
        if(!$pedido){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontro el pedido.']));
            return $response->withHeader('Content-Type', 'application/json');                      
        }else if($pedido->id_estado == 4){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El pedido fue cancelado.']));
            return $response->withHeader('Content-Type', 'application/json');    
        }else if($pedido->id_estado == 1){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El cliente no termino de pedir los productos para este pedido.']));
            return $response->withHeader('Content-Type', 'application/json');    
        }else if($pedido->tiempo_estimado == null){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Los productos todavia estan en espera.']));
            return $response->withHeader('Content-Type', 'application/json');  
        }

        $mesa = Mesa::TraerUnaMesaPorCodigo($codigo_mesa);
        if(!$mesa){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontro la mesa.']));
            return $response->withHeader('Content-Type', 'application/json');                      
        }

        if($mesa->id != $pedido->id_mesa){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'La mesa no corresponde al pedido.']));
            return $response->withHeader('Content-Type', 'application/json');                     
        }

        $response->getBody()->write(json_encode(['success' => true, 'tiempo_estimado' => $pedido->tiempo_estimado,'message' => 'Tiempo estimado del pedido en segundos.']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarPedidosListoParaServir($request, $response, $args) {
        $hayPedidos = false;
        $pedidos = Pedido::TraerTodosLosPedidos();
        if (!$pedidos) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'No hay pedidos listo para servir.']));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $pedidoData = [];
            foreach ($pedidos as $pedido) {
                if($pedido->id_estado == 8){
                    $pedidoData[] = [
                        'codigo' => $pedido->codigo,
                        'tiempo_estimado' => $pedido->tiempo_estimado,   
                        'id_estado' => $pedido->id_estado                 
                    ];
                    $hayPedidos = true;
                }
            }

            if($hayPedidos){
                $response->getBody()->write(json_encode(['success' => true, 'pedidos' => $pedidoData]));
            }else{
                $response->getBody()->write(json_encode(['success' => true, 'message' => 'No hay pedidos listo para servir.']));
            }
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function cargarEncuesta($request, $response, $args) {
        $data = $request->getParsedBody();

        $codigo_pedido = $data['codigo_pedido'];
        $codigo_mesa = $data['codigo_mesa'];
        $descripcion = $data['descripcion'];
        $valoracion = $data['valoracion'];

                
        $pedido = Pedido::TraerUnPedidoPorCodigo($codigo_pedido);        
        if(!$pedido){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontro el pedido.']));
            return $response->withHeader('Content-Type', 'application/json');                      
        }

        $mesa = Mesa::TraerUnaMesa($pedido->id_mesa);

        if($mesa->codigo != $codigo_mesa){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El codigo de la mesa no corresponde a este pedido.']));
            return $response->withHeader('Content-Type', 'application/json');    
        }

        if($valoracion < 0 || $valoracion > 5){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Valoración: puede valorar el servicio de 0 a 5 estrellas.']));
            return $response->withHeader('Content-Type', 'application/json');  
        }

        $enc = new Encuesta();
        $enc->codigo_pedido = $codigo_pedido;
        $enc->codigo_mesa = $codigo_mesa;
        $enc->descripcion = $descripcion;
        $enc->valoracion = $valoracion;

        $result = $enc->InsertarEncuesta();

        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'La encuesta se realizó con éxito.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Error al realizar la encuesta']));
        }

        return $response->withHeader('Content-Type', 'application/json');  

    }

    public function obtenerMejoresComentarios($request, $response, $args) {
        
        $cantidad = $args['cantidad'];

        $mejoresComentarios = Encuesta::ObtenerMejoresComentarios($cantidad);
        
        if (empty($mejoresComentarios)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No hay comentarios disponibles.']));
        } else {
            $comentarios = array_map(function ($comentario) {
                return [
                    'codigo_pedido' => $comentario->codigo_pedido,
                    'codigo_mesa' => $comentario->codigo_mesa,
                    'descripcion' => $comentario->descripcion,
                    'valoracion' => $comentario->valoracion
                ];
            }, $mejoresComentarios);
    
            $response->getBody()->write(json_encode(['success' => true, 'mejores_comentarios' => $comentarios]));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function subirFotoMesa($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $codigoPedido = $data['codigo_pedido']; 
        $codigoMesa = $data['codigo_mesa']; 

        $pedido = Pedido::TraerUnPedidoPorCodigo($codigoPedido);
        if (!$pedido) {
            $response->getBody()->write(json_encode(['success' => false, 'codigo_pedido' => $codigoPedido,'message' => 'El pedido no existe']));
            return $response->withHeader('Content-Type', 'application/json');
        } 

        $mesa = Mesa::TraerUnaMesa($pedido->id_mesa);

        if($mesa->codigo != $codigoMesa){
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'El codigo de la mesa no corresponde a este pedido.']));
            return $response->withHeader('Content-Type', 'application/json');    
        }
       
       
        if(ArchivoController::validarImagen()){
            $imgRuta = self::RUTA_IMAGEN . "{$codigoPedido}_{$codigoMesa}.jpg";
            if(ArchivoController::cargarImagen($response, $imgRuta)){                           
                
                $imagen = Pedido::ModificarPedidoRutaImagen($pedido->id,$imgRuta);
                $response->getBody()->write(json_encode(['success' => true,'message' => 'La imagen se cargo de forma correcta.']));
                
            }else{
                $response->getBody()->write(json_encode(['success' => false, 'message' => 'Ocurrió algún error al subir la imagen.No pudo guardarse correctamente.']));
            }
        }else{
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'La extensión o el tamaño de los archivos no es correcta. Se permiten archivos .png o .jpg. Con tamaño maximo de 100 kb']));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

}





