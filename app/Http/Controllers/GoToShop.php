<?php

namespace App\Http\Controllers;
use App\Models\Authentication;
use App\Models\shipmentModel;


use Illuminate\Http\Request;

class GoToShop extends Controller
{

  function GoToShopEstimate(Request $request){     
      $estimate['cabify'] =$this->GetEstimate($request->all());         
      $estimate['padidosya_estimate']= $this->EstimateShipping($request->all());
      $estimate['fex'] =$this->FexCotizer($request->all());
      echo "<pre>";
      print_r($estimate);die;
      $key = $this->matchPrice($estimate);
     //echo $key;
      $this->GoToShopCreateShipment($key,$request->all());
  }

  /*--------------------------------------------Get minimum Price Api Provider-------------------------------------------- */

  function matchPrice($estimate){
    $value = min($estimate);
    $key = array_search($value, $estimate);
    return $key;
  }
/*-----------------------------------------------------Create Shipment-------------------------------------------------*/
  function GoToShopCreateShipment($plateform,$postData){
    if($plateform=='padidosya_estimate'){
      $response = $this->CreateShippingOrder($postData);
    }
    if($plateform=='cabify'){
      $response = $this->PostCreateDelivery($postData);
    }
    if($plateform=='fex'){
      $response =  $this->FexSolicitar($postData);
    }
    echo "<pre>";
    print_r($response);
    $this->insertAndSave(json_decode($response,true),$plateform,$postData);
  }

  function insertAndSave($save,$plateform,$postData){
     if($plateform=='padidosya_estimate'){
         $userId =1;
         $shipping_id = $save['id'];
         $refference_id =$save['referenceId'];
         $deleivery_time =$save['deliveryTime'];
         $waypoints =$save['waypoints'];
         $items =$save['items'];
         $price =$save['price'];
         $status = "PREORDER";
         $type ="DROP_OFF";
     }
     if($plateform=='cabify'){
      $userId =1;
      $refference_id =$save['data']['createDelivery']['sender']['id'];
      $deleivery_time =$save['data']['createDelivery']['startAt'];
      $waypoints =$save['data']['createDelivery']['deliveryPoints'];
      $items =$postData['items'];
      $id =$save['data']['createDelivery']['id'];
      $price =12;
      $status = "PREORDER";
      $type = "DROP_OFF";
  }
  if($plateform=='fex'){
    $userId =1;
    $refference =$postData['referenceId'];
    $deleivery_time =$save['deliveryTime'];
    $waypoints =$save ['dir_destino'];
    $items = $postData['items'];
   // $id =$save['id'];
    $price =1355;
    $status = "PREORDER";
    $type = "DROP_OFF";
}

  }
    function GoToShopAuthentication(Request $request){
      switch ($request->type) {
        case "cabify":
          $this->GetAccessToken($request->all());
          break;
        case "Pedidosya":
          $this->getToken($request->all());
          break;
       default:
          return json_encode('Invalid Type');
        }
        /* function GoToShopShipping(Request $request){
          switch ($request->type) {
            case "cabify":
              $this->GetAccessToken($request->all());
              break;
            case "Pedidosya":
              $this->getToken($request->all());
              break;
           default:
              return json_encode('Invalid Type');
            }*/
    }
// --------------------------------------Padidosya Authentication---------------------------------------

  function getToken($token){  
    $url = "https://auth-api.pedidosya.com/v1/token";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
   $data = array();
    $data['client_id'] = $token['client_id'];
    $data['client_secret'] = $token['client_secret'];
    $data['grant_type'] = $token['grant_type'];
    $data['password'] = $token['password'];
    $data['username'] = $token['username'];    
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);    
    $resp = curl_exec($curl);
    $response = json_decode($resp,true);
    if($response){
      $store = new Authentication;
      $store->client_id = $token['client_id'];
      $store->client_secret = $token['client_secret'];
      $store->grant_type = $token['grant_type'];
      $store->password = $token['password'];
      $store->username = $token['username'];
      $store->type = $token['type'];
      $store->token = $response['access_token'];
      $store->save();
     // $lastInsertedId= $store->id;
     // $affectedRows = $store->where("id", $lastInsertedId)->update(["token" =>$response['access_token']]);
    
    }
    curl_close($curl);
    return $response;
 }


 /**------------------------------------------Cabify Authentication------------------------------------------------- */
 function GetAccessToken($token){

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

  $data['grant_type'] =$token['grant_type'];
  $data['client_id'] =$token['client_id'];
  $data['client_secret'] =$token['client_secret'];
  
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  
  $resp = curl_exec($curl);
  $response = json_decode($resp,true);
  if($response){
    $store = new Authentication;
    $store->client_id = $token['client_id'];
    $store->client_secret = $token['client_secret'];
    $store->grant_type = $token['grant_type'];
    $store->password = '';
    $store->username = '';
    $store->type = $token['type'];
    $store->token = $response['access_token'];
    $store->save();
    //$lastInsertedId= $store->id;
    //$affectedRows = $store->where("id", $lastInsertedId)->update(["token" =>$response['access_token']]);
  }
  curl_close($curl);
  return $response;
}

