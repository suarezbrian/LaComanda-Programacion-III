<?php

include_once  __DIR__ . '/../controller/empleadoController.php';
include_once  __DIR__ . '/../controller/mesaController.php';
include_once  __DIR__ . '/../controller/productoController.php';
include_once  __DIR__ . '/../controller/pedidoController.php';
include_once  __DIR__ . '/../controller/usuarioController.php';
include_once  __DIR__ . '/../middlewares/AuthMiddleware.php';
include_once  __DIR__ . '/../middlewares/EstadoPedidoMiddleware.php';
include_once  __DIR__ . '/../middlewares/ValidacionEmpleadoMiddleware.php';
include_once  __DIR__ . '/../middlewares/ValidacionMesaMiddleware.php';
include_once  __DIR__ . '/../middlewares/RoleMiddleware.php';
include_once  __DIR__ . '/../controller/pdfController.php';


use Slim\App;

return function (App $app) {
    $app->group('/api/empleados', function ($group) {
        
        $group->post('/add', 'EmpleadoController:insertarEmpleado')->setName('insertarEmpleado')->add(new ValidacionEmpleadoMiddleware());
        $group->get('/getAll', 'EmpleadoController:listarEmpleados');
        $group->get('/get/{id}', 'EmpleadoController:buscarEmpleadoPorId');
        $group->delete('/delete/{id}', 'EmpleadoController:borrarEmpleado');
        $group->put('/update/{id}', 'EmpleadoController:modificarEmpleado')->setName('modificarEmpleado')->add(new ValidacionEmpleadoMiddleware());
    })->add(new RoleMiddleware(['rolValido' => ['Socio']]))->add(new AuthMiddleware());

    $app->group('/api/mesas', function ($group) {
     
        $group->post('/add', 'MesaController:insertarMesa')->setName('insertarMesa')->add(new ValidacionMesaMiddleware());
        $group->get('/getAll', 'MesaController:listarMesas');
        $group->get('/get/{id}', 'MesaController:buscarMesaPorId');
        $group->delete('/delete/{id}', 'MesaController:borrarMesa');
        $group->put('/update/{id}', 'MesaController:modificarMesa')->setName('modificarMesa')->add(new ValidacionMesaMiddleware());
        $group->put('/update/estado/{id}', 'MesaController:modificarEstadoMesa')->add(new RoleMiddleware(['rolValido' => ['Socio']]));
        $group->get('/listar-mesas', 'MesaController:listarMesaYEstado')->add(new RoleMiddleware(['rolValido' => ['Socio']]));
        $group->get('/cobrar-mesas/{codigo_mesa}', 'MesaController:CalcularImportePedidoPorCodigoMesa')->add(new RoleMiddleware(['rolValido' => ['Mozo']]));
        $group->get('/mesa-mas-utilizada', 'MesaController:obtenerMesaMasUsada')->add(new RoleMiddleware(['rolValido' => ['Socio']]));
    })->add(new AuthMiddleware());

    $app->group('/api/productos', function ($group) {
       
        $group->post('/add', 'ProductoController:insertarProducto');
        $group->get('/getAll', 'ProductoController:listarProductos');
        $group->get('/get/{id}', 'ProductoController:buscarProductoPorId');
        $group->delete('/delete/{id}', 'ProductoController:borrarProducto');
        $group->put('/update/{id}', 'ProductoController:modificarProducto');
        $group->get('/masVendidos', 'ProductoController:productosMasVendidos');
        $group->get('/menosVendidos', 'ProductoController:productosMenosVendidos');
    })->add(new RoleMiddleware(['rolValido' => ['Socio']]))->add(new AuthMiddleware());

    $app->group('/api/pedidos', function ($group) {

        $group->post('/add', 'PedidoController:insertarPedido')->add(new RoleMiddleware(['rolValido' => ['Mozo']]));
        $group->get('/get/{id}', 'PedidoController:buscarPedidoPorId')->add(new RoleMiddleware(['rolValido' => ['Mozo', 'Socio']]));
        $group->put('/cambiarEstado', 'PedidoController:cambiarEstadoPedido')->add(new EstadoPedidoMiddleware())->add(new RoleMiddleware(['rolValido' => ['Mozo']]));
        $group->put('/cambiar-estado-producto', 'PedidoController:cambiarEstadoProductoDeUnPedido')->add(new RoleMiddleware(['rolValido' => ['Bartender', 'Cervecero', 'Cocinero']]));
        $group->get('/getAll', 'PedidoController:listarPedidos')->add(new RoleMiddleware(['rolValido' => ['Socio']]));
        $group->get('/listo-para-servir/getAll', 'PedidoController:listarPedidosListoParaServir')->add(new RoleMiddleware(['rolValido' => ['Mozo']]));
        $group->delete('/delete/{id}', 'PedidoController:borrarPedido')->add(new RoleMiddleware(['rolValido' => ['Mozo', 'Socio']]));
        $group->put('/update/{id}', 'PedidoController:modificarPedido')->add(new RoleMiddleware(['rolValido' => ['Mozo', 'Socio']]));
        $group->get('/listarPorEstado/{id}', 'PedidoController:listarPedidosPorEstado')->add(new RoleMiddleware(['rolValido' => ['Mozo', 'Socio']]));
        $group->get('/listarProductos/pedidos/{id_estado}', 'PedidoController:listarProductosPorEstado')->add(new RoleMiddleware(['rolValido' => ['Bartender', 'Cervecero', 'Cocinero']]));
        $group->post('/addProductos', 'PedidoController:agregarProductosAlPedido')->add(new RoleMiddleware(['rolValido' => ['Mozo']]));
        $group->get('/conRetraso', 'PedidoController:pedidosConRetraso')->add(new RoleMiddleware(['rolValido' => ['Socio']]));
        $group->get('/sinRetraso', 'PedidoController:pedidosSinRetraso')->add(new RoleMiddleware(['rolValido' => ['Socio']]));
        $group->get('/tiempo-demora/pedido/{codigo_mesa}/{codigo_pedido}', 'PedidoController:listarTiempoDemoraPedido')->add(new RoleMiddleware(['rolValido' => ['Usuario']]));
        $group->post('/cargar-encuesta', 'PedidoController:cargarEncuesta')->add(new RoleMiddleware(['rolValido' => ['Usuario']]));
        $group->get('/mejores-comentarios/{cantidad}', 'PedidoController:obtenerMejoresComentarios')->add(new RoleMiddleware(['rolValido' => ['Socio']]));
        

    })->add(new AuthMiddleware());

    $app->group('/api/usuarios', function ($group) {

        $group->post('/add', 'UsuarioController:insertarUsuario');
        $group->get('/get/{id}', 'UsuarioController:buscarUsuarioPorId');
        $group->get('/getAll', 'UsuarioController:listarUsuarios');
        $group->delete('/delete/{id}', 'UsuarioController:borrarUsuario');
        $group->put('/update/{id}', 'UsuarioController:modificarUsuario');
        $group->post('/cargar', 'UsuarioController:cargarUsuarios');
        $group->get('/descargar', 'UsuarioController:descargarUsuarios');

    })->add(new RoleMiddleware(['rolValido' => ['Socio']]))->add(new AuthMiddleware());

    // LOGINS
    $app->group('/api/login', function ($group) {
        $group->post('/usuarios', 'UsuarioController:login');
    });

    // PDF 
    $app->group('/api/pdf', function ($group) {       
    
        $group->get('/descargar-logo', 'PDFController:descargarPDFConLogo')->add(new RoleMiddleware(['rolValido' => ['Socio']]));
    })->add(new AuthMiddleware());
};