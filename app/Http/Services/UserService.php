<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService 
{
    public static function register($full_name, $email, $category_id, $password, $phone, $country_id=null){
        if(!$email && !$phone){
            return [false, 'email and phone number cannot be empty'];
        }

        if($email != false){
            $existing = User::where('email', $email)->first();
        }else{
            $existing = User::where('phone', $phone)->first();
        }

        if(!empty($existing)){
            if($existing->phone_email_verified == true){
                Log::error("user with email/phone already exist");
                return [false, "user with  email/phone already exist. kindly proceed to login"];
            }
            //regenerate token and send to email / phone
            $otp = OtpService::generateOtp($existing->id, $existing->email, $existing->phone);

            if(!$otp[0]){
                return [false, $otp[1]];
            }
            return [true, $existing];
        }

        $user = new User();
        $user->name = trim($full_name);
        $user->email = $email;
        $user->phone = $phone;
        $user->category_id = $category_id;
        $user->country_id = $country_id;
        $user->password = Hash::make(trim($password));
        try{
            $user->save();
        }catch(\Exception $e){
            Log::error($e);
            return [false, 'error while creating account'];
        }
        
        //generate and send otp
        $otp = OtpService::generateOtp($user->id, $user->email, $user->phone);
        if(!$otp[0]){
            return [false, $otp[1]];
        }
        return [true, $user];
    }
    
    public static function resendOtp($email, $phone){
        if(!$email && !$phone){
            return [false, 'email and phone number cannot be empty'];
        }

        if($email != false){
            $user = User::where('email', $email)->first();
        }else{
            $user = User::where('phone', $phone)->first();
        }


        if(!$user){
            return [false, 'user not found'];
        }

        $otp = OtpService::generateOtp($user->id, $user->email, $user->phone);

        if(!$otp[0]){
            return [false, $otp[1]];
        }

        if($email != false){
            return [true, $email];
        }else{
            return [true, $phone];
            }
    }

    public static function accountRecoveryRequest($email='', $phone=''){
        if(!empty($email)){
            $user = User::where('email', $email)->first();
        }else{
            $user = User::where('phone', $phone)->first();
        }
        if(!$user){
            return [false, 'user not found'];
        }

        $otp = OtpService::generateOtp($user->id, $user->email);

        if(!$otp[0]){
            return [false, $otp[1]];
        }

        return [true, $user->email];
    }

    public static function assignRole($user){
        if(empty($user->cat)){
            return [false, 'user does not have a category'];
        }
        $cat = strtolower($user->cat);
        
        if($cat == 'Other Service Providers'){
            try{
                $user->assignRole('other');
            }catch(\Exception $e){
                Log::error($e);
                return [false, 'error occurred while assigning role to user'];
            }     
        }else{
            try{
                $user->assignRole($cat);
            }catch(\Exception $e){
                Log::error($e);
                return [false, 'error occurred while assigning role to user'];
            }
        }

        return [true, 'role assigned to user successfully'];
    }
}
