<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\shipmentModel;
use App\Models\Estimate;
  

class pedidosyaApiController extends Controller
{
    //---------------------------------------------Get Token--------------------------------------------------

     function getToken(Request $request){  

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
        $data['client_id'] = $request->client_id;
        $data['client_secret'] = $request->client_secret;
        $data['grant_type'] = $request->grant_type;
        $data['password'] = $request->password;
        $data['username'] = $request->username;

        
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
     }




//-------------------------------------------Create Shipping Order----------------------------------------------

    function CreateShippingOrder(Request $request){    

    $url = "https://courier-api.pedidosya.com/v1/shippings";      
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "Content-Type: application/json",
       "Authorization:".$request->token
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
    $data = array();
    $data['referenceId'] =$request->referenceId;
    $data['isTest'] =$request->isTest;
    $data['deliveryTime'] =$request->deliveryTime;
    $data['notificationMail'] =$request->notificationMail;
    $data['volume'] =$request->volume;
    $data['weight'] =$request->weight;  
    
    for($i=0;$i<count($request->items);$i++){
      $data['items'][$i]['categoryId'] = $request['items'][$i]['categoryId'];
      $data['items'][$i]['value'] = $request['items'][$i]['value'];
      $data['items'][$i]['description'] = $request['items'][$i]['description'];
      $data['items'][$i]['sku'] = $request['items'][$i]['sku'];
      $data['items'][$i]['quantity'] = $request['items'][$i]['quantity'];
      $data['items'][$i]['volume'] = $request['items'][$i]['volume'];
      $data['items'][$i]['weight'] = $request['items'][$i]['weight'];
    }

    for($i=0;$i<count($request->waypoints);$i++){
      $data['waypoints'][$i]['type'] = $request['waypoints'][$i]['type'];
      $data['waypoints'][$i]['latitude'] = $request['waypoints'][$i]['latitude'];
      $data['waypoints'][$i]['longitude'] = $request['waypoints'][$i]['longitude'];
      $data['waypoints'][$i]['addressStreet'] = $request['waypoints'][$i]['addressStreet'];
      $data['waypoints'][$i]['addressAdditional'] = $request['waypoints'][$i]['addressAdditional'];
      $data['waypoints'][$i]['city'] = $request['waypoints'][$i]['city'];
      $data['waypoints'][$i]['phone'] = $request['waypoints'][$i]['phone'];
      $data['waypoints'][$i]['name'] = $request['waypoints'][$i]['name'];
      $data['waypoints'][$i]['instructions'] = $request['waypoints'][$i]['instructions'];
      $data['waypoints'][$i]['order'] = $request['waypoints'][$i]['order'];

    }
    
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));   
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
    $resp = curl_exec($curl);
    $data = json_decode($resp, true);
    // echo "<pre>";
    // print_r ($data); die;
    if($data['id']){      
      $shipment = new shipmentModel;
      $shipment->user_id = "text";
      $shipment->reference_id = $data['referenceId'];
      $shipment->items = json_encode($data['items']);
      $shipment->waypoints =json_encode($data['waypoints']);
      $shipment->delivery_time = $data['deliveryTime'];
      $shipment->price = json_encode($data['price']);
      $shipment->status = $data['status'];
      $shipment->save();
      $lastInsertedId= $shipment->id;
      $shiiping_id= $data['id'];
      $affectedRows = $shipment->where("id", $lastInsertedId)->update(["shipping_id" =>$shiiping_id]);
      return $resp;
    }
    curl_close($curl);  

  }   
    




  //-------------------------------------------Get Shipping Order Details----------------------------------------

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




