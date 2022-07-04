<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoToShopShipping extends FormRequest
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
            'referenceId'=>'required',
            'isTest'=>'required',
            'deliveryTime'=>'required',
            'notificationMail'=>'required',
            'volume'=>'required',
            'weight'=>'required', 
            'items.*'=>'required',
            'waypoints.*'=>'required',
        ];
    }
}
