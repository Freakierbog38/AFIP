<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

class AnalisisVentasController extends Controller
{

    // Función que regresa la vista principal de este módulo
    public function show(){
        return view('modulos/AnalisisDeVentas', ['title' => 'Sección 1: Análisis de Ventas']);
    }

    ///////////////////////////////// VENTAS DE PRODUCTOS O SERVICIOS /////////////////////////////////

    // Regresa un array con los registros de Ventas de Productos o Servicios
    public function getVentasProdSer(){
        // Hace la consulta y lo guarda en una variable
        $prodSer = \DB::select('CALL II_pro_select_productos_servicios_id(?)', array(\Auth::user()->id_usuario));

        $totalUnidades = 0;
        $totalVentas = 0;

        // Variable que guarda la tabla a mostrar
        $tabla = '
        <table id="TablaAnalisisVentas" class="table display responsive nowrap">
            <thead>
                <tr>
                    <th>Producto/Servicio</th>
                    <th>Unidad al Mes</th>
                    <th>Precio Unitario en Pesos</th>
                    <th>Ventas en Pesos al Mes</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>';
        // Recorre los registros traidos de la base de datos
        foreach($prodSer as $row){
            // Y se agregan a la tabla
            $tabla .= '
                <tr>
                    <td>'.$row->nombre_producto_servicio_mezcla_productos_servicios_1_anio.'</td>
                    <td>'.$row->precio_u_producto_servicio_mezcla_productos_servicios_1_anio.' unidades</td>
                    <td>$ '.$row->us_producto_servicio_mezcla_productos_servicios_1_anio.'</td>
                    <td>$ '.$row->ventas_producto_servicio_mezcla_productos_servicios_1_anio.'</td>
                    <td><button onclick="llenarFormularioEdit('.$row->id_producto_servicio_mezcla_productos_servicios_1_anio.')" class="modal-effect btn-oblong btn-warning edit-ProSer" data-toggle="modal" data-effect="effect-slide-in-bottom"><i class="icon ion-edit"></i></button></td>
                    <td><button onclick="borrarProSer('.$row->id_producto_servicio_mezcla_productos_servicios_1_anio.')" class="btn-oblong btn-danger delete-ProSer"><i class="icon ion-trash-a"></i></button></td>
                </tr>
            ';
            $totalUnidades += $row->precio_u_producto_servicio_mezcla_productos_servicios_1_anio;
            $totalVentas += $row->ventas_producto_servicio_mezcla_productos_servicios_1_anio;
        }

        $totalPrecioUnit = $totalVentas / $totalUnidades;

        $tabla .= '<tr class="tx-bold bg-gray-900">
                    <td>Total</td>
                    <td>'.$totalUnidades.' unidades</td>
                    <td>$ '.$totalPrecioUnit.'</td>
                    <td>$ '.$totalVentas.'</td>
                    <td></td>
                    <td></td>
                </tr>';

        // Etiquetas de cierre de la tabla   
        $tabla .='
            </tbody>
        </table>';
        
        // Retorna en formato json
        return $tabla;
        
    }

