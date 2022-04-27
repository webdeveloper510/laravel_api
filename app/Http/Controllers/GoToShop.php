<?php

namespace App\Http\Controllers;
use App\Models\Authentication;
use App\Models\shipmentModel;


use Illuminate\Http\Request;

class GoToShop extends Controller
{
  public function __construct()
  {

  }

  function GoToShopShipping(Request $request){ 
   // print_r($request->all());die;    
      $estimate['cabify'] =$this->GetEstimate($request->all());         
      $estimate['padidosya_estimate']= $this->EstimateShipping($request->all());
      $estimate['fex'] =$this->FexCotizer($request->all());
      // echo "<pre>";
      // print_r($estimate);
      $key = $this->matchPrice($estimate);

     //echo $key;
      $this->GoToShopCreateShipment($key,$request->all(),$estimate['cabify']['result']);
  }

  // ----------------------------------------------GoToShop Estimate-------------------------------------------

    function GoToShopEstimate(Request $request){
      $response = $request->all();  
      $estimate['cabify'] =$this->GetEstimate($request->all());         
      $estimate['padidosya_estimate']= $this->EstimateShipping($request->all());
      $estimate['fex'] =$this->FexCotizer($request->all());
      $key = $this->matchPrice($estimate);
      return $this->createEstimateWithPrice($key,$response,$estimate);  
    }


    function createEstimateWithPrice($provider,$response,$estimate){
        unset($response['isTest']);
        unset($response['notificationMail']);
        unset($response['access_token']);
        if($provider=='cabify'){
          $data = $estimate['cabify']['result']['data'];
          $response['price']=array(
            'distance'=>$data['estimates'][0]['distance'],
            'subtotal'=>$data['estimates'][0]['total']['amount'],
            'taxes'=>'',
            'total'=>$data['estimates'][0]['total']['amount'],
            'currency'=>$data['estimates'][0]['total']['currency']
          );
        }
        else if($provider=='fex'){
          $data = $estimate['fex']['result']['resultado'];
          $response['price']=array(
            'distance'=>$data['distancia'],
            'subtotal'=>$data['total'],
            'taxes'=>'',
            'total'=>$data['total'],
            'currency'=>''
          );
        }

        else{
          $response = $estimate['padidosya_estimate']['result'];
        }

        return $response;
    }

  /*--------------------------------------------Get minimum Price Api Provider-------------------------------- */

  function matchPrice($estimate){
    $value = min($estimate);
    $key = array_search($value, $estimate);  
    return $key;
  }
  
/*-----------------------------------------------------Create Shipment----------------------------------------*/

  function GoToShopCreateShipment($plateform,$postData,$result){

    if($plateform=='padidosya_estimate'){
      $response = $this->CreateShippingOrder($postData);
    }
    if($plateform=='cabify'){
      $response = $this->PostCreateDelivery($postData,$result);
    }
    if($plateform=='fex'){
      $response =  $this->FexSolicitar($postData);
    }

    $this->insertAndSave(json_decode($response,true),$plateform,$postData);
  }

