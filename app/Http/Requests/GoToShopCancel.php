<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoToShopCancel extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required',
<<<<<<< HEAD
           // 'reasonText' =>'required_if:type,Pedidosya',
=======
            //'reasonText' =>'required_if:type,Pedidosya',
>>>>>>> b2975d3a04886a5f5a1fc9ba7ef3d7642fe13cce
        ];
    }
    
    function message(){
          return [
<<<<<<< HEAD
            'id.required' => 'Shipping is required!',
           // 'reasonText.required' => 'reasonText is required',
=======
            'id.required' => 'id is required!',
            //'reasonText.required' => 'reasonText is required',
>>>>>>> b2975d3a04886a5f5a1fc9ba7ef3d7642fe13cce
            ];
        }
}