<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class cabifyController extends Controller
{
    function GetAccessToken(){

        $url = "https://cabify-sandbox.com/auth/api/authorization";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
           "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        $data = '{
            "grant_type":"client_credentials",
            "client_id":"49834944447749338a7f17c62bfb8de0",
            "client_secret":"MK84LtOM-WxtYLIZ"
        }';
        
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);       
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }


  function PostCreateDelivery(){
  $curl = curl_init();
    
  curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://cabify-sandbox.com/api/v3/graphql',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{"query":"mutation CreateDelivery($senderId: c432e92c224370bccf5715eae53ff94a, $productId: db10033ac9b52ac4e1d785107f3e96aa, $deliveryPoints: [{
    "name":"PickUp point",
    "instr":"https://url.example",
    "addr":"Calle de Évora",
    "num":"1",
    "city":"Madrid",
    "country":"Spain",
    "loc":[
       ,40.3865045
       -70.560783
    ],
    "receiver":{
       "mobileCc":"34",
       "mobileNum":"666778899",
       "name":"John Doe"
    }
 },
 {
    "name":"Destination point",
    "addr":"Calle de Évora",
    "num":"1",
    "city":"Madrid",
    "country":"Spain",
    "loc":[
      -33.417019,
      -70.560783
    ],
    "receiver":{
       "mobileCc":"34",
       "mobileNum":"666998877",
       "name":"Jane Doe"
    }
 }
], $optimize: true) {\\r\\n  createDelivery(deliveryInput: {senderId: c432e92c224370bccf5715eae53ff94a, productId: db10033ac9b52ac4e1d785107f3e96aa, deliveryPoints: $deliveryPoints, optimize: true}) {\\r\\n    sender {\\r\\n      id\\r\\n      name\\r\\n      email\\r\\n    }\\r\\n    id\\r\\n    deliveryPoints {\\r\\n      addr\\r\\n      city\\r\\n      receiver {\\r\\n        mobileCc\\r\\n        mobileNum\\r\\n        name\\r\\n      }\\r\\n      instr\\r\\n      loc\\r\\n      name\\r\\n      num\\r\\n    }\\r\\n    startAt\\r\\n    startType\\r\\n  }\\r\\n}","variables":{}}',
   CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer wn2iJLu8kkvmnKcM5vUncHeUTRW1eD',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
curl_close($curl);
echo $response;


    }
}
