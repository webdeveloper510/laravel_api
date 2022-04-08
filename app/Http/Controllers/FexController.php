<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FexController extends Controller
{


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
        "acceso":"DEB454D-A086639-8CD60D0-77C",
        "ori_lat": -33.553788,
        "ori_lng": -70.656825,
        "des_lat": -33.447811,
        "des_lng": -70.597801,
        "vehiculo": 5,
        "reg_origen":0
    }';
    
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);       
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;

     }
}
