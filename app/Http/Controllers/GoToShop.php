<?php

namespace App\Http\Controllers;
use App\Models\Authentication;
use App\Models\shipmentModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;
use JWTAuth;
use JWT;
use Tymon\JWTAuthExceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject as JWTSubject;
class GoToShop extends Controller 
{
  public function __construct(){
  }

  // public function register(Request $rtequest){
  //   $validator=validator::make($request->all(),[
  //     'email'=>'required|string|email|unique:users',
  //     'password'=>'required|string|confirmed|min:6'
  //   ]);
  //   if($validator->fails()){
  //     return response()->json($validator->errors()->tojson(),400);
  //   }
  //   $user = user::create(array_merge(
  //     $validator->validated(),
  //     ['password'=>abcd($request->password)]
  //   ));
  //   return response()->json([
  //     'message'=>'user successfully registered',
  //     'user'=>$user
  //   ],201);
  // }

  // public function login(Request $request){
  //   $validator=validator::make($request->all(),[
  //     'email'=>'required|email',
  //     'password'=>'required|string|min:6'
  //   ]);
  //   if($validator->fails()){
  //     return response()->json($validator->errors(),422);
  // }
  //   if(!$token=auth()->attempt( $validator->validated())){
  //     return response()->json(['error'=>'Unauthorized',422]);
  //   }
  //   return $this->CreateNewToken($token);
  // }
  // public function CreateNewToken($token){

  //   return response()->json([
  //     'access_token'=>$token,
  //     'expires_in'=>auth()->factory()->getTTL()*60,
  //     'user'=>auth()->user()
  //   ]);

  // }

  function GoToShopShipping(Request $request){ 
   // print_r($request->all());die;    
    $estimate = array();
      $estimate['cabify'] =$this->GetEstimate($request->all());   
          
      $estimate['padidosya_estimate']= $this->EstimateShipping($request->all());
      $estimate['fex'] =$this->FexCotizer($request->all());
      $price_array = array('cabify'=>$estimate['cabify']['price'],'padidosya_estimate'=>$estimate['padidosya_estimate']['price'],'fex'=>$estimate['fex']['price']);
      $key = $this->matchPrice($price_array);
      $this->GoToShopCreateShipment($key,$request->all(),$estimate['cabify']['result'],$estimate['cabify']['parcel_id']);
  }
  function getVehicle($weight){
    if($weight<=4){
      $vehicleIdentifier = 1;
    }
    if($weight<=50 && $weight>4){
      $vehicleIdentifier = 2;
    }
    if($weight<=500 && $weight>50){
      $vehicleIdentifier = 3;
    }
    if($weight<=700 && $weight>500){
      $vehicleIdentifier = 5;
    }
    if($weight<=1000 && $weight>700){
      $vehicleIdentifier = 8;
    }
    return $vehicleIdentifier;
  }

  // ----------------------------------------------GoToShop Estimate-------------------------------------------

    function GoToShopEstimate(Request $request){

      $response = $request->all();  
      $estimate['cabify'] =$this->GetEstimate($request->all());        
      $estimate['padidosya_estimate']= $this->EstimateShipping($request->all());
      $estimate['fex'] =$this->FexCotizer($request->all());
      $price_array = array('cabify'=>$estimate['cabify']['price'],'padidosya_estimate'=>$estimate['padidosya_estimate']['price'],'fex'=>$estimate['fex']['price']);
      $key = $this->matchPrice($price_array);
      return $this->createEstimateWithPrice($key,$response,$estimate);  
    }


