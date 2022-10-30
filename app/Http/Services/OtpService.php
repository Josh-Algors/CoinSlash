<?php

namespace App\Http\Services;

use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService{
    // handle all OTP related issues 
    public static function generateOtp($user_id, $email, $code){
        // $myotp = rand(1000, 9999);
        $existingOtp = Otp::whereUserId($user_id)->whereUsed(false)->first();
        if($existingOtp){
            $existingOtp->used = true;
            $existingOtp->update();
        }
        //create otp record
        $otp = new Otp();
        $otp->user_id = $user_id;
        $otp->otp = Hash::make($code);
        $otp->used = false;
        $otp->expired_at = Carbon::now()->addMinutes(10)->timestamp;
        try{
            $otp->save();
            $text = "Kindly use this code - ". $code . " to verify your account.";
        }catch(\Exception $e){
            Log::error('error generating OTP.'. $e);
            return [false, 'error generating OTP.'];
        }

        
        $send_otp = NotificationService::Email($email, $text);
       
                
        if(!$send_otp){
            Log::error("Error occur while sending OTP to user");
            return [false, 'error generating OTP.'];
        }
        return [true, $code];
    }

    public static function verifyOtp($email, $otp){

        if(empty($email)){

            $user = User::where('name', $email)->first();

        }
        else{

            $user = User::where('email', $email)->first();

        }


        if(empty($user)){
            $error['status'] = "error";
            $error['message'] = "User not found";
            return response()->json(["error" => $error], 400);
        }

        $myotp = Otp::whereUserId($user->id)->whereUsed(false)->first();

        $code = rand(1000, 9999);

        if(!$myotp){

            self::generateOtp($user->id, $user->email, $code);

            $success['status'] = "success";
            $success['message'] = "No OTP found. New OTP sent to your email";
            return response()->json(["success" => $success], 200);

        }

        // check if otp has expired
        if((Carbon::now()->timestamp) > ($myotp->expired_at)){
            //update otp to used
            $myotp->used = true;
            $myotp->update();

            if(empty($email)){

                $user = User::where('name', $email)->first();

            }else{

                $user = User::where('email', $email)->first();

            }

            self::generateOtp($user->id, $user->email, $code);

            $success['status'] = "success";
            $success['message'] = "OTP expired. New OTP sent to your email";
            return response()->json(["success" => $success], 200);
        }
        if(!Hash::check($otp, $myotp->otp)){
            return [false, 'Invalid OTP'];
        }

        //update OTP
        try{
            $myotp->used = true;
            $myotp->update();
        }catch(\Exception $e){
            return [false, 'error encountered while updating otp'];
        }

        //update user
        try{
            $user->phone_email_verified = true;
            $user->phone_email_verified_at = now();
            $user->status = true;
            $user->update();
        }catch(\Exception $e){
            Log::error($e);
            return [false, 'error encountered while updating user'];
        }
        return [true, 'Verified'];
    }
}
