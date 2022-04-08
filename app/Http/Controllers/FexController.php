<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FexController extends Controller
{
<<<<<<< HEAD
    function FexCotizer(){

        $url = "https://fex.cl/fex_api/externo/flete/cotizar";

      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      
      $headers = array(
        "Content-Type: application/json",
        //"Authorization:1763-311722-2b9dec88-f50c-4a16-5715-3c247b050714"
     );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      
      $data = '{
=======


 function Cotizar(){

  
    $url = " https://fex.cl/fex_api/externo/flete/cotizar";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
      "Content-Type: application/json",
      //"Authorization:1763-311722-2b9dec88-f50c-4a16-5715-3c247b050714"
   );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
    $data = '{
>>>>>>> 2becf3739ac4ec3120b972e6f3177baeee544f20
        "acceso":"DEB454D-A086639-8CD60D0-77C",
        "ori_lat": -33.553788,
        "ori_lng": -70.656825,
        "des_lat": -33.447811,
        "des_lng": -70.597801,
        "vehiculo": 5,
        "reg_origen":0
<<<<<<< HEAD
      }';
      
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl);
      curl_close($curl);
      return $resp;

    }


    function  FexSolicitar(){

        $url = "https://fex.cl/fex_api/externo/flete/solicitar";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
          "Content-Type: application/json",
          //"Authorization:1763-311722-2b9dec88-f50c-4a16-5715-3c247b050714"
       );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        $data = '{
            "acceso":"DEB454D-A086639-8CD60D0-77C",
            "ori_lat": -33.4249838,
            "ori_lng": -70.6051579,
            "dir_origen": "Josué Smith Solar 528, Providencia, Región Metropolitana, Chile",
            "des_lat": -33.4268146,
            "des_lng": -70.5869082,
            "dir_destino": "Teruel 1156, Las Condes, Región Metropolitana, Chile",
            "des_carga": "5 cajas de mercancía",
            "rec_nom": "María José",
            "rec_tel": 999999999,
            "vehiculo": 17,
            "reg_origen":0,
            "extra":"Factura Nº 0000"
           
        }';
        
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);       
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
  
    }
=======
    }';
    
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);       
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;

     }
>>>>>>> 2becf3739ac4ec3120b972e6f3177baeee544f20
}
