<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterCtrl extends Controller
{
    //


    public function test(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'username' => 'required',
        //     'email' => 'required|email|unique:users',
        //     'password' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     // send validation failed message
        //     $errors = $validator->errors()->all();
        //     $error["message"] = $errors[0];
        //     $error['status'] = 'ERROR';
        //     $error['code'] = 'VALIDATION_ERROR';
        //     return response()->json(["error" => $error], 400);
        // }

        try{
            $user = new User();
            $user->username = "test";
            $user->email = "test@gmail.com";
            $user->password = Hash::make("jkjkjkjk");
            $user->save();
        }
        catch(\Throwable $exp)
        {
            return response()->json(["error" => $exp->getMessage()], 400);
        }
        return response()->json(["message" => "success"], 200);
    }
}
