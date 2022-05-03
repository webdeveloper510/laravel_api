<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FexController extends Controller
{


  // ----------------------------------------------Fex Estimate-------------------------------------------------

    function FexCotizer(Request $request){

        $url = "https://fex.cl/fex_api/externo/flete/cotizar";

      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      
      $headers = array(
        "Content-Type: application/json",
     );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      
      $data = array();

    $data['acceso'] =$request->acceso;
    $data['ori_lat'] =$request->ori_lat;
    $data['ori_lng'] =$request->ori_lng;
    $data['des_lat'] =$request->des_lat;
    $data['des_lng'] =$request->des_lng;
    $data['vehiculo'] =$request->vehiculo;
    $data['reg_origen'] =$request->reg_origen;
      
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl);
      curl_close($curl);
      return $resp;

    }

// ------------------------------------------------Fex Shipping------------------------------------------------

    function  FexSolicitar(Request $request){

     // print_r($request->all());die;

        $url = "https://fex.cl/fex_api/externo/flete/solicitar";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
          "Content-Type: application/json"
       );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
     $data = array();

    $data['acceso'] =$request->acceso;
    $data['ori_lat'] =$request->ori_lat;
    $data['ori_lng'] =$request->ori_lng;
    $data['dir_origen'] =$request->dir_origen;
    $data['des_lat'] =$request->des_lat;
    $data['des_lng'] =$request->des_lng;
    $data['dir_destino'] =$request->dir_destino;
    $data['des_carga'] =$request->des_carga;
    $data['rec_nom'] =$request->rec_nom;
    $data['rec_tel'] =$request->rec_tel;
    $data['vehiculo'] =$request->vehiculo;
    $data['programado'] =$request->programado;
    $data['reg_origen'] =$request->reg_origen;
    $data['extra'] =$request->extra;

    
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
  
    }
// ----------------------------------------------Fex Cancellation----------------------------------------------

    function PostFexCancellation(Request $request){
            // echo "<pre>";
            // print_r($request->all());die;
      $url = "https://fex.cl/fex_api/externo/flete/cambiar_estado";

      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      
      $headers = array(
        "Content-Type: application/json",
     );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      
   $data = array();

      $data['acceso'] =$request->acceso;
      $data['servicio'] =$request->servicio;
      $data['estado'] =$request->estado;

      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl);
      curl_close($curl);
      return $resp;

    }

    // -----------------------------------------------Fex Webhook (callback)--------------------------------------

        function FexCallback(Request $request){

          $url = "";

          $curl = curl_init($url);
          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          
          $headers = array(
            "Content-Type: application/json",
         );
          curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
          
       $data = array();
    
          $data['servicio'] =$request->acceso;
          $data['tipo'] =$request->tipo;
          $data['estado'] =$request->estado;
          $data['descripcion'] =$request->descripcion;

          $data['conductor']=array(
            'nombre'=>'Nombre Completo',
            'telefono'=>'000000000',
            'patente'=>'Patente',
            'tipo'=>'Camioneta',
            'posicion'=>array(
              'lat'=>'00.00000000',
              'lng'=>'00.00000000'
             )
            );

                echo "<pre>";
                print_r(json_encode($data));die;

          curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          
          $resp = curl_exec($curl);
          curl_close($curl);
          return $resp;
    

       }

}