    function createEstimateWithPrice($provider,$response,$estimate){
        unset($response['isTest']);
        unset($response['notificationMail']);
        unset($response['access_token']);
        if($provider=='cabify'){
         // $data = $estimate['cabify']['result']['data'];
          $response=$estimate['cabify']['result'];
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

       // print_r($response);die;

        return $response;
    }

  /*--------------------------------------------Get minimum Price Api Provider-------------------------------- */

  function matchPrice($estimate){
    $value = min($estimate);
    $key = array_search($value, $estimate);  
    return $key;
  }
  
/*-----------------------------------------------------Create Shipment----------------------------------------*/

  function GoToShopCreateShipment($plateform,$postData,$result,$parcel_id){
    if($plateform=='padidosya_estimate'){
      $response = $this->CreateShippingOrder($postData);
    }
    if($plateform=='cabify'){
      $response = $this->PostCreateDelivery($postData,$result,$parcel_id);
    }
    if($plateform=='fex'){
      $response =  $this->FexSolicitar($postData);
    }

    $done = $this->insertAndSave(json_decode($response,true),$plateform,$postData);
    echo json_encode($done);
  }

  function insertAndSave($save,$plateform,$postData){
    $shipment = new shipmentModel;
    //print_r($save);die;
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
      $insert_data['reference_id'] =$save['referenceId'];
      $insert_data['delivery_time']=$save['deliveryTime'];
      $insert_data['items'] =json_encode($save['items']);
      $insert_data['waypoints'] =json_encode($save['waypoints']);
      $insert_data['shipping_id']=$save['id'];;
      $insert_data['price']=json_encode($save['price']);
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

//print_r($insert_data);die;
if(!empty($insert_data)){

  $shipment = new shipmentModel;
  $shipment->user_id =1;
  $shipment->reference_id = $insert_data['reference_id'];
  $shipment->items = $insert_data['items'];
  $shipment->waypoints =$insert_data['waypoints'];
  $shipment->delivery_time = $insert_data['delivery_time'];
  $shipment->price = $insert_data['price'];
  $shipment->status = $insert_data['status'];
  $shipment->type = $insert_data['type'];
  $shipment->shipping_id = $insert_data['shipping_id'];
  $shipment->save();
  // $lastInsertedId= $shipment->id;
  // $shiiping_id= $data['id'];
  // $affectedRows = $shipment->where("id", $lastInsertedId)->update(["Shipping" =>$shipping_id]);
  return ['Code'=>200,'message'=>'Saved Successfull!','response'=>$save];

}


  }

  //---------------------------------------------GoToShopAuthentication----------------------------------------- 

    function GoToShopAuthentication(Request $request){
      switch ($request->type) {
        case "Pedidosya":
          $this->getToken($request->all());
          break;
        case "cabify":
          $this->GetAccessToken($request->all());
          break;
        default:
        echo json_encode(array("msg"=>"Please send valid body"));
      }   
          
                     
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
    // echo "<pre>";
    // print_r($data);die;
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
    $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);
    echo json_encode(array('response'=>$response,'code'=>$response_code));
 }


 /**------------------------------------------Cabify Authentication------------------------------------------------- */
 function GetAccessToken($token){

  //print_r($token);die;

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
 // print_r($response);die;
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
    echo json_encode($auth_response);
  }
  $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);

  return $response;
}

//----------------------------------Padidosya Estimate------------------------------------------------------
    
    function EstimateShipping($postdata){    
          $token = $this->getTokenFromDb('Pedidosya');
         $url = "https://courier-api.pedidosya.com/v1/estimates/shippings";
  
         $curl = curl_init($url);
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_POST, true);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         
         $headers = array(
           "Content-Type: application/json",
           "Authorization:".$token['token']
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
          
          return ['price'=>$data['price']['total'],'result'=>$data];
       }
// -------------------------------------------Cabify Estimate-----------------------------------------------
  function GetEstimate($postData){ 
      $cabify_token = $this->getTokenFromDb('cabify');
      $parcel = $this->createParcel($postData);
      $parcel_id = $parcel['parcels'][0]['id']; 
        $stops['loc'] = array();   
        $curl = curl_init();
      curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://delivery.api.cabify-sandbox.com/v1/parcels/estimate',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
            "parcel_ids":["'.$parcel_id.'"]
         }',
      CURLOPT_HTTPHEADER => array(
     'Authorization: Bearer '.$cabify_token['token'],
     'Content-Type: application/json'
       ),
       ));

      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      $parcels_estimate = json_decode($response,true);
      if($httpcode==200){
        $setResponse=array();
        $setResponse['referenceId']=$postData['referenceId'];
        $setResponse['deliveryTime']=$postData['deliveryTime'];
        $setResponse['volume']=$postData['volume'];
        $setResponse['weight']=$postData['weight'];
        $setResponse['items']=$postData['items'];
        $setResponse['waypoints']=$postData['waypoints'];
        $setResponse['price']=array(
          'distance'=>'',
          'subtotal'=>$parcels_estimate['price_total']['amount'],
          'taxes'=>'',
          'total'=>$parcels_estimate['price_total']['amount'],
          'currency'=>$parcels_estimate['price_total']['currency'],
        );
      }
       // return  $response;
        return ['price'=>$parcels_estimate['price_total']['amount'],'code'=>$httpcode,'result'=>$setResponse,'parcel_id'=>$parcel_id];
     
      }