    // Función que agrega un registro de Ventas de Productos o Servicios
    public function agregarVentasProSer(Request $request)
    {
        $rules = array(
            'ProductoServicio' => 'required',
            'UnidadesMes' => 'required',
            'PrecioUnitario' => 'required',
            'mult' => 'required'
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        \DB::select('CALL II_pro_insert_productos_servicios(?,?,?,?,?)', array(
            $request->ProductoServicio,
            $request->UnidadesMes,
            $request->PrecioUnitario,
            $request->mult,
            \Auth::user()->id_usuario
        ));

        return response()->json(['success' => '¡Agregado con éxito!']);
    }

    // Función que edita un registro de Ventas de Productos o Servicios
    public function editarVentasProSer(Request $request)
    {
        $rules = array(
            'ProductoServicio' => 'required',
            'UnidadesMes' => 'required',
            'PrecioUnitario' => 'required',
            'mult' => 'required'
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        \DB::select('CALL II_pro_edit_producto_servicio(?,?,?,?,?)', array(
            $request->editID,
            $request->ProductoServicio,
            $request->UnidadesMes,
            $request->PrecioUnitario,
            $request->mult
        ));

        return response()->json(['success' => '¡El registro ha sido modificado con éxito!']);

    }

    // Función que elimina un registro de Ventas de Productos o Servicios
    public function eliminarVentasProSer($id, Request $request)
    {
        $delete = \DB::select('CALL II_pro_delete_producto_servicio(?)', array($id));

        $mensaje = 'El registro fue eliminado';
        
        if($request->ajax()){
            return response()->json([
                'id' => $id,
                'mensaje' => $mensaje
            ]);
        }
        
        return 'Ocurrio un error';

    }

    // Función que devuelve el contenido de un registro de Producto o Servicio
    public function selectUnProSer($id, Request $request)
    {
        $proSer = \DB::select('CALL II_pro_select_producto_servicio(?)', array($id));
        
        if($request->ajax()){
            return \Response::json(array(
                'PD' => $proSer,
                'id' => $id,
                'request' => $request
            ));
        }
        else{
            return $id;
        }

    }

    ///////////////////////////////// ESTACIONALIDAD DE VENTAS /////////////////////////////////
    
    // Regresa un array con los registros de Estacionalidad de Ventas
    public function getEstacionalidadVentas(){
        // Hace la consulta y lo guarda en una variable
        $estVen = \DB::select('CALL II_pro_select_estacionalidad_ventas_id(?)', array(\Auth::user()->id_usuario));
        
        // Variable que tendrá un botón html para agregar o borrar el contenido de la tabla
        // Por defecto tendrá el de agregar
        $boton = '<a href="#FormEstacionVentas" class="modal-effect btn btn-oblong btn-success" data-toggle="modal" data-effect="effect-slide-in-bottom">Agregar</a>';

        // La estructura del datatable se guarda en una variable
        // Primero las cabeceras
        $tabla = '
            <table id="TablaEstacionVentas" class="table display responsive nowrap">
                <thead>
                    <tr>
                        <th>Número del Mes</th>
                        <th>Nombre del Mes</th>
                        <th>Ventas del Mes</th>
                        <th>Promedio del Mes</th>
                        <th>Índice</th>
                    </tr>
                </thead>
            <tbody>';
        // Luego con los datos obtenidos de la consulta se guardan en filas
        foreach($estVen as $row){
            $tabla .= '
                <tr>
                    <td>'.$row->n_mes.'</td>
                    <td>'.$row->mes_estacionalidad_ventas.'</td>
                    <td>'.$row->variacion_ventas_estacionalidad_ventas.'</td>
                    <td>'.$row->promedio_mes_estacionalidad_ventas.'</td>
                    <td>'.$row->indice_estacionalidad_ventas.'</td>
                </tr>
            ';
            // Cuando se encuentre al menos un registro se cambiará al botón de borrar
            $boton = '<button onclick="borrarEstacionVentas('.\Auth::user()->id_usuario.')" class="btn btn-oblong btn-danger mg-l-10">Eliminar todos los registros</button>';
        }
        
        // Finalmente las etiquetas de cierre
        $tabla .='
            </tbody>
        </table>';
        
        // Retorna la tabla
        // return $tabla;

        return response()->json([
            'tabla' => $tabla,
            'boton' => $boton
        ]);
        
    }

    // Función que agrega un registro de Ventas de Estacionalidada de Ventas
    public function agregarEstacionalidadVentas(Request $request)
    {
        $rules = array(
            'anio' => 'required',
            'Ventas1' => 'required',
            'Ventas2' => 'required',
            'Ventas3' => 'required',
            'Ventas4' => 'required',
            'Ventas5' => 'required',
            'Ventas6' => 'required',
            'Ventas7' => 'required',
            'Ventas8' => 'required',
            'Ventas9' => 'required',
            'Ventas10' => 'required',
            'Ventas11' => 'required',
            'Ventas12' => 'required',
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        \DB::select('CALL II_pro_insert_estacionalidad_ventas(?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array(
            $request->Ventas1,
            $request->Ventas2,
            $request->Ventas3,
            $request->Ventas4,
            $request->Ventas5,
            $request->Ventas6,
            $request->Ventas7,
            $request->Ventas8,
            $request->Ventas9,
            $request->Ventas10,
            $request->Ventas11,
            $request->Ventas12,
            \Auth::user()->id_usuario,
            $request->anio
        ));

        return response()->json(['success' => '¡Se han agregado los datos!']);
    }

    // Función que elimina un registro de Estacionalidad de Ventas
    public function eliminarEstacionalidadVentas($id, Request $request)
    {
        $delete = \DB::select('CALL II_pro_delete_estacionalidad_ventas(?)', array($id));

        $mensaje = 'Se han eliminado los registros';
        
        if($request->ajax()){
            return response()->json([
                'id' => $id,
                'mensaje' => $mensaje
            ]);
        }
        
        return 'Ocurrio un error';

    }

    ///////////////////////////////// RESULTADOS PRODUCTOS/SERVICIOS /////////////////////////////////
    
    // Regresa un array con los registros de Resultados
    public function getResultados(){
        // Hace la consulta y lo guarda en una variable
        $res = \DB::select('CALL II_pro_get_suma_valores_mezcla_productos_servicios_1_anio(?)', array(\Auth::user()->id_usuario));

        // Se inicia la variable para guardar el contenido html por mostrar
        $tabla = '';
        // Luego con los datos obtenidos de la consulta se guardan en filas
        foreach($res as $row){
            $tabla .= '
            <div class="col-md">
                <p class="invoice-info-row">
                    <span>Unidades al Mes</span>
                    <span>'.$row->us.' unidades</span>
                </p>
                <p class="invoice-info-row">
                    <span>Ventas al Mes</span>
                    <span>$ '.$row->ven.'</span>
                </p>
                <p class="invoice-info-row">
                    <span>Precio Promedio</span>
                    <span>$ '.$row->precio_promedio.'</span>
                </p>
            </div>';
        }
        
        // Retorna la tabla
        return $tabla;
        
    }
}
