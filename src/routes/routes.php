<?php

include_once  __DIR__ . '/../controller/empleadoController.php';
include_once  __DIR__ . '/../controller/mesaController.php';
include_once  __DIR__ . '/../controller/productoController.php';
include_once  __DIR__ . '/../controller/pedidoController.php';
include_once  __DIR__ . '/../controller/usuarioController.php';
include_once  __DIR__ . '/../middlewares/AuthMiddleware.php';
include_once  __DIR__ . '/../middlewares/EstadoPedidoMiddleware.php';

use Slim\App;

return function (App $app) {
    $app->group('/api/empleados', function ($group) {
        
        $group->post('/add', 'EmpleadoController:insertarEmpleado');
        $group->get('/getAll', 'EmpleadoController:listarEmpleados');
        $group->get('/get/{id}', 'EmpleadoController:buscarEmpleadoPorId');
        $group->delete('/delete/{id}', 'EmpleadoController:borrarEmpleado');
        $group->put('/update/{id}', 'EmpleadoController:modificarEmpleado');
    })->add(new AuthMiddleware(['rolValido' => ['admin']]));

    $app->group('/api/mesas', function ($group) {
     
        $group->post('/add', 'MesaController:insertarMesa');
        $group->get('/getAll', 'MesaController:listarMesas');
        $group->get('/get/{id}', 'MesaController:buscarMesaPorId');
        $group->delete('/delete/{id}', 'MesaController:borrarMesa');
        $group->put('/update/{id}', 'MesaController:modificarMesa');
    })->add(new AuthMiddleware(['rolValido' => ['admin', 'empleado']]));

    $app->group('/api/productos', function ($group) {
       
        $group->post('/add', 'ProductoController:insertarProducto');
        $group->get('/getAll', 'ProductoController:listarProductos');
        $group->get('/get/{id}', 'ProductoController:buscarProductoPorId');
        $group->delete('/delete/{id}', 'ProductoController:borrarProducto');
        $group->put('/update/{id}', 'ProductoController:modificarProducto');
    })->add(new AuthMiddleware(['rolValido' => ['admin', 'empleado']]));

    $app->group('/api/pedidos', function ($group) {

        $group->post('/add', 'PedidoController:insertarPedido');
        $group->get('/get/{id}', 'PedidoController:buscarPedidoPorId');
        $group->put('/cambiarEstado', 'PedidoController:cambiarEstadoPedido')->add(new EstadoPedidoMiddleware());
        $group->get('/getAll', 'PedidoController:listarPedidos');
        $group->delete('/delete/{id}', 'PedidoController:borrarPedido');
        $group->put('/update/{id}', 'PedidoController:modificarPedido');
    })->add(new AuthMiddleware(['rolValido' => ['admin', 'empleado', 'usuario']]));

    $app->group('/api/usuarios', function ($group) {

        $group->post('/add', 'UsuarioController:insertarUsuario');
        $group->get('/get/{id}', 'UsuarioController:buscarUsuarioPorId');
        $group->get('/getAll', 'UsuarioController:listarUsuarios');
        $group->delete('/delete/{id}', 'UsuarioController:borrarUsuario');
        $group->put('/update/{id}', 'UsuarioController:modificarUsuario');
    })->add(new AuthMiddleware(['rolValido' => ['admin']]));
};