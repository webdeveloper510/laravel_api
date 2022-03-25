<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class adminController extends Controller
{
    function GetShippingOrders(){        
        
            $url = "https://courier-api.pedidosya.com/v1/shippings?fromDate=2022-03-21&toDate=2022-04-01";      
    
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            
            $headers = array(
               "Content-Type: application/json",
               "Authorization:1763-251253-b130d54a-0664-4aae-4ea5-277737608457"
            );
            
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);             
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            
            $resp = curl_exec($curl);
            curl_close($curl);
            return $resp;         

    }
}
