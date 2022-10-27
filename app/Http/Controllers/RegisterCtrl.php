<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

class RegisterCtrl extends Controller
{
    //


    public function test(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required|email|unique:users',
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

    }
}