// --------------------------------------------Fex Estimate-------------------------------------
      
    function FexCotizer($request){      
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
    $data['ori_lat'] =-33.553788;
    $data['ori_lng'] =-70.656825;
    $data['des_lat'] =-33.447811;
    $data['des_lng'] =-70.597801;
    $data['vehiculo'] = $this->getVehicle($request['weight']);
    $data['reg_origen'] =0;      
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl); 
      $response = json_decode($resp,true);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if($httpcode==200){
        return ['price'=>$response['resultado']['total'],'result'=>$response];
      }
     
     //return $resp;
      curl_close($curl);

      echo $resp;
   

    }


  // -----------------------------------------padidosya Shipping-------------------------------------------

    
  function CreateShippingOrder($shipping){    
    $shipping = $this->getTokenFromDb('Pedidosya');
    $url = "https://courier-api.pedidosya.com/v1/shippings";      
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "Content-Type: application/json",
       "Authorization:".$shipping['token']
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

  function createParcel($shipping){   
    $cabify_token = $this->getTokenFromDb('cabify');
  //  print_r($cabify_token);die;
    $SixDigitRandomNumber = rand(100000,999999);
    $data = array();
    $final_array=[];
    $name = $shipping['waypoints'][0]['name'];
    $phone = $shipping['waypoints'][0]['phone'];
    $lat = $shipping['waypoints'][0]['latitude'];
    $lon = $shipping['waypoints'][0]['longitude'];
    $addr = $shipping['waypoints'][0]['addressStreet'];
    $instr = $shipping['waypoints'][0]['instructions'];
    $phone1 = $shipping['waypoints'][1]['phone'];
    $name1 = $shipping['waypoints'][1]['name'];
    $latitude = $shipping['waypoints'][1]['latitude'];
    $longitude = $shipping['waypoints'][1]['longitude'];
    $addressStreet = $shipping['waypoints'][1]['addressStreet'];
    $instructions = $shipping['waypoints'][1]['instructions'];
    $parcels=array(
      'external_id'=>'parcel_'.$SixDigitRandomNumber,
      'pickup_info'=>array(
      'contact'=>array(
        'name'=>$name,
        'phone'=>$phone
      ),
      'loc'=>array(
        'lat'=>$lat,
        'lon'=>$lon,
      ),
      'addr'=>$addr
      ),
      'dropoff_info'=>array(
        'contact'=>array(
          'name'=>$name1,
          'phone'=>$phone1
        ),
        'loc'=>array(
          'lat'=>$latitude,
          'lon'=>$longitude,
        ),
        'addr'=>$addressStreet
      )
      );
     $parcel = json_encode($parcels,true);
     $data = '{"parcels": ['.$parcel.']}';
     //print_r($data);die;
    $final_array['parcels'] = json_encode($parcels);
     $curl = curl_init();
     curl_setopt_array($curl, array(
       CURLOPT_URL => 'https://delivery.api.cabify-sandbox.com/v1/parcels',
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => '',
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 0,
       CURLOPT_FOLLOWLOCATION => true,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       CURLOPT_CUSTOMREQUEST => 'POST',
       CURLOPT_POSTFIELDS =>$data,
       CURLOPT_HTTPHEADER => array(
         'Content-Type: application/json',
         "Authorization: Bearer ".$cabify_token['token']
       ),
     ));
     
     $response = curl_exec($curl);     
     $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

     curl_close($curl);
     if($httpcode==200){
       $parcel_response = json_decode($response,true);
        return $parcel_response;
      }  
      echo $response;
     
//return $this->setShippingResponseAsPadidosya($response,'cabify',$shipping,$result,$httpcode);
//return $response;    

 }
/*---------------------------------Create Cabify Delievery-------------------------*/ 

 function PostCreateDelivery($request_data,$cabify,$parcel_id){
 // echo $parcel_id;die;
  $token = $this->getTokenFromDb('cabify');
  $curl = curl_init();
  curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://delivery.api.cabify-sandbox.com/v1/parcels/deliver',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "parcel_ids": [
         "'.$parcel_id.'"
    ],
    "optimize": true,
    "requester_id": "280e5faa46f711ecacc0cad412eb504e"
}
',
  CURLOPT_HTTPHEADER => array(
 'Authorization: Bearer '.$token['token'],
 'Content-Type: application/json'
   ),
   ));

  $response = curl_exec($curl);
 
  $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
 
   curl_close($curl);
   if($httpcode==202){
     //echo "dasds";die;
    $parcel = $this->getParcelByid($parcel_id);
    return $this->setShippingResponseAsPadidosya($parcel,'cabify',$request_data,$cabify,$httpcode);
   }
  
 }

 /**-----------------------------------------Get Parcel BY id-------------------------------------- */

 function getParcelByid($parcel_id){
  $token = $this->getTokenFromDb('cabify');
  $curl = curl_init();
  curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://delivery.api.cabify-sandbox.com/v1/parcels/'.$parcel_id,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
 'Authorization: Bearer '.$token['token'],
 'Content-Type: application/json'
   ),
   ));

  $response = curl_exec($curl);
 
  $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  if($httpcode==200){
    return $response;
  }
  return $response;
 

 }

