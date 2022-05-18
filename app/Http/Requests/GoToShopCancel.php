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
           // 'reasonText' =>'required_if:type,Pedidosya',
        ];
    }
    
    function message(){
          return [
            'id.required' => 'Shipping is required!',
           // 'reasonText.required' => 'reasonText is required',
            ];
        }
}