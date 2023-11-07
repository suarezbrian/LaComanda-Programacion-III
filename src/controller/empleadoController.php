<?php

include_once  __DIR__ . '/../models/empleado.php';

class EmpleadoController {

    public function insertarEmpleado($request, $response, $args) {
        $data = $request->getParsedBody();
    
        $nombre = $data['nombre'];
        $rol = $data['rol'];
        $contacto = $data['contacto'];
       
        $emp = new Empleado();
        $emp->nombre = $nombre;
        $emp->rol = $rol;
        $emp->contacto = $contacto;
        
        $result = $emp->InsertarEmpleadoParametros();

        $response->getBody()->write(json_encode(['success' => $result ? true : false]));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function buscarEmpleadoPorId($request, $response, $args) {
        $id = $args['id'];
        
        $empleados = Empleado::TraerUnEmpleado($id);
        
        if($empleados === false) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No existe ese id']));
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($empleados));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarEmpleados($request, $response, $args) {
        $empleados = Empleado::TraerTodoLosEmpleado();

        $response->getBody()->write(json_encode($empleados));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrarEmpleado($request, $response, $args) {
        $id = $args['id'];
        $emp = new Empleado();
        $emp->id = $id;
        $result = $emp->BorrarEmpleado();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Empleado eliminado']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el empleado']));   
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarEmpleado($request, $response, $args) {
        $id = $args['id'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);

        $nombre = $data['nombre'];
        $rol = $data['rol'];
        $contacto = $data['contacto'];
        
        if (empty($nombre) || empty($rol) || empty($contacto)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Todos los campos deben completarse']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $emp = new Empleado();
        $emp->id = $id;
        $emp->nombre = $nombre;
        $emp->rol = $rol;
        $emp->contacto = $contacto;
        
        $result = $emp->ModificarEmpleadoParametros();
            
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Empleado modificado']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el empleado']));
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }
}
