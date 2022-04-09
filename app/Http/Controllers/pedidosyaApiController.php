<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\shipmentModel;
use App\Models\Estimate;
  

class pedidosyaApiController extends Controller
{
    //-----------------------Get Token--------------------------

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




//------------------Create Shipping Order----------------------------------------------

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
    




  //--------------------------Get Shipping Order Details-------------------------------------------------

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




//--------------------------Get Shipping Order Tracking--------------------------------------------------------

     function GetShippingOrderTracking(Request $request){
          print_r($request->all());die;

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




//---------------------------Estimate Shipping Order------------------------------------------------    

      function EstimateShippingOrder(Request $request){
        print_r($request->all());die;        
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
        
        echo "<pre>";
        print_r($data);die;


        
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);       
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
      }

      
     function EstimateWaypointsCoverage(){  

      $url = "https://courier-api.pedidosya.com/v1/estimates/coverage";

      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      
      $headers = array(
        "Content-Type: application/json",
        "Authorization:1763-311722-2b9dec88-f50c-4a16-5715-3c247b050714"
     );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      
      $data = '{
          "waypoints": [
            {
              "addressStreet": "Plaza Independencia 759",
              "city": "Montevideo"
            },
            {
              "addressStreet": "La Cumparsita 1475",
              "city": "Montevideo",
              "latitude": -33.417019,
              "longitude": -70.560783
            }
          ]
      }';
      
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl);
      curl_close($curl);
      return $resp;
   
    }


    function EstimateShipping (){
    $url = "https://courier-api.pedidosya.com/v1/estimates/shippings";

    $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      
      $headers = array(
        "Content-Type: application/json",
        "Authorization:1763-052142-7b5bf341-b8d8-4f43-7661-1eedecc91669"
     );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      
  $data = '{
  "referenceId": "Client Internal Reference",
  "isTest": true,
  "deliveryTime": "2020-06-24T19:00:00Z",
  "notificationMail": "email@email.com",
  "volume": 20.02,
  "weight": 0.8,
  "items": [
    {
      "categoryId": 123,
      "value": 1250.6,
      "description": "Unos libros de Kotlin y una notebook.",
      "sku": "ABC123",
      "quantity": 1,
      "volume": 10.01,
      "weight": 0.5
    },
    {
      "categoryId": 124,
      "value": 250,
      "description": "Una remera",
      "sku": "ABC124",
      "quantity": 1,
      "volume": 10.01,
      "weight": 0.3
    }
  ],
  "waypoints": [
    {
      "type": "PICK_UP",
      "addressStreet": "Plaza Independencia 755",
      "addressAdditional": "Piso 6 Recepción",
      "city": "Montevideo",
      "latitude": -33.417019,
      "longitude": -70.560783,
      "phone": "+59898765432",
      "name": "Oficina Ciudad Vieja",
      "instructions": "El ascensor esta roto.",
      "order": 1
    },
    {
      "type": "DROP_OFF",
      "latitude": -33.417019,
      "longitude": -70.560783,
      "addressStreet": "La Cumparsita 1475",
      "addressAdditional": "Piso 1, Oficina Delivery",
      "city": "Montevideo",
      "phone": "+59812345678",
      "name": "Agustin",
      "instructions": "Entregar en mano",
      "order": 2
    }
  ]
}';
      
     
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);  
    //for debug only
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
    $resp = curl_exec($curl);
    $data = json_decode($resp, true);
    // echo "<pre>"; 
    // print_r($data);die;
    
    if($data){      
      $estimate = new Estimate;
      $estimate->referenceId = $data['referenceId'];
      $estimate->deliveryTime = json_encode($data['deliveryTime']);
      $estimate->items =json_encode($data['items']);
      $estimate->waypoints = json_encode($data['waypoints']);
      $estimate->price = json_encode($data['price']);
      $estimate->save();
      return $resp;
    }
    curl_close($curl);
   

  }

  }


  


    