function setShippingResponseAsPadidosya($response,$provider,$shipping,$estimate,$code){
  $setResponse=array();
   $response = json_decode($response,true);
   if($provider=='cabify'){
    $setResponse['id']=$response['id'];
    $setResponse['status']='PREORDER';
    $setResponse['cancelCode']='';
    $setResponse['cancelReason']='';
    $setResponse['referenceId']=$shipping['referenceId'];
    $setResponse['isTest']=$shipping['isTest'];
    $setResponse['deliveryTime']=$response['deliver_from'];
    $setResponse['lastUpdated']='';
    $setResponse['createdAt']='';
    $setResponse['expiresAt']='';
    $setResponse['items']=$shipping['items'];
    $setResponse['volume']=$shipping['volume'];
    $setResponse['weight']=$shipping['weight'];
    $setResponse['price']=$estimate['price'];
    $setResponse['shareLocationUrl']='https://envios.pedidosya.com.uy/tracking/ODYzMjAxMTAyMTMwOTM0Njk0Njg3NCNBUEkjODYz';
    $setResponse['proofOfDelivery']=true;
    $setResponse['notificationMail']=$shipping['notificationMail'];
    $setResponse['waypoints']=$shipping['waypoints'];
    $setResponse['onlineSupportUrl']='https://someOnlineSupportUrl.com';
    // for($i=0;$i<count($response['data']['createDelivery']['deliveryPoints']);$i++){
    //   $waypoints = $response['data']['createDelivery']['deliveryPoints'];
    //   $setResponse['waypoints'][$i]['type'] = $waypoints[$i]['name'];
    //   $setResponse['waypoints'][$i]['addressStreet'] = $waypoints[$i]['addr'];
    //   $setResponse['waypoints'][$i]['addressAdditional'] = $waypoints[$i]['addr'];
    //   $setResponse['waypoints'][$i]['city'] = $waypoints[$i]['city'];
    //   $setResponse['waypoints'][$i]['latitude'] = $waypoints[$i]['loc'][0];
    //   $setResponse['waypoints'][$i]['longitude'] = $waypoints[$i]['loc'][1];
    //   $setResponse['waypoints'][$i]['phone'] =$shipping['waypoints'][$i]['phone'] ;
    //   $setResponse['waypoints'][$i]['name'] ='';
    //   $setResponse['waypoints'][$i]['instructions'] = $shipping['waypoints'][$i]['instructions'];
    //   $setResponse['waypoints'][$i]['order'] = $shipping['waypoints'][$i]['order'];
    // }
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
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
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

/**-------------------------------------------Get Shipping Padidosya details------------------------------- */

function GetShippingOrderDetails(Request $request){      
  $shipping = $this->getShipingFRomDatabase($request->id);
  if($shipping['type']=='cabify'){
     $token = $this->getTokenFromDb('cabify');
    $type = 'cabify';
    
    $headers = array(
      "Content-Type: application/json",
      'Authorization: Bearer '.$token['token']
   );
    $url = "https://delivery.api.cabify-sandbox.com/v1/parcels/".$request->id;
  }
  if($shipping['type']=='fex'){
    $type = 'fex';
    $headers = array(
      "Content-Type: application/json",
   );
    $url = "https://fex.cl/fex_api/externo/flete/estado?acceso=DEB454D-A086639-8CD60D0-77C&servicio=".$request->id;
  }
  if($shipping['type']=='Padidosya'){
    $type = 'padidosya';
    $token = $this->getTokenFromDb('Pedidosya');

    $headers = array(
      "Content-Type: application/json",
      "Authorization:".$token['token']
   );

    $url = "https://courier-api.pedidosya.com/v1/shippings/".$request->id;
  }
   
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);                 
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);  

   if($httpcode==200 && $type!='padidosya'){

    $data = $this->makeResponse($type,$resp,$shipping);
     return $data;

   }

   else
    return $resp;
 }


 /**  -------------------------------------Cancellation Api----------------------------------- */

 function GoToShopCancellation(Request $request){      
  $shipping = $this->getShipingFRomDatabase($request->id);
  if($shipping['type']=='cabify'){
     $token = $this->getTokenFromDb('cabify');
      $type = 'cabify';     
      $headers = array(
        "Content-Type: application/json",
        'Authorization: Bearer '.$token['token']
    );
   $request['reasonText'] = "Testing Cancellation";
    $url = "https://delivery.api.cabify-sandbox.com/v1/parcels/".$request->id;
  }
  if($shipping['type']=='fex'){
    $type = 'fex';
    $headers = array(
      "Content-Type: application/json",
   );
   $request['acceso'] = "Testing Cancellation";
   $request['servicio'] = $request->id;;
   $request['estado'] = 3;
    $url = "https://fex.cl/fex_api/fex_api/externo/flete/cambiar_estado";
  }
  if($shipping['type']=='Padidosya'){
    $type = 'padidosya';
    $token = $this->getTokenFromDb('Pedidosya');

    $headers = array(
      "Content-Type: application/json",
      "Authorization:".$token['token']
   );
   $request['reasonText'] = "Testing Cancellation";
    $url = "https://courier-api.pedidosya.com/v1/shippings/".$request->id."/cancel";
  }
   
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);  
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request));       
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);  

   if($httpcode==200 && $type!='padidosya'){
    $data = $this->cancelResponse($type,$resp,$shipping);
     return $data;

   }

   else
    return $resp;
 }

 function cancelResponse($type,$resp,$database_data){
  $data = json_decode($resp,true);
  $setResponse=array();
  if($type=='cabify'){
   $setResponse['id'] = $data['id'];
   $setResponse['status'] = 'PREORDER';
   $setResponse['cancelCode'] = 'CONTENT_WRONG_RIDER';
   $setResponse['cancelReason'] = 'Producto despachado no es correcto';
   $setResponse['referenceId'] = 'Client Internal Reference';
   $setResponse['isTest'] = 'true';
   $setResponse['deliveryTime'] = "2020-06-05T19:00:00Z";
   $setResponse['lastUpdated'] = "2022-04-05T19:00:00Z";
   $setResponse['createdAt'] = "2020-06-24T19:00:00Z";
   $setResponse['volume'] = '20.02';
   $setResponse['weight'] = "20";
   $setResponse['waypoints'] = $database_data['waypoints'];
   $setResponse['items'] = $database_data['items'];
   $setResponse['price'] = array(
     'distance'=>'2345',
     'subtotal'=>'184.85',
     'taxes'=>'5.15',
     'total'=>'2345',
     'currency'=>'UYU'
   );
  }
  if($type=='fex'){
   $setResponse['id'] = $data["resultado"]['servicio'];
   $setResponse['referenceId'] = 'Client Internal Reference';
   $setResponse['status'] = 'PREORDER';
   $setResponse['isTest'] = 'true';
   $setResponse['deliveryTime'] = "2020-06-24T19:00:00Z";
   $setResponse['items'] = $database_data['items'];
   $setResponse['waypoints'] = $database_data['waypoints'];
   $setResponse['weight'] = "20";
   $setResponse['lastUpdated'] = "2020-07-21T12:10:32Z";
   $setResponse['createdAt'] = '2020-07-21T12:00:32Z';
   $setResponse['volume'] = '20.02';
   $setResponse['price'] = array(
     'distance'=>'2345',
     'subtotal'=>'184.85',
     'taxes'=>'5.15',
     'total'=>'2345',
     'currency'=>'UYU'
   );
 }
    return $setResponse;

}

 function makeResponse($type,$resp,$database_data){
   $data = json_decode($resp,true);
   $setResponse=array();
   if($type=='cabify'){
    $setResponse['id'] = $data['id'];
    $setResponse['referenceId'] = 'Client Internal Reference';
    $setResponse['status'] = 'PREORDER';
    $setResponse['isTest'] = 'true';
    $setResponse['deliveryTime'] = "2020-06-24T19:00:00Z";
    $setResponse['waypoints'] = $database_data['waypoints'];
    $setResponse['items'] = $database_data['items'];
    $setResponse['weight'] = "20";
    $setResponse['lastUpdated'] = "2020-07-21T12:10:32Z";
    $setResponse['createdAt'] = '2020-07-21T12:00:32Z';
    $setResponse['volume'] = '20.02';
    $setResponse['price'] = array(
      'distance'=>'2345',
      'subtotal'=>'184.85',
      'taxes'=>'5.15',
      'total'=>'2345',
      'currency'=>'UYU'
    );
   }
   if($type=='fex'){
    $setResponse['id'] = $data["resultado"]['servicio'];
    $setResponse['referenceId'] = 'Client Internal Reference';
    $setResponse['status'] = 'PREORDER';
    $setResponse['isTest'] = 'true';
    $setResponse['deliveryTime'] = "2020-06-24T19:00:00Z";
    $setResponse['items'] = $database_data['items'];
    $setResponse['waypoints'] = $database_data['waypoints'];
    $setResponse['weight'] = "20";
    $setResponse['lastUpdated'] = "2020-07-21T12:10:32Z";
    $setResponse['createdAt'] = '2020-07-21T12:00:32Z';
    $setResponse['volume'] = '20.02';
    $setResponse['price'] = array(
      'distance'=>'2345',
      'subtotal'=>'184.85',
      'taxes'=>'5.15',
      'total'=>'2345',
      'currency'=>'UYU'
    );
  }
     return $setResponse;
 
 }

