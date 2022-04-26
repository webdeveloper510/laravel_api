<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class cabifyController extends Controller
{

// --------------------------------------------Access Token---------------------------------------------

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

// ----------------------------------------Create Delivery-----------------------------------------------

  function PostCreateDelivery(Request $request){
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
      "variables":{"optimize":true,"senderId":"280e5faa46f711ecacc0cad412eb504e","productId":"db10033ac9b52ac4e1d785107f3e96aa","deliveryPoints":[{"name":"PickUp point","instr":"https://url.example","addr":"Calle de Évora","num":"1","city":"Madrid","country":"Spain","loc":[40.3865045,-3.718262699999999],"receiver":{"mobileCc":"34","mobileNum":"666778899","name":"John Doe"}},{"name":"Destination point","addr":"Calle de Évora","num":"1","city":"Madrid","country":"Spain","loc":[40.3865045,-3.718262699999999],"receiver":{"mobileCc":"34","mobileNum":"666998877","name":"Jane Doe"}}]}}',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer zC32CTbXI18d2dSMd_X3cu_e_xIb4Y',
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return $response;

 }

//  -------------------------------------------------Create Journey-------------------------------------------

function CreateJourney(){

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
  CURLOPT_POSTFIELDS =>'{"query":"mutation createJourney ($bookingInput: BookingInput) {\\r\\n    createJourney (bookingInput: $bookingInput) {\\r\\n        id\\r\\n    }\\r\\n}",
  "variables":{"bookingInput":{"requesterId":"280e5faa46f711ecacc0cad412eb504e","stops":[{"loc":[40.4315553,-3.7018335],"addr":"Calle de los poetas 12,
   Madrid"},{"loc":[40.4244385,-3.6884232],"addr":"Avenida de Circo 2, Madrid"}],"startAt":null,"productId":"280e5faa46f711ecacc0cad412eb504e","message":"Test from Cabify Dev Team","rider":{"id":"01234567890123456789012345678901"}}}}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer 4YOa0F26QRUPND_Lsqf2J4mcv37ICl',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

    }

// ---------------------------------------------Cabify Estimate-----------------------------------------------
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
     CURLOPT_POSTFIELDS =>'{"query":"mutation CreateDelivery($senderId: String!, $productId: String!, $deliveryPoints: [DeliveryPointInput]!,
      $optimize: Boolean) {\\r\\n createDelivery(deliveryInput: {senderId: $senderId, productId: $productId, deliveryPoints:
      $deliveryPoints, optimize: $optimize}) {\\r\\n sender {\\r\\n id\\r\\n name\\r\\n email\\r\\n }\\r\\n id\\r\\n
      deliveryPoints {\\r\\n addr\\r\\n city\\r\\n receiver {\\r\\n mobileCc\\r\\n mobileNum\\r\\n name\\r\\n }\\r\\n
      instr\\r\\n loc\\r\\n name\\r\\n num\\r\\n }\\r\\n startAt\\r\\n startType\\r\\n
      }\\r\\n}",
      "variables":{"optimize":true,"senderId":"c432e92c224370bccf5715eae53ff94a","productId":"db10033ac9b52ac4e1d785107f3e96aa","deliveryPoints":[{"name":"Oficina
      Ciudad Vieja",
      "instr":"El ascensor esta roto.","addr":"Plaza Independencia755","city":"Montevideo","country":"England","loc":[-33.417019,-70.560783],
      "receiver":{"mobileCc":34,"mobileNum":666998877,"name":"John"}},{"name":"Agustin","instr":"Entregaren mano","addr":"La Cumparsita1475",
      "city":"Montevideo","country":"England","loc":[-33.40626862115443,-70.5728953556456],"receiver":{"mobileCc":34,"mobileNum":666998877,"name":"John"}}]}}',
     CURLOPT_HTTPHEADER => array(
       'Authorization: Bearer oTe2jxPxPLxLlLkxsgSlZ2jwhHLzJx',
       'Content-Type: application/json'
     ),
   ));
   
   $response = curl_exec($curl);
   
   curl_close($curl);
   echo $response;
   
  

 }

// ---------------------------------------------CancelDelivery-------------------------------------------

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

// ------------------------------------------------Cabify Webhook------------------------------------------------

        function Callback(Request $request){
            // echo "<pre>";
            // print_r($request->all());die;
          $url = "https://delivery.api.cabify-sandbox.com/v1/webhooks";

          $curl = curl_init($url);
          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          
          $headers = array(
            "Content-Type: application/json",
            'Authorization: Bearer '.$request->token
         );
          curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
          
       $data = array();
       $data['hook'] =$request->hook;
       $data['callback_url'] =$request->callback_url;

    for($i=0;$i<count($request['headers']);$i++){
        $data['headers'][$i]['name'] = $request['headers'][$i]['name'];
        $data['headers'][$i]['value'] = $request['headers'][$i]['value'];
        }

       // print_r($data);die;

          curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          
          $resp = curl_exec($curl);
          curl_close($curl);
          return $resp;
          
   }

  // ------------------------------------------UpdateStatus of callback---------------------------------------
   
    function updateStatus(Request $request){
          // echo "<pre>";
          // print_r($request->all());die;
      $url = "http://example.com/your/callback/here";

      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      
      $headers = array(
        "Content-Type: application/json",
        'Authorization: Bearer '.$request->token
     );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      
   $data = array();
   $data['id'] =$request->id;
   $data['state'] =$request->state;
   $data['delivery_attempt'] =$request->delivery_attempt;
   $data['tracking'] =$request->tracking;
   $data['asset'] =$request->asset;
   $data['driver'] =$request->driver;

for($i=0;$i<count($request['headers']);$i++){
    $data['headers'][$i]['name'] = $request['headers'][$i]['name'];
    $data['headers'][$i]['value'] = $request['headers'][$i]['value'];
    }

   // print_r($data);die;

      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl);
      curl_close($curl);
      return $resp;

  }
}