//----------------------------------Padidosya Estimate------------------------------------------------------
    
    function EstimateShipping($postdata){    

         $url = "https://courier-api.pedidosya.com/v1/estimates/shippings";
  
         $curl = curl_init($url);
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_POST, true);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         
         $headers = array(
           "Content-Type: application/json",
           "Authorization:".$postdata['token']
        );
         curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
         
         $data =array();
         $data['referenceId']=$postdata['referenceId'];
         $data['isTest']=$postdata['isTest'];
         $data['deliveryTime']=$postdata['deliveryTime'];
         $data['notificationMail']=$postdata['notificationMail'];
         $data['volume']=$postdata['volume'];
         $data['weight']=$postdata['weight'];          
       for($i=0;$i<count($postdata['items']);$i++){
         $data['items'][$i]['categoryId'] = $postdata['items'][$i]['categoryId'];
         $data['items'][$i]['value'] = $postdata['items'][$i]['value'];
         $data['items'][$i]['description'] = $postdata['items'][$i]['description'];
         $data['items'][$i]['sku'] = $postdata['items'][$i]['sku'];
         $data['items'][$i]['quantity'] = $postdata['items'][$i]['quantity'];
         $data['items'][$i]['volume'] = $postdata['items'][$i]['volume'];
         $data['items'][$i]['weight'] = $postdata['items'][$i]['weight'];
         }
 
         for($i=0;$i<count($postdata['waypoints']);$i++){
           $data['waypoints'][$i]['type'] = $postdata['waypoints'][$i]['type'];
           $data['waypoints'][$i]['addressStreet'] = $postdata['waypoints'][$i]['addressStreet'];
           $data['waypoints'][$i]['addressAdditional'] = $postdata['waypoints'][$i]['addressAdditional'];
           $data['waypoints'][$i]['city'] = $postdata['waypoints'][$i]['city'];
           $data['waypoints'][$i]['latitude'] = $postdata['waypoints'][$i]['latitude'];
           $data['waypoints'][$i]['longitude'] = $postdata['waypoints'][$i]['longitude'];
           $data['waypoints'][$i]['phone'] = $postdata['waypoints'][$i]['phone'];
           $data['waypoints'][$i]['name'] = $postdata['waypoints'][$i]['name'];
           $data['waypoints'][$i]['instructions'] = $postdata['waypoints'][$i]['instructions'];
           $data['waypoints'][$i]['order'] = $postdata['waypoints'][$i]['order'];
           }
                
         curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($data));       
         curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
         
         $resp = curl_exec($curl);
         curl_close($curl);
          $data = json_decode($resp,true);
          return $data['price']['total'];
       }
// -------------------------------------------Cabify Estimate-----------------------------------------------

       function GetEstimate($postData){ 
        $stops['loc'] = array();   
        $curl = curl_init();

        $variable=array();
        $variable=array(
          'requesterId'=>'280e5faa46f711ecacc0cad412eb504e',
          'startType'=>'ASAP',
           'startAt'=>'2020-08-01 08:00:00',
           "stops"=>[
            ["loc"=> [$postData['waypoints'][0]['latitude'] , $postData['waypoints'][0]['longitude'] ] ],
            [ "loc"=> [ $postData['waypoints'][1]['latitude'], $postData['waypoints'][1]['longitude'] ] ]
          ]
           );
           $variable = json_encode($variable);
        //print_r($variable);die;
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
          "variables":{"estimateInput":'.$variable.'}}',
          CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer aU5MV2MpG9erc8PYdB3UeMw2aP8ELT",
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        $response = json_decode($response,true);
      //  print_r($response);die;
        curl_close($curl);
       // return  $response;
        return $response['data']['estimates'][0]['total']['amount'];
     
      }

