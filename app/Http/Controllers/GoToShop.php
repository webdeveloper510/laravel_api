<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoToShop extends Controller
{

    function GoToShopEstimate(Request $request){
      $estimate = array();
     // $estimate['padidosya_estimate']= $this->EstimateShipping($request->all());
      //$estimate['fex'] =$this->FexCotizer($request->all());
      $estimate['cabify'] =$this->GetEstimate($request->all());
      echo "<pre>";
      print_r($estimate);
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
         return json_decode($resp);
       }
// -------------------------------------------Cabify Estimate-----------------------------------------------

       function GetEstimate($postData){

        $curl = curl_init();

        $variable=array();
        $variable=array(
          'requesterId'=>$postData['estimateInput']['requesterId'],
          'startType'=>$postData['estimateInput']['startType'],
           'startAt'=>$postData['estimateInput']['startAt'],
           'stops'=>$postData['estimateInput']['stops'],
           );
           $variable = json_encode($variable);
           
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
            'Authorization: Bearer oTe2jxPxPLxLlLkxsgSlZ2jwhHLzJx',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;       
     
      }

// --------------------------------------------Fex Estimate-------------------------------------
      
    function FexCotizer($request){
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
    $data['acceso'] =$request['acceso'];
    $data['ori_lat'] =$request['ori_lat'];
    $data['ori_lng'] =$request['ori_lng'];
    $data['des_lat'] =$request['des_lat'];
    $data['des_lng'] =$request['des_lng'];
    $data['vehiculo'] =$request['vehiculo'];
    $data['reg_origen'] =$request['reg_origen'];
      
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));       
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      
      $resp = curl_exec($curl);
      curl_close($curl);
      return $resp;

    }
}
