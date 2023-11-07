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
        
        $result = $producto->InsertarProductoParametros();

        $response->getBody()->write(json_encode(['success' => $result ? true : false]));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function buscarProductoPorId($request, $response, $args) {
        $id = $args['id'];
        
        $producto = Producto::TraerUnProducto($id);
        
        if($producto === false) {
            $producto = ['error' => 'No existe ese id'];
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
        $result = $producto->BorrarProducto();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Producto eliminado']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el producto']));   
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
}