  function insertAndSave($save,$plateform,$postData){
    $insert_data=[];
     if($plateform=='padidosya_estimate'){
      $insert_data['user_id'] =1;
      $insert_data['shipping_id'] = $save['id'];
      $insert_data['reference_id'] =$save['referenceId'];
      $insert_data['delivery_time'] =$save['deliveryTime'];
      $insert_data['waypoints'] =$save['waypoints'];
      $insert_data['items'] =$save['items'];
      $insert_data['price'] =$save['price'];
      $insert_data['status'] = "PREORDER";
      $insert_data['type'] ="padidosya";
     }
     if($plateform=='cabify'){
      $insert_data['user_id'] =1;
      $insert_data['reference_id'] =$save['data']['createDelivery']['sender']['id'];
      $insert_data['delivery_time']=$save['data']['createDelivery']['startAt'];
      $insert_data['waypoints']=$save['data']['createDelivery']['deliveryPoints'];
      $insert_data['items'] =$postData['items'];
      $insert_data['shipping_id']=$save['data']['createDelivery']['id'];
      $insert_data['price']=12;
      $insert_data['status'] = "PREORDER";
      $insert_data['type'] = "cabify";
  }
  if($plateform=='fex'){
    $insert_data['user_id'] =1;
    $insert_data['reference_id'] ='fex refference';
    $insert_data['delivery_time']=$postData['deliveryTime'];
    $insert_data['waypoints']=$postData['waypoints'];
    $insert_data['items'] =$postData['items'];
    $insert_data['shipping_id']=$save['resultado']['servicio'];
    $insert_data['price']=$save['resultado']['total'];
    $insert_data['status'] = "PREORDER";
    $insert_data['type'] = "cabify";
}
if(!empty($insert_data)){

  $shipment = new shipmentModel;
  $shipment->user_id =1;
  $shipment->reference_id = $data['referenceId'];
  $shipment->items = json_encode($data['items']);
  $shipment->waypoints =json_encode($data['waypoints']);
  $shipment->delivery_time = $data['deliveryTime'];
  $shipment->price = json_encode($data['price']);
  $shipment->status = $data['status'];
  $shipment->save();
  $lastInsertedId= $shipment->id;
  $shiiping_id= $data['id'];
  $affectedRows = $shipment->where("id", $lastInsertedId)->update(["Shipping" =>$shipping_id]);
  return $resp;

}


  }

  //---------------------------------------------GoToShopAuthentication----------------------------------------- 

