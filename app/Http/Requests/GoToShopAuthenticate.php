<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoToShopAuthenticate extends FormRequest
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
            'type'=>'required',
            'client_id'=>'required',
            'client_secret'=>'required',
            'grant_type'=>'required',
            'password'=>'required_if:type,Pedidosya',
            'username'=>'required_if:type,Pedidosya',
        ];
    }

    function message(){
        return[
            'client_id.required'=>'client_id is required!',
            'client_secret.required'=>'client_secret is required!',
            'grant_type.required'=>'grant_type is required!',
            'password.required'=>'password is required!',
            'username.required'=>'username is required!',
        ];
    }
}