//  -------------------------------------------Shipping Proof Of Delivery--------------------------------------

      function ShippingProofOfDelivery(Request $request){
        $token = $this->getTokenFromDb('Pedidosya');

          $url = "https://courier-api.pedidosya.com/v1/shippings/".$request->id."/proofOfDelivery";
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            
            $headers = array(
               "Content-Type: application/json",
               "Authorization:".$token['token']
            );
        
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);                 
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            
            $resp = curl_exec($curl);
            curl_close($curl);
            return $resp;
         }

// --------------------------------------Shipping Oerder Tracking---------------------------------------------

function GoToShopShippingOrderTracking(Request $request){
  // print_r($request->all());die;
  $authrise = $this->getTokenFromDb('Pedidosya');
 $url = "https://courier-api.pedidosya.com/v1/shippings/".$request->id."/tracking";      

 $curl = curl_init($url);
 curl_setopt($curl, CURLOPT_URL, $url);
 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 
 $headers = array(
    "Content-Type: application/json",
    "Authorization:".$authrise['token']
 );
 
 curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);                       
 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
 
 $resp = curl_exec($curl);
 curl_close($curl);
 return $resp;
}

// ------------------------------------------Shipping Proof Of Delivery---------------------------------------

function GoToShopProofOfDelivery(Request $request){
  $authrise = $this->getTokenFromDb('Pedidosya');
 $url = "https://courier-api.pedidosya.com/v1/shippings/".$request->id."/proofOfDelivery";      

 $curl = curl_init($url);
 curl_setopt($curl, CURLOPT_URL, $url);
 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 
 $headers = array(
    "Content-Type: application/json",
    "Authorization:".$authrise['token']
 );
 
 curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);                       
 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
 
 $resp = curl_exec($curl);
 curl_close($curl);
 return $resp;
}