    function GoToShopAuthentication(Request $request){   
          $this->GetAccessToken($request->all());
          $this->getToken($request->all());

            
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
    if($response['access_token']){
      $store = new Authentication;
      $store->client_id = $token['client_id'];
      $store->client_secret = $token['client_secret'];
      $store->grant_type = $token['grant_type'];
      $store->password = $token['password'];
      $store->username = $token['username'];
      $store->type = $token['type'];
      $store->token = $response['access_token'];
      $store->save();    
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
  if($response['access_token']){
    $store = new Authentication;
    $store->client_id = $token['client_id'];
    $store->client_secret = $token['client_secret'];
    $store->grant_type = $token['grant_type'];
    $store->password = '';
    $store->username = '';
    $store->type = $token['type'];
    $store->token = $response['access_token'];
    $store->save();
    $auth_response['access_token'] = $response['access_token'];
    return json_encode($auth_response);
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
           "Authorization:".$postdata['access_token']
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
          
          return [$data['price']['total'],'result'=>$data];
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
            'Authorization: Bearer jNNIuGm9GdwzSzAYoNA0O72V9W6jRJ',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        $response = json_decode($response,true);
        // echo "<pre>";
        // print_r($response);die;
        curl_close($curl);
       // return  $response;
        return [$response['data']['estimates'][0]['total']['amount'],'result'=>$response];
     
      }

// --------------------------------------------Fex Estimate-------------------------------------
      
    function FexCotizer($request){;
      
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
    $data['ori_lng'] =$request['waypoints'][0]['longitude'];
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
     return [$response['resultado']['total'],'result'=>$response];

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
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    return ['code'=>$httpcode,'response'=>$resp];
    curl_close($curl);  

  }   

    
  // --------------------------------------------Cabify Shipping---------------------------------------------

  function PostCreateDelivery($shipping,$result){
     $data = $shipping['waypoints'];   
     $curl = curl_init();
     $request_data = array(
    'query' => 'mutation CreateDelivery($senderId: String!, $productId: String!, $deliveryPoints: [DeliveryPointInput]!, $optimize: Boolean) {\\r\\n  createDelivery(deliveryInput: {senderId: $senderId, productId: $productId, deliveryPoints: $deliveryPoints, optimize: $optimize}) {\\r\\n    sender {\\r\\n      id\\r\\n      name\\r\\n      email\\r\\n    }\\r\\n    id\\r\\n    deliveryPoints {\\r\\n      addr\\r\\n      city\\r\\n      receiver {\\r\\n        mobileCc\\r\\n        mobileNum\\r\\n        name\\r\\n      }\\r\\n      instr\\r\\n      loc\\r\\n      name\\r\\n      num\\r\\n    }\\r\\n    startAt\\r\\n    startType\\r\\n  }\\r\\n}',
    'variables' => array(
    'optimize' => true,
    'senderId'=>'c432e92c224370bccf5715eae53ff94a',
    'productId'=>'db10033ac9b52ac4e1d785107f3e96aa',
      
    ), 
  );

  $variable['optimize'] =true;
  $variable['senderId'] ='c432e92c224370bccf5715eae53ff94a';
  $variable['productId'] ='db10033ac9b52ac4e1d785107f3e96aa';
 
  for($i=0;$i<count($data);$i++)
{
  $location = array();
  $location[] = $data[$i]['latitude'];
  $location[] = $data[$i]['longitude'];
  //$location[]=$data[$i]['latitude'].','.$data[$i]['longitude'];
  $receiver = array(
    'mobileCc'=>"34",
    'mobileNum'=>"666998877",
    'name'=>'John'
  );
  $data1[$i]['name'] = $data[$i]['name'];
  $data1[$i]['instr'] =$data[$i]['instructions'];
  $data1[$i]['addr'] = $data[$i]['addressStreet'];
  $data1[$i]['city'] =$data[$i]['city'];
  $data1[$i]['num'] ='1';
  $data1[$i]['country'] = 'England';
  $data1[$i]['loc'] = $location;
  $data1[$i]['receiver'] = $receiver;
}    
   $deliveryPoints = json_encode($data1);
  //   echo "<pre>";
  //  print_r($deliveryPoints);

   $json_data = array_merge($request_data['variables'],$data1);
   $variables1 = json_encode($json_data);
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
   CURLOPT_POSTFIELDS =>'{"query":"mutation CreateDelivery($senderId: String!, $productId: String!, $deliveryPoints: [DeliveryPointInput]!, $optimize: Boolean) {\\r\\n  createDelivery(deliveryInput: {senderId: $senderId, productId: $productId, deliveryPoints: $deliveryPoints, optimize: $optimize}) {\\r\\n    sender {\\r\\n      id\\r\\n      name\\r\\n      email\\r\\n    }\\r\\n    id\\r\\n    deliveryPoints {\\r\\n      addr\\r\\n      city\\r\\n      receiver {\\r\\n        mobileCc\\r\\n        mobileNum\\r\\n        name\\r\\n      }\\r\\n      instr\\r\\n      loc\\r\\n      name\\r\\n      num\\r\\n    }\\r\\n    startAt\\r\\n    startType\\r\\n  }\\r\\n}",
   "variables":{"optimize":true,"senderId":"6bb956433d5d9e3a75d018054e7c7e52","productId":"75dd566797369d1f0927102e5356ce59","deliveryPoints":'.$deliveryPoints.'}}',
   CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer Cf5mfDWhyU06vy_l5B2RdkXDfRLFTz',
     'Content-Type: application/json'
   ),
 ));
$response = curl_exec($curl);
//  echo "<pre>";
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// print_r($response);die;


curl_close($curl);
return $this->setShippingResponseAsPadidosya($response,'cabify',$shipping,$result,$httpcode);
//return $response;    

 }

function setShippingResponseAsPadidosya($response,$provider,$shipping,$estimate,$code){
  $setResponse=array();
   $response = json_decode($response,true);
   if($provider=='cabify' && $response['data']['createDelivery']['id']){
    $setResponse['id']=$response['data']['createDelivery']['id'];
    $setResponse['status']='PREORDER';
    $setResponse['cancelCode']='';
    $setResponse['cancelReason']='';
    $setResponse['referenceId']=$shipping['referenceId'];
    $setResponse['isTest']=$shipping['isTest'];
    $setResponse['deliveryTime']=$response['data']['createDelivery']['startAt'];
    $setResponse['lastUpdated']='';
    $setResponse['createdAt']='';
    $setResponse['expiresAt']=$response['data']['createDelivery']['id'];
    $setResponse['items']=$shipping['items'];
    $setResponse['volume']=$shipping['volume'];
    $setResponse['weight']=$shipping['weight'];
    $setResponse['price']=array(
      'distance'=>$estimate['data']['estimates']['distance'],
      'subtotal'=>$estimate['data']['estimates'][0]['total']['amount'],
      'taxes'=>5,
      'total'=>$estimate['data']['estimates'][0]['total']['amount'],
      'currency'=>$estimate['data']['estimates'][0]['total']['currency']
    );
    $setResponse['shareLocationUrl']='https://envios.pedidosya.com.uy/tracking/ODYzMjAxMTAyMTMwOTM0Njk0Njg3NCNBUEkjODYz';
    $setResponse['proofOfDelivery']=true;
    $setResponse['notificationMail']=$shipping['notificationMail'];
    $setResponse['onlineSupportUrl']='https://someOnlineSupportUrl.com';
    for($i=0;$i<count($response['data']['createDelivery']['deliveryPoints']);$i++){
      $waypoints = $response['data']['createDelivery']['deliveryPoints'];
      $setResponse['waypoints'][$i]['type'] = $waypoints[$i]['name'];
      $setResponse['waypoints'][$i]['addressStreet'] = $waypoints[$i]['addr'];
      $setResponse['waypoints'][$i]['addressAdditional'] = $waypoints[$i]['addr'];
      $setResponse['waypoints'][$i]['city'] = $waypoints[$i]['city'];
      $setResponse['waypoints'][$i]['latitude'] = $waypoints[$i]['loc'][0];
      $setResponse['waypoints'][$i]['longitude'] = $waypoints[$i]['loc'][1];
      $setResponse['waypoints'][$i]['phone'] =$shipping['waypoints'][$i]['phone'] ;
      $setResponse['waypoints'][$i]['name'] ='';
      $setResponse['waypoints'][$i]['instructions'] = $shipping['waypoints'][$i]['instructions'];
      $setResponse['waypoints'][$i]['order'] = $shipping['waypoints'][$i]['order'];
    }
    return json_encode($setResponse); 
   }

   if($provider=='fex' && $response['estatus']==1){
    $setResponse['id']=$response['resultado']['servicio'];
    $setResponse['status']='PREORDER';
    $setResponse['cancelCode']='';
    $setResponse['cancelReason']='';
    $setResponse['referenceId']=$shipping['referenceId'];
    $setResponse['isTest']=$shipping['isTest'];
    $setResponse['deliveryTime']=$shipping['deliveryTime'];
    $setResponse['lastUpdated']='';
    $setResponse['createdAt']='';
    $setResponse['expiresAt']='';
    $setResponse['items']=$shipping['items'];
    $setResponse['volume']=$shipping['volume'];
    $setResponse['weight']=$shipping['weight'];
    $setResponse['waypoints']=$shipping['waypoints'];
    $setResponse['price']=array(
      'distance'=>$response['resultado']['distancia'],
      'subtotal'=>$response['resultado']['total'],
      'taxes'=>'',
      'total'=>$response['resultado']['total'],
      'currency'=>'usd'
    );
    $setResponse['shareLocationUrl']='https://envios.pedidosya.com.uy/tracking/ODYzMjAxMTAyMTMwOTM0Njk0Njg3NCNBUEkjODYz';
    $setResponse['proofOfDelivery']=true;
    $setResponse['notificationMail']=$shipping['notificationMail'];
    $setResponse['onlineSupportUrl']='https://someOnlineSupportUrl.com';
    return json_encode($setResponse); 

   }

   return ['code'=>$code,'response'=>$response];
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
$data['programado'] =$shipping['deliveryTime'];
$data['vehiculo'] =2;
$data['reg_origen'] =0;


  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
  $resp = curl_exec($curl);
  $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  curl_close($curl);
  return $this->setShippingResponseAsPadidosya($resp,'fex',$shipping,'',$httpcode);


}

// -------------------------------------------Padidosya Cancellation----------------------------------------------

