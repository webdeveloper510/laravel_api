<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class cabifyController extends Controller
{
    function GetAccessToken(Request $request){

        $url = "https://cabify-sandbox.com/auth/api/authorization";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
           "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = array();

        $data['grant_type'] =$request->grant_type;
        $data['client_id'] =$request->client_id;
        $data['client_secret'] =$request->client_secret;
        
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }


  function PostCreateDelivery(Request $request){
    // echo "<pre>";
    // print_r($request->all());die;
     $data = $request['variables']['deliveryPoints'];
     $curl = curl_init();
    $request_data = array(
    'query' => 'mutation CreateDelivery($senderId: String!, $productId: String!, $deliveryPoints: [DeliveryPointInput]!, $optimize: Boolean) {\\r\\n  createDelivery(deliveryInput: {senderId: $senderId, productId: $productId, deliveryPoints: $deliveryPoints, optimize: $optimize}) {\\r\\n    sender {\\r\\n      id\\r\\n      name\\r\\n      email\\r\\n    }\\r\\n    id\\r\\n    deliveryPoints {\\r\\n      addr\\r\\n      city\\r\\n      receiver {\\r\\n        mobileCc\\r\\n        mobileNum\\r\\n        name\\r\\n      }\\r\\n      instr\\r\\n      loc\\r\\n      name\\r\\n      num\\r\\n    }\\r\\n    startAt\\r\\n    startType\\r\\n  }\\r\\n}',
    'variables' => array(
      'optimize' => true,
      'senderId'=>$request['variables']['senderId'],
      'productId'=>$request['variables']['productId']
      
    ),
  );
  // echo "<pre>";
  // print_r($data);die;
  for($i=0;$i<count($data);$i++)
{
  $data1['deliveryPoints'][$i]['name'] = $data[$i]['name'];
  $data1['deliveryPoints'][$i]['instr'] =$data[$i]['instr'];
  $data1['deliveryPoints'][$i]['addr'] = $data[$i]['addr'];
  $data1['deliveryPoints'][$i]['city'] =$data[$i]['city'];
  $data1['deliveryPoints'][$i]['country'] = $data[$i]['country'];
  $data1['deliveryPoints'][$i]['loc'] = $data[$i]['loc'];
  $data1['deliveryPoints'][$i]['receiver'] = $data[$i]['receiver'];
}    
   
   $json_data['variables'] = array_merge($request_data['variables'],$data1);
   $json_data1['query'] = $request_data['query'];
   $another = array_merge($json_data1,$json_data);

   print_r(json_encode($another));die;
  curl_setopt_array($curl, array(
   CURLOPT_URL => 'https://cabify-sandbox.com/api/v3/graphql',
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_ENCODING => '',
   CURLOPT_MAXREDIRS => 10,
   CURLOPT_TIMEOUT => 0,
   CURLOPT_FOLLOWLOCATION => true,
   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
   CURLOPT_CUSTOMREQUEST => 'POST',
   CURLOPT_POSTFIELDS =>json_encode($another),
   CURLOPT_HTTPHEADER => array(
     'Authorization: Bearer DB8owffggCXyJiSc8Rvll7_r909BkR',
     'Content-Type: application/json'
   ),
 ));

$response = curl_exec($curl);
curl_close($curl);
echo $response;

 }

 function GetEstimate(){

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
     CURLOPT_POSTFIELDS =>'{"query":"query estimates ($estimateInput: EstimatesInput) {\\r\\n    estimates (estimateInput: $estimateInput) {\\r\\n        distance\\r\\n        duration\\r\\n        eta {\\r\\n            formatted\\r\\n            lowAvailability\\r\\n        }\\r\\n        priceBase {\\r\\n            amount\\r\\n            currency\\r\\n        }\\r\\n        product {\\r\\n            description {\\r\\n                en\\r\\n                es\\r\\n                pt\\r\\n            }\\r\\n            icon\\r\\n            id\\r\\n            name {\\r\\n                ca\\r\\n                en\\r\\n                es\\r\\n                pt\\r\\n            }\\r\\n        }\\r\\n        route\\r\\n        supplements {\\r\\n            description\\r\\n            kind\\r\\n            name\\r\\n            payToDriver\\r\\n            price {\\r\\n                amount\\r\\n                currency\\r\\n                currencySymbol\\r\\n                formatted\\r\\n            }\\r\\n            taxCode\\r\\n        }\\r\\n        total {\\r\\n            amount\\r\\n            currency\\r\\n        }\\r\\n    }\\r\\n}",
     "variables":{"estimateInput":{"requesterId":"280e5faa46f711ecacc0cad412eb504e","startType":"ASAP","startAt":"","stops":[{"loc":[-33.40626862115443,-70.5728953556456]},{"loc":[-33.41401149288474,-70.5698367726058]}]}}}',
     CURLOPT_HTTPHEADER => array(
       'Authorization: Bearer oTe2jxPxPLxLlLkxsgSlZ2jwhHLzJx',
       'Content-Type: application/json'
     ),
   ));
   
   $response = curl_exec($curl);
   
   curl_close($curl);
   echo $response;
   
  

 }

 function PostCancelDelivery(){

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
   CURLOPT_POSTFIELDS =>'{"query":"mutation CreateDelivery($senderId: String!, $productId: String!, $deliveryPoints: [DeliveryPointInput]!, $optimize: Boolean) {\\r\\n  createDelivery(deliveryInput: {senderId: $senderId, productId: $productId, deliveryPoints: $deliveryPoints, optimize: $optimize}) {\\r\\n    sender {\\r\\n      id\\r\\n      name\\r\\n      email\\r\\n    }\\r\\n    id\\r\\n    deliveryPoints {\\r\\n      addr\\r\\n      city\\r\\n      receiver {\\r\\n        mobileCc\\r\\n        mobileNum\\r\\n        name\\r\\n      }\\r\\n      instr\\r\\n      loc\\r\\n      name\\r\\n      num\\r\\n    }\\r\\n    startAt\\r\\n    startType\\r\\n  }\\r\\n}",
   "variables":{"optimize":true,"senderId":"c432e92c224370bccf5715eae53ff94a","productId":"db10033ac9b52ac4e1d785107f3e96aa","deliveryPoints":[{"name":"PickUp point","instr":"https://url.example","addr":"Calle de Évora","num":"1","city":"Madrid","country":"Spain","loc":[40.3865045,-3.718262699999999],"receiver":{"mobileCc":"34","mobileNum":"666778899","name":"John Doe"}},{"name":"Destination point","addr":"Calle de Évora","num":"1","city":"Madrid","country":"Spain","loc":[40.3865045,-3.718262699999999],"receiver":{"mobileCc":"34","mobileNum":"666998877","name":"Jane Doe"}}]}}',
   CURLOPT_HTTPHEADER => array(
     'Authorization: Bearer Nv91W2HwY-w6xRtYodzCuOh7nolfKa',
     'Content-Type: application/json'
   ),
 ));

$response = curl_exec($curl);
curl_close($curl);
echo $response;

}

}
