<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }
    //
    public function login(Request $request)
    {

    $rules = [
        'email'    => 'required',
        'password' => 'required',
    ];

    $input     = $request->only('email','password');
    
    $validator = Validator::make($input, $rules);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'error' => $validator->messages()]);
    }
    $user = User::where(['email'=>$request->email,'password'=>md5($request->password)])->get();
    if($user){
        echo "yesss";
        print_r($user);die;
        return response()->json([
            'status' => 'success',
            'message' => 'User Login successfully',
            'user' => $user,
        ],200);
    }
    
    else{
            return response()->json([
            'status' => 'Failed',
            'message' => 'User not found!',
        ],400);
    }
      

    }
public function register(Request $request){
        //print_r($request->all());die;
     $rules = [
        'name' => 'unique:users|required',
        'email'    => 'unique:users|required',
        'password' => 'required',
        'surname'=>'required',
        'billingAddress'=>'required',
        'telephone'=>'required',
        'bussinessName'=>'required',
        'tradeName'=>'required',
       'ruc'=>'required',
        'turn'=>'required',
    ];

    $input     = $request->only('name', 'email','password');
    $validator = Validator::make($input, $rules);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'error' => $validator->messages()]);
    }
    $name = $request->name;
    $email    = $request->email;
    $password = $request->password;
    $user     = User::create(['name' => $name, 'email' => $email, 'password' => md5($password),'login_status'=>1,
        'surname'=>$request->surname,
        'billingAddress'=>$request->billingAddress,
        'telephone'=>$request->telephone,
        'bussinessName'=>$request->bussinessName,
        'tradeName'=>$request->tradeName,
        'ruc'=>$request->ruc,
        'turn'=>$request->turn
        ]
        
        );
       $success['token'] =  $user->createToken('Laravel')->plainTextToken;
        $success['name'] =  $user->name;
        $user->remember_token =  $success['token'];
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authentiation'=>[
                'token'=>$success['token']
                ]
        ],200);
    }

}