function PostCancelShipping(Request $request){

  $url = "https://courier-api.pedidosya.com/v1/shippings/".$request->id."/cancel";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
// problem:-some database isshue!!!--------------------------
$headers = array(
   "Content-Type: application/json",
   "Authorization:".$request->token
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = array();
$data['reasonText'] = $request->reasonText;

curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
return $resp;

}
// ----------------------------------------------Cabify Cencellation---------------------------------------------

function PostCancelDelivery(){

  $curl = curl_init();
    //  Delivery will be canceled only when we have journeyId..?------------------
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
    'Authorization: Bearer jNNIuGm9GdwzSzAYoNA0O72V9W6jRJ',
     'Content-Type: application/json'
   ),
 ));

$response = curl_exec($curl);
curl_close($curl);
echo $response;

}

// ------------------------------------------------Fex Cencellation----------------------------------------------

function PostFexCancellation(Request $request){
 
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

/**---------------------------------------------Get Shipping Padidpsya details------------------------------- */

function GetShippingOrderDetails(Request $request){      
        
  $url = "https://courier-api.pedidosya.com/v1/shippings/".$request->id; 
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "Content-Type: application/json",
       "Authorization:".$request->token
    );

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);                 
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
 }

// ------------------------------------------------Get Delivery Cabify details---------------------------------

  function DeliveryDetails(Request $request){

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://cabify-sandbox.com/auth/api/authorization',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{"query":"query Delivery($id: String!) {\\r\\n  delivery(id: $id) {\\r\\n    createdAt\\r\\n    endAt\\r\\n    id\\r\\n    prices {\\r\\n        currency\\r\\n        priceBase {\\r\\n            amount\\r\\n            currency\\r\\n        }\\r\\n        price {\\r\\n            amount\\r\\n            currency\\r\\n        }\\r\\n        priceFormatted\\r\\n        priceTotal {\\r\\n            amount\\r\\n            currency\\r\\n        }\\r\\n        priceTotalFormatted\\r\\n        discount {\\r\\n            amount\\r\\n            currency\\r\\n        }\\r\\n        discountFormatted\\r\\n    }\\r\\n    productId\\r\\n    regionId\\r\\n    sender {\\r\\n        id\\r\\n        name\\r\\n        surname\\r\\n        email\\r\\n        mobile\\r\\n        mobileNum\\r\\n        mobileCc\\r\\n    }\\r\\n    sales {\\r\\n        code\\r\\n    }\\r\\n    startAt\\r\\n    startType\\r\\n    endState\\r\\n    deliveryPoints{\\r\\n        loc\\r\\n        addr\\r\\n        city\\r\\n        receiver {\\r\\n            mobileCc\\r\\n            mobileNum\\r\\n            name\\r\\n        }\\r\\n        country\\r\\n        name\\r\\n        num\\r\\n        hitAt\\r\\n        instr\\r\\n        proofOfDelivery {\\r\\n            recipientIdNumber\\r\\n            recipientName\\r\\n            photoUrl\\r\\n            types\\r\\n        }\\r\\n        status\\r\\n        failReason\\r\\n        supportTicket\\r\\n        tracking {\\r\\n            assetLocation {\\r\\n                bearing\\r\\n                location {\\r\\n                    latitude\\r\\n                    longitude\\r\\n                }\\r\\n            }\\r\\n            asset {\\r\\n                color\\r\\n                id\\r\\n                name\\r\\n                regPlate\\r\\n            }\\r\\n            currentState\\r\\n            driver {\\r\\n                id\\r\\n                name\\r\\n                avatarUrl\\r\\n            }\\r\\n            kind\\r\\n            product {\\r\\n                id\\r\\n                icon\\r\\n                description {\\r\\n                    es\\r\\n                }\\r\\n                name {\\r\\n                    es\\r\\n                }\\r\\n            }\\r\\n            route {\\r\\n                eta\\r\\n                path\\r\\n            }\\r\\n            sender {\\r\\n                id\\r\\n                name\\r\\n                surname\\r\\n                email\\r\\n                mobile\\r\\n                mobileNum\\r\\n                mobileCc\\r\\n            }\\r\\n            tracking_url\\r\\n        }\\r\\n    }\\r\\n  }\\r\\n}",
      "variables":{"id":'.$request->id.'}}',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    echo $response;  
  }

  function FexDelieveryDetail(Request $request){

  }

}