// ---------------------------------------------Get Delivery Cabify details------------------------------------

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



  //----------------------------------------- Delivery Fex Detail---------------------------------------------------

  function GoToShopFexDelieveryDetail(Request $request){
   echo "<pre>";
   print_r($request);die;
    $token = $this->getTokenFromDb('cabify');
  $curl = curl_init();
  curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://fex.cl/fex_api/externo/flete/estado?acceso=?&servicio'.$request->id,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
 'Authorization: Bearer '.$token['token'],
 'Content-Type: application/json'
   ),
   ));

  $response = curl_exec($curl);
 
  $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  return $this->setShippingResponseAsPadidosya($resp,'fex',$shipping,'',$httpcode);
  }

  




  //---------------------------------------------Padidosya Create Callback-----------------------------------------------

  function createCallback(Request $request){

    $url = "https://courier-api.pedidosya.com/v1/callbacks";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_PUT, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "Content-Type: application/json",
       "Authorization:".$request->token
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    for($i=0; $i<count($request->callbacks);$i++){
      $data['callbacks'][$i]['url'] = $request['callbacks'][$i]['url'];
      $data['callbacks'][$i]['authorizationKey'] = $request['callbacks'][$i]['authorizationKey'];
      $data['callbacks'][$i]['topic'] = $request['callbacks'][$i]['topic'];
      $data['callbacks'][$i]['notificationType'] = $request['callbacks'][$i]['notificationType'];
    }

    // echo "<pre>";
    // print_r(json_encode($data));die;
    
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    
    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $resp = curl_exec($curl);
    curl_close($curl);
    var_dump($resp);

  }