// --------------------------------------------Fex Estimate-------------------------------------
      
    function FexCotizer($request){

      // switch ($request->weight) {
      //   case $request->weight > 10:
      //     $vehicle = 'two';
      //     break;
      //   case "Pedidosya":
      //     $this->getToken($request->all());
      //     break;
      //  default:
      //     return json_encode('Invalid Type');
      //   }
       // print_r($request);die;
      
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
    $data['acceso'] ="DEB454D-A086639-8CD60D0-77C";
    $data['ori_lat'] =$request['waypoints'][0]['latitude'];
    $data['ori_lng'] =$request['waypoints'][0]['longitude'];;
    $data['des_lat'] =$request['waypoints'][1]['latitude'];
    $data['des_lng'] =$request['waypoints'][1]['longitude'];
    $data['vehiculo'] = 3;
    $data['reg_origen'] =0;
      
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl); 
      $response = json_decode($resp,true);
     // print_r($response);
//return $resp;
      curl_close($curl);
     return $response['resultado']['total'];

    }


  // -----------------------------------------padidosya Shipping-------------------------------------------

    
  function CreateShippingOrder($shipping){    

    $url = "https://courier-api.pedidosya.com/v1/shippings";      
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "Content-Type: application/json",
       "Authorization:".$shipping->token
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
    $data = array();
    $data['referenceId'] =$shipping['referenceId'];
    $data['isTest'] =$shipping['isTest'];
    $data['deliveryTime'] =$shipping['deliveryTime'];
    $data['notificationMail'] =$shipping['notificationMail'];
    $data['volume'] =$shipping['volume'];
    $data['weight'] =$shipping['weight'];  
    
    for($i=0;$i<count($shipping['items']);$i++){
      $data['items'][$i]['categoryId'] = $shipping['items'][$i]['categoryId'];
      $data['items'][$i]['value'] = $shipping['items'][$i]['value'];
      $data['items'][$i]['description'] = $shipping['items'][$i]['description'];
      $data['items'][$i]['sku'] = $shipping['items'][$i]['sku'];
      $data['items'][$i]['quantity'] = $shipping['items'][$i]['quantity'];
      $data['items'][$i]['volume'] = $shipping['items'][$i]['volume'];
      $data['items'][$i]['weight'] = $shipping['items'][$i]['weight'];
    }

    for($i=0;$i<count($shipping['waypoints']);$i++){
      $data['waypoints'][$i]['type'] = $shipping['waypoints'][$i]['type'];
      $data['waypoints'][$i]['latitude'] = $shipping['waypoints'][$i]['latitude'];
      $data['waypoints'][$i]['longitude'] = $shipping['waypoints'][$i]['longitude'];
      $data['waypoints'][$i]['addressStreet'] = $shipping['waypoints'][$i]['addressStreet'];
      $data['waypoints'][$i]['addressAdditional'] = $shipping['waypoints'][$i]['addressAdditional'];
      $data['waypoints'][$i]['city'] = $shipping['waypoints'][$i]['city'];
      $data['waypoints'][$i]['phone'] = $shipping['waypoints'][$i]['phone'];
      $data['waypoints'][$i]['name'] = $shipping['waypoints'][$i]['name'];
      $data['waypoints'][$i]['instructions'] = $shipping['waypoints'][$i]['instructions'];
      $data['waypoints'][$i]['order'] = $shipping['waypoints'][$i]['order'];

    }
    
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));   
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
    $resp = curl_exec($curl);
   // $data = json_decode($resp, true);
    // if($data['id']){      
    //   $shipment = new shipmentModel;
    //   $shipment->user_id = "text";
    //   $shipment->reference_id = $data['referenceId'];
    //   $shipment->items = json_encode($data['items']);
    //   $shipment->waypoints =json_encode($data['waypoints']);
    //   $shipment->delivery_time = $data['deliveryTime'];
    //   $shipment->price = json_encode($data['price']);
    //   $shipment->status = $data['status'];
    //   $shipment->shipping_type = 'pedidosya';
    //   $shipment->save();
    //   $lastInsertedId= $shipment->id;
    //   $shiiping_id= $data['id'];
    //   $affectedRows = $shipment->where("id", $lastInsertedId)->update(["Shipping" =>$shipping_id]);
    //   return $resp;
    // }
    return $resp;
    curl_close($curl);  

  }   

    
  // --------------------------------------------Cabify Shipping---------------------------------------------

    
  function PostCreateDelivery($shipping){
    // echo "<pre>";
    // print_r($shipping);die;
     $data = $shipping['waypoints'];
     $curl = curl_init();
    $request_data = array(
    'query' => 'mutation CreateDelivery($senderId: String!, $productId: String!, $deliveryPoints: [DeliveryPointInput]!, $optimize: Boolean) {\\r\\n  createDelivery(deliveryInput: {senderId: $senderId, productId: $productId, deliveryPoints: $deliveryPoints, optimize: $optimize}) {\\r\\n    sender {\\r\\n      id\\r\\n      name\\r\\n      email\\r\\n    }\\r\\n    id\\r\\n    deliveryPoints {\\r\\n      addr\\r\\n      city\\r\\n      receiver {\\r\\n        mobileCc\\r\\n        mobileNum\\r\\n        name\\r\\n      }\\r\\n      instr\\r\\n      loc\\r\\n      name\\r\\n      num\\r\\n    }\\r\\n    startAt\\r\\n    startType\\r\\n  }\\r\\n}',
    'variables' => array(
      'optimize' => true,
      'senderId'=>'c432e92c224370bccf5715eae53ff94a',
      'productId'=>'db10033ac9b52ac4e1d785107f3e96aa'
      
    ),
  );
  // echo "<pre>";
  // print_r($data);die;
  for($i=0;$i<count($data);$i++)
{
  $location = array();
  $location[] = $data[$i]['latitude'];
  $location[] = $data[$i]['longitude'];
  //$location[]=$data[$i]['latitude'].','.$data[$i]['longitude'];
  $receiver = array(
    'mobileCc'=>34,
    'mobileNum'=>666998877,
    'name'=>'John'
  );
  $data1['deliveryPoints'][$i]['name'] = $data[$i]['name'];
  $data1['deliveryPoints'][$i]['instr'] =$data[$i]['instructions'];
  $data1['deliveryPoints'][$i]['addr'] = $data[$i]['addressStreet'];
  $data1['deliveryPoints'][$i]['city'] =$data[$i]['city'];
  $data1['deliveryPoints'][$i]['country'] = 'England';
  $data1['deliveryPoints'][$i]['loc'] = $location;
  $data1['deliveryPoints'][$i]['receiver'] = $receiver;
}    


   $json_data['variables'] = array_merge($request_data['variables'],$data1);
    
   $json_data1['query'] = $request_data['query'];
   $another = array_merge($json_data1,$json_data);
   $shiping = json_encode($another);
  curl_setopt_array($curl, array(
   CURLOPT_URL => 'https://cabify-sandbox.com/api/v3/graphql',
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_ENCODING => '',
   CURLOPT_MAXREDIRS => 10,
   CURLOPT_TIMEOUT => 0,
   CURLOPT_FOLLOWLOCATION => true,
   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
   CURLOPT_CUSTOMREQUEST => 'POST',
   CURLOPT_POSTFIELDS =>"'".$shiping."'",
   CURLOPT_HTTPHEADER => array(
     'Authorization: Bearer DB8owffggCXyJiSc8Rvll7_r909BkR',
     'Content-Type: application/json'
   ),
 ));

$response = curl_exec($curl);
curl_close($curl);
return $response;

    

 }


//  ------------------------------------------------Fex Shipping-----------------------------------------------


 function  FexSolicitar($shipping){

  $url = "https://fex.cl/fex_api/externo/flete/solicitar";

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  
  $headers = array(
    "Content-Type: application/json",
 );
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  
$data = array();

$data['acceso'] ="280e5faa46f711ecacc0cad412eb504e";
$data['ori_lat'] =$shipping['waypoints'][0]['latitude'];
$data['ori_lat'] =$shipping['waypoints'][0]['latitude'];
$data['dir_origen'] =$shipping['waypoints'][0]['addressStreet'];
$data['ori_lat'] =$shipping['waypoints'][1]['latitude'];
$data['ori_lat'] =$shipping['waypoints'][1]['latitude'];
$data['dir_destino'] =$shipping['waypoints'][1]['addressStreet'];
$data['des_carga'] =$shipping['items'][0]['description'];
$data['rec_nom'] =$shipping['waypoints']['rec_nom'];
$data['rec_tel'] =$shipping['waypoints']['dir_destino'];
$data['vehiculo'] =2;
$data['reg_origen'] =0;


  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
  $resp = curl_exec($curl);
  curl_close($curl);
  return $resp;

}

}