//--------------------------------------------Get Shipping Order Tracking------------------------------------------

     function GetShippingOrderTracking(Request $request){
         // print_r($request->all());die;

        $url = "https://courier-api.pedidosya.com/v1/shippings/".$request->id."/tracking";      

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




//---------------------------------------------Estimate Shipping Order---------------------------------------------    

      function EstimateShipping(Request $request){
       // print_r($request->all());die;        
        $url = "https://courier-api.pedidosya.com/v1/estimates/shippings";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
          "Content-Type: application/json",
          "Authorization:".$request->token
       );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        $data =array();
        $data['referenceId']=$request->referenceId;
        $data['isTest']=$request->isTest;
        $data['deliveryTime']=$request->deliveryTime;
        $data['notificationMail']=$request->notificationMail;
        $data['volume']=$request->volume;
        $data['weight']=$request->weight; 

        
      for($i=0;$i<count($request->items);$i++){
        $data['items'][$i]['categoryId'] = $request['items'][$i]['categoryId'];
        $data['items'][$i]['value'] = $request['items'][$i]['value'];
        $data['items'][$i]['description'] = $request['items'][$i]['description'];
        $data['items'][$i]['sku'] = $request['items'][$i]['sku'];
        $data['items'][$i]['quantity'] = $request['items'][$i]['quantity'];
        $data['items'][$i]['volume'] = $request['items'][$i]['volume'];
        $data['items'][$i]['weight'] = $request['items'][$i]['weight'];
        }

        for($i=0;$i<count($request->waypoints);$i++){
          $data['waypoints'][$i]['type'] = $request['waypoints'][$i]['type'];
          $data['waypoints'][$i]['addressStreet'] = $request['waypoints'][$i]['addressStreet'];
          $data['waypoints'][$i]['addressAdditional'] = $request['waypoints'][$i]['addressAdditional'];
          $data['waypoints'][$i]['city'] = $request['waypoints'][$i]['city'];
          $data['waypoints'][$i]['latitude'] = $request['waypoints'][$i]['latitude'];
          $data['waypoints'][$i]['longitude'] = $request['waypoints'][$i]['longitude'];
          $data['waypoints'][$i]['phone'] = $request['waypoints'][$i]['phone'];
          $data['waypoints'][$i]['name'] = $request['waypoints'][$i]['name'];
          $data['waypoints'][$i]['instructions'] = $request['waypoints'][$i]['instructions'];
          $data['waypoints'][$i]['order'] = $request['waypoints'][$i]['order'];
          }
               
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($data));       
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
      }
//------------------------------------------Estimate Waypoints Coverage-----------------------------------
      
     function EstimateWaypointsCoverage(Request $request){  
      // echo "<pre>";
      // print_r($request->all());die;
      $url = "https://courier-api.pedidosya.com/v1/estimates/coverage";

      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      
      $headers = array(
        "Content-Type: application/json",
        "Authorization:".$request->token
     );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

      $data=array();      
      for($i=0;$i<count($request->waypoints);$i++){
        $data['waypoints'][$i]['addressStreet'] = $request['waypoints'][$i]['addressStreet'];
        $data['waypoints'][$i]['city'] = $request['waypoints'][$i]['city'];
        $data['waypoints'][$i]['latitude'] = $request['waypoints'][$i]['latitude'];
        $data['waypoints'][$i]['longitude'] = $request['waypoints'][$i]['longitude'];
      }
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl);
      curl_close($curl);
      return $resp;
   
     }
      // -----------------------------------------Cancel Shipping Order-------------------------------------------

        function PostCancelShipping(Request $request){

          $url = "https://courier-api.pedidosya.com/v1/shippings/".$request->id."/cancel";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
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

//---------------------------------------------Create Callback-----------------------------------------------

        function createCallback(Request $request){

          //print_r($request->all());die;

          $url = "https://courier-api.pedidosya.com/v1/callbacks";

          $curl = curl_init($url);
          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_PUT, true);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          
          $headers = array(
             "Content-Type: application/json",
             "Authorization:1763-122337-a645d170-da19-4fbc-7b9a-6effbdedd376"

          );
          curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
          for($i=0; $i<count($request['callbacks']);$i++){
            $data['callbacks'][$i]['url'] = $request['callbacks'][$i]['url'];
            $data['callbacks'][$i]['authorizationKey'] = $request['callbacks'][$i]['authorizationKey'];
            $data['callbacks'][$i]['topic'] = $request['callbacks'][$i]['topic'];
            $data['callbacks'][$i]['notificationType'] = $request['callbacks'][$i]['notificationType'];
          }
  
          echo "<pre>";
          print_r(json_encode($data));die;
          
          curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
          
          //for debug only!
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          
          $resp = curl_exec($curl);
          curl_close($curl);
          var_dump($resp);

        }
//-------------------------------------------------SetStatus---------------------------------------------------

        function setStatus(Request $request){

          $url = "https://courier-api.pedidosya.com/your-callback-url";

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
        
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;

        }
  }


  


    