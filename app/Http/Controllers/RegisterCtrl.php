<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use App\Mail\verifyAccount;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterCtrl extends Controller
{
    //


    public function signup(Request $request)
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

        try{
            $user = new User();
            $user->username = $request->username;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            // $user->remember_token = str_random(10);
            $user->setup = 0;
            $user->save();
        }
        catch(\Throwable $exp)
        {
            return response()->json(["error" => $exp->getMessage()], 400);
        }

        //send mail
        $code = str_rand(4);
        try{
            Mail::to($request->email)->send(new verifyAccount($request->username, $code));
        }
        catch(\Throwable $exp)
        {

        }

        $success['status'] = "SUCCESS";
        $success['code'] = "USER_CREATED";
        $success['message'] = "Account created successfully! Please verify your email address.";

        return response()->json(["success" => $success], 200);
    }

    public function verifyAccount(Request $request){

        $validator = Validator::make($request->all(), [
            'code' => 'required|numeric|digits:4',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            // send validation failed message
            $errors = $validator->errors()->all();
            $error["message"] = $errors[0];
            $error['status'] = 'ERROR';
            $error['code'] = 'VALIDATION_ERROR';
            return response()->json(["error" => $error], 400);
        }

        $user = User::where('email', $request->email)->first();

        if($user->setup == 1){
            $error['status'] = 'ERROR';
            $error['code'] = 'ACCOUNT_ALREADY_VERIFIED';
            $error['message'] = 'Account already verified!';
            return response()->json(["error" => $error], 400);
        }

        if($user->email_verification_code == $request->code){
            $user->email_verified_at = now();
            $user->setup = 1;
            $user->save();

            $token = $user->createToken($user->id)->accessToken;
            $success['token'] = $token->token;
            $success['status'] = "SUCCESS";
            $success['code'] = "ACCOUNT_VERIFIED";
            $success['message'] = "Account verified successfully!";

            return response()->json(["success" => $success], 200);
        }
        else{
            $error['status'] = 'ERROR';
            $error['code'] = 'INVALID_CODE';
            $error['message'] = 'Invalid verification code!';
            return response()->json(["error" => $error], 400);
        }
    }
}
