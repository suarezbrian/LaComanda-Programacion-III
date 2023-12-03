<?php

include_once  __DIR__ . '/../models/empleado.php';

class EmpleadoController {

    public function insertarEmpleado($request, $response, $args) {

        $emp = new Empleado();
        $emp->nombre =  $request->getAttribute('nombre');
        $emp->id_usuario = $request->getAttribute('id_usuario');
        $emp->contacto = $request->getAttribute('contacto');
        $emp->activo = 0;
        $ahora = time();
        $dateFormatted = date('Y-m-d H:i:s', $ahora);
        $emp->fecha_creacion = $dateFormatted;  

        $result = $emp->InsertarEmpleadoParametros();

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true, 'id_empleado' => $result,'nombre_empleado' => $emp->nombre
            ,'message' => 'El empleado fue agregado de forma exitosa.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se pudo agregar el empleado.']));   
        } 

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function buscarEmpleadoPorId($request, $response, $args) {
        $id = $args['id'];
        
        $empleados = Empleado::TraerUnEmpleado($id);
        
        if($empleados === false) {
            $response->getBody()->write(json_encode(['success' => false, 'id_empleado' => $id,'message' => 'El empleado no existe o esta dado de baja.']));
            return $response->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($empleados));    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarEmpleados($request, $response, $args) {
        $empleados = Empleado::TraerTodoLosEmpleado();
 
        if ( count($empleados) > 0) {
            $response->getBody()->write(json_encode($empleados)); 
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No hay ningun empleado activo registrado.']));   
        } 
           
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function borrarEmpleado($request, $response, $args) {
        $id = $args['id'];  

        $empleado = Empleado::TraerUnEmpleado($id);
       
        if($empleado === false) {
            $response->getBody()->write(json_encode(['success' => false, 'id_empleado' => $id,'message' => 'El empleado no existe o esta dado de baja.']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $emp = new Empleado();
        $emp->id = $id;
        $result = $emp->BajaLogicaDeLaDB($id);

        if ($result > 0) {
            $response->getBody()->write(json_encode(['success' => true,'nombre' => $empleado->nombre ,'message' => 'El empleado fue dado de baja de forma correcta.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el empleado o ya esta dado de baja.']));   
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarEmpleado($request, $response, $args) {
        $id = $args['id'];

        $emp = new Empleado();
        $emp->id = $id;
        $emp->nombre =  $request->getAttribute('nombre');
        $emp->contacto = $request->getAttribute('contacto');

        $result = $emp->ModificarEmpleadoParametros();
       
        if ($result) {
            $response->getBody()->write(json_encode(['success' => true, 'id_empleado' => $id,'message' => 'Empleado modificado de forma correcta.']));
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No se encontró el empleado o está dado de baja.']));
        }    
        return $response->withHeader('Content-Type', 'application/json');
    }
}
