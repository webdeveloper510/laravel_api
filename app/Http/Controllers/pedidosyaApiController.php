<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\shipmentModel;
use App\Models\Estimate;
  

class pedidosyaApiController extends Controller
{
    //-----------------------Client Module--------------------------

     function getToken(){ 

        $url = "https://auth-api.pedidosya.com/v1/token";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
           "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        $data = '{
            "client_id":"courier_270551_cl",
            "client_secret":"1627654013",
            "grant_type":"password",
            "password":"prGzFrNI[2021",
            "username":"270551-PB6KL@courierapi.com"
        }';
        
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);       
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
     }




    function CreateShippingOrder(){

    $url = "https://courier-api.pedidosya.com/v1/shippings";     
    
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "Content-Type: application/json",
       "Authorization:1763-292224-e5f544f6-dc2e-4fb9-59cd-9b61a837a455"
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
    $data = '{
  "referenceId": "Client Internal Reference",
  "isTest": false,
  "deliveryTime": "2022-04-03T19:00:00Z",
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
       "latitude":-33.417019,
      "longitude":-70.560783,

      "addressStreet": "Avenida José Agustín Arango Juan Díaz Panamá, PanamáJuan Díaz Panamá",
      "addressAdditional": "Avenida José Agustín Arango Juan Díaz Panamá, PanamáJuan Díaz Panamá",
      "city": "diaz panama",
    
      "phone": "+59898765432",
      "name": "Oficina Ciudad Vieja",
      "instructions": "El ascensor esta roto.",
      "order": 1
    },
    {
      "type": "DROP_OFF",
      "latitude":-33.417019,
      "longitude":-70.560783,

      "addressStreet": "Avenida Urraca N 1 Rufina Alfaro San Miguelito, PanamáRufina Alfaro Panamá",
      "addressAdditional": "Avenida Urraca N 1 Rufina Alfaro San Miguelito, PanamáRufina Alfaro Panamá",
      "city": "San Miguelito",
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
    
    if($data['id']){
      
      $shipment = new shipmentModel;
      $shipment->user_id = "text";
      $shipment->reference_id = "text";
      $shipment->items = "text";
      $shipment->waypoint ="text";
      $shipment->delivery_time = "text";
      $shipment->save();
      $lastInsertedId= $shipment->id;
      $shiiping_id= $data['id'];
      $affectedRows = $shipment->where("id", $lastInsertedId)->update(["shipping_id" =>$shiiping_id]);
      return $resp;
    }
    curl_close($curl);
   

  }   
    




    function GetShippingOrderDetails(){
        
        $url = "https://courier-api.pedidosya.com/v1/shippings/17632203251255272272893";      

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



     function GetShippingOrderTracking(){

        $url = "https://courier-api.pedidosya.com/v1/shippings/17632203251255272272893/tracking";      

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

     function Getaccesstoken(){


      $url = " https://cabify-sandbox.com/auth/api/authorization";

      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      
      $headers = array(
         "Content-Type: application/json",
      );
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      
      $data = '{
        "access_token":"1Lex4yzYz0M1NoMa-kfE1Uj3BdDd18",
        "expires_in":2591999,
        "token_type":"Bearer"
      }';
      
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl);
      curl_close($curl);
      return $resp;

     }

      function EstimateShippingOrder(){


        
        $url = "https://courier-api.pedidosya.com/v1/estimates/shippings";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array(
          "Content-Type: application/json",
          "Authorization:1763-311619-54e88082-dd5e-4dc3-6616-2f548c241e3b"
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


  


    