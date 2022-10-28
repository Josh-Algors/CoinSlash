<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use App\Mail\verifyAccount;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    //
    public function login(Request $request){

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            // send validation failed message
            $errors = $validator->errors()->all();
            $error["message"] = $errors[0];
            $error['status'] = 'ERROR';
            $error['code'] = 'VALIDATION_ERROR';
            return response()->json(["error" => $error], 400);
        }

        $checkEmail = User::where('email', $request->username)->first();
        $checkUsername = User::where('username', $request->username)->first();

        if($checkEmail || $checkUsername){
            if($checkEmail){
                $user = $checkEmail;
            }
            else{
                $user = $checkUsername;
            }

            if(Hash::check($request->password, $user->password)){
                $token = $user->createToken($user->id)->accessToken;
                $success['token'] = $token->token;
                $success['status'] = "SUCCESS";
                $success['code'] = "LOGIN_SUCCESS";
                return response()->json(["success" => $success], 200);
            }
            else{
                $error['message'] = "Invalid password";
                $error['status'] = "ERROR";
                $error['code'] = "INVALID_PASSWORD";
                return response()->json(["error" => $error], 400);
            }
        }
        else{
            $error['message'] = "Invalid username or email";
            $error['status'] = "ERROR";
            $error['code'] = "INVALID_USERNAME_OR_EMAIL";
            return response()->json(["error" => $error], 400);
        }

    }
}