// --------------------------------------------------Set Status---------------------------------------------

  function setStatus(Request $request){
    
    $url = "https://courier-api.pedidosya.com/api/updateStatus";

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  
  $headers = array(
     "Content-Type: application/json",
  );
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  
  $data = array();
  $data['topic'] = $request->topic;
  $data['id'] = $request->id;
  $data['referenceId'] = $request->referenceId;
  $data['generated'] = $request->generated;
  $data['transmitted'] = $request->transmitted;
  // print_r($data);die;
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
  $resp = curl_exec($curl);
  curl_close($curl);
  return $resp;

  } 

    // -------------------------------------------Cabify Callback---------------------------------------------

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

// --------------------------------------------------Fex Callback--------------------------------------------

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

        // echo "<pre>";
        // print_r(json_encode($data));die;

  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
  $resp = curl_exec($curl);
  $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  return array('code'=>$httpcode,'response'=>$resp); 
}

function getTokenFromDb($type){
  //GetAccessToken();
   $auth = Authentication::where('type',$type)->orderBy('id', 'DESC')->first()->toArray();
   return $auth;
}

function getShipingFRomDatabase($shiping_id){
  $auth = shipmentModel::where('shipping_id',$shiping_id)->first()->toArray();
  return $auth;
}

function JwtAuthenticate(Request $request){
    $credentials = $request->only('email', 'password');
    //valid credential
    $validator = Validator::make($credentials, [
        'email' => 'required|email',    
        'password' => 'required|string|min:6|max:50'
    ]);

    //Send failed response if request is not valid
    if ($validator->fails()) {
        return response()->json($validator->errors()->json(),400);
    }

  //   $token = Str::random(32);
    try {
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json([
              'success' => false,
              'message' => 'Login credentials are invalid.',
            ], 400);
        }
    } catch (JWTException $e) {
  return $credentials;
        return response()->json([
              'success' => false,
              'message' => 'Could not create token.',
            ], 500);
    }

 //Token created, return with success response and jwt token
    return response()->json([
        'success' => true,
        'token' => $token,
    ]);
}

protected function respondWithToken($token)
{
    return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth()->factory()->getTTL() * 60
    ]);
}

}
