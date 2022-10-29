<?php

namespace App\Http\Services;

use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService{
    // handle all OTP related issues 
    public static function generateOtp($user_id, $email, $phone){
        $myotp = rand(1000, 9999);
        $existingOtp = Otp::whereUserId($user_id)->whereUsed(false)->first();
        if($existingOtp){
            $existingOtp->used = true;
            $existingOtp->update();
        }
        //create otp record
        $otp = new Otp();
        $otp->user_id = $user_id;
        $otp->otp = Hash::make($myotp);
        $otp->used = false;
        $otp->expired_at = Carbon::now()->addMinutes(10)->timestamp;
        try{
            $otp->save();
            $text = "Your One-Time-Password is: ". $myotp;
        }catch(\Exception $e){
            Log::error('error generating OTP.'. $e);
            return [false, 'error generating OTP.'];
        }

        //send otp to email
        if(!empty($email)){
            $send_otp = NotificationService::Email($email, $text);
        }else{
            $send_otp = NotificationService::Sms($phone, $text);
        }
                
        if(!$send_otp){
            Log::error("Error occur while sending OTP to user");
            return [false, 'error generating OTP.'];
        }
        return [true, $myotp];
    }

    public static function verifyOtp($email, $otp, $phone){
        if(empty($email)){
            $user = User::where('phone', $phone)->first();
        }else{
            $user = User::where('email', $email)->first();
        }


        if(empty($user)){
            return [false, 'user not found'];
        }
        // if($user->phone_email_verified == true){
        //     return [false, 'user email has earlier been verified'];
        // }
        $myotp = Otp::whereUserId($user->id)->whereUsed(false)->first();
        if(!$myotp){
            self::generateOtp($user->id, $user->email, $user->phone);
            return [false, 'no OTP found for user. New OTP has been sent to your email'];
        }
        // check if otp has expired
        if((Carbon::now()->timestamp) > ($myotp->expired_at)){
            //update otp to used
            $myotp->used = true;
            $myotp->update();
            if(empty($email)){
                $user = User::where('phone', $phone)->first();
            }else{
                $user = User::where('email', $email)->first();
            }

            self::generateOtp($user->id, $user->email, $user->phone);
            return [false, 'Expired OTP. New OTP has been sent to your phone or email'];
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
