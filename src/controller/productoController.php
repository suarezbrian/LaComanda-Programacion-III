<?php

include_once  __DIR__ . '/../models/producto.php';

class ProductoController {
    
    public function insertarProducto($request, $response, $args) {
        $data = $request->getParsedBody();
        $nombre = $data['nombre'];
        $precio = $data['precio'];
        $categoria = $data['categoria'];
       
        $producto = new Producto();
        $producto->nombre = $nombre;
        $producto->precio = $precio;
        $producto->categoria = $categoria;
        $producto->cant_pedido = 0;
        
        $result = $producto->InsertarProductoParametros();


        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'id_producto' => $result,'nombre_producto' => $producto->nombre
            ,'message' => 'El producto fue agregado de forma exitosa.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se pudo agregar el producto.']));   
        } 
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function buscarProductoPorId($request, $response, $args) {
        $id = $args['id'];
        
        $producto = Producto::TraerUnProducto($id);
        
        if($producto === false) {
            $response->getBody()->write(json_encode(['success' => false, 'id_producto' => $id,'message' => 'El producto no existe.']));
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($producto));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarProductos($request, $response, $args) {
        $productos = Producto::TraerTodosLosProductos();

        $response->getBody()->write(json_encode($productos));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrarProducto($request, $response, $args) {
        $id = $args['id'];
        $producto = new Producto();
        $producto->id = $id;
        $result = $producto->BajaLogicaProducto();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Producto eliminado']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se pudo eliminar el producto']));   
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarProducto($request, $response, $args) {
        $id = $args['id'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        
        $nombre = $data['nombre'];
        $precio = $data['precio'];
        $categoria = $data['categoria'];
        
        $producto = new Producto();
        $producto->id = $id;
        $producto->nombre = $nombre;
        $producto->precio = $precio;
        $producto->categoria = $categoria;
        
        $result = $producto->ModificarProductoParametros();
            
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Producto modificado']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el producto']));
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function productosMasVendidos($request, $response, $args) {
        $productosMasVendidos = Producto::ObtenerProductosMasPedido();

        if (!$productosMasVendidos) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontraron productos más vendidos']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['success' => true, 'productos' => $productosMasVendidos]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function productosMenosVendidos($request, $response, $args) {
        $productosMenosVendidos = Producto::ObtenerProductosMenosPedido();

        if (!$productosMenosVendidos) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontraron productos menos vendidos']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['success' => true, 'productos' => $productosMenosVendidos]));
        return $response->withHeader('Content-Type', 'application/json');

    }
}