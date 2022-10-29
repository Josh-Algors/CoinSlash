<?php

namespace Modules\User\Http\Controllers;

use App\Http\Services\NotificationService;
use App\Http\Services\OtpService;
use App\Http\Services\UserService;

use App\Models\Country;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\Patient;
use App\Models\Referral;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    //first registration stage
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
            'name' => 'required|string',
            'category_id'=>'required|integer',
            'country_id'=>'required|integer',
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ]
        ]);

        if ($validator->fails()) {
            return response(['status' => false,'message' => 'Validation errors' .  $validator->errors(), 'data'=>[]], 422);
        }

        $input = $request->all();
        $email = isset($input['email']) ? $input['email'] : false;
        $phone = isset($input['phone']) ? $input['phone'] : false;
        $name = $input['name'];
        $category_id = $input['category_id'];
        $country_id = $input['country_id'];
        $password = $input['password'];
        //check if user category exist
        if(empty(UserCategory::whereId($category_id)->first())){
            return response()->json(['status' => false, 'message' => 'user category does not exist', 'data' => $input], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //check if country exist
        if(empty(Country::whereId($country_id)->first())){
            return response()->json(['status' => false, 'message' => 'country does not exist', 'data' => $input], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $user = UserService::register($name, $email, $category_id, $password, $phone, $country_id);
        } catch (\Exception $e) {
            Log::error("Error occur while creating this account " . $request->input('email') . json_encode($e));
            return response()->json(['status' => false, 'message' => 'Error occured while creating account', 'data' => $input], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // //dump user to patient db if user category is patient
        // try{
        //     $findUser = $user[1];
        //     // dd($findUser);
        //     if($findUser->category_id == 3){

        //         if(checkPatient($findUser->id)){
        //             $error['status'] = false;
        //             $error['message'] = 'User already exist';

        //             return response()->json($error, 400);
        //         }

        //         Patient::create([
        //             'user_id'=>$findUser->id,
        //             'name'=>$findUser->name,
        //             'email'=>$findUser->email,
        //             'phone'=>$findUser->phone,
        //             'country'=>$findUser->country_id,
        //             'referral_code'=> str_rand(6),
        //             'is_verified' => 0,
        //         ]);

        //         if($request->referral_code){
        //             Referral::create([
        //                 'referral_code'=>$request->referral_code,
        //                 'referred_email'=>$request->email,
        //             ]);
        //         }
        //     }
        // }
        // catch (\Exception $e){
        //     $error['status'] = 'error';
        //     $error['message'] = 'Error occured while creating account';
        //     return response()->json($error, Response::HTTP_INTERNAL_SERVER_ERROR);
        // }

        //send otp to user
        if(!$user[0]){
            return response(['status' => false,'message' => $user[1], 'data' =>$user[1]], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response(['status' => true,'message' => 'Account created successfully. An otp code has been sent to your email.', 'data' =>$user[1]], Response::HTTP_OK);
    }
    

    public static function verifyEmail(Request $request){
        // return User::find(1)->createToken('myapp')->accessToken;
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
        ]);

        if ($validator->fails()) {
            return response(['status' => false, 'message' => 'Validation errors. ' .  $validator->errors(), 'data'=>false], 422);
        }
        
        $input = $request->all();
        $email = isset($input['email']) ? $input['email'] : false;
        $phone = isset($input['phone']) ? $input['phone'] : false;
        $otp = trim($input['otp']);

        $verify = OtpService::verifyOtp($email, $otp, $phone);

        if(!$verify[0]){
            return response(['status' => false, 'message' => $verify[1] , 'data'=>false], 422);
        }

        //update user
        try{
            $user = User::where('email', $email)->first();
            $user->phone_email_verified = true;
            $user->phone_email_verified_at = now();
            $user->update();

            //asign role
            $role = UserService::assignRole($user);
            if(!$role[0]){
                return response(['status' => false, 'message' => $role[1] , 'data'=>false], 422);
            }
        }catch(\Exception $e){
            Log::error($e);
            return response(['status' => false, 'message' => 'error encountered while updating user record.' , 'data'=>false], 422);
        }

        //update patient record
        // try{
        //     $checkMailPatient = Patient::where('email', $email)->first();
        //     $checkPhonePatient = Patient::where('phone', $phone)->first();
        //     if($checkMailPatient){
        //         $valid = $checkMailPatient;
        //         $checkMailPatient->is_verified = 1;
        //         $checkMailPatient->update();
        //     }
        //     if($checkPhonePatient){
        //         $valid = $checkPhonePatient;
        //         $checkPhonePatient->is_verified = 1;
        //         $checkPhonePatient->update();
        //     }
        // }
        // catch(\Exception $e){
        //     $error['status'] = 'error';
        //     $error['message'] = $e->getMessage();
        //     return response()->json($error, Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
        NotificationService::Email($user->email, 'Your email has been successfully verified. You can now proceed to onboarding stage');
       
        // dd($user);
        // generate token for user 
        $tokenResult = $user->createToken('Personal Access Token')->accessToken;
        
        $category = strtolower($user->cat);
        
        $redirect = self::redirect($category);

        $data = [
            'redirect_url' =>  $redirect,
            'access_token' => $tokenResult,
            'onboarded' => $user->onboarded,
        ];
        
        return response(['status' => true, 'message' => $verify[1] , 'data'=>$data], 200);
    }
    
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['status' => false, 'message' => 'Validation errors. ' .  $validator->errors(), 'data'=>false], 422);
        }
        
        $input = $request->all();
        $email = $input['email'];
        $password = trim($input['password']);

        $user = User::where('email', $email)->first();
        if(empty($user)){
            return response(['status' => false, 'message' => 'user not found', 'data'=>false], 422);
        }

        if(!(Hash::check($password, $user->password))){
            return response(['status' => false, 'message' => 'incorrect password', 'data'=>false], 422);
        }

        $tokenResult = $user->createToken('Personal Access Token')->accessToken;
        $patientRecord = Patient::where('user_id', $user->id)->first();

        $category = strtolower($user->cat);
        
        $redirect = self::redirect($category);

        $data = [
            'redirect_url' =>  $redirect,
            'access_token' => $tokenResult,
            'onboarded' => $user->onboarded
        ];
        

        return response(['status' => true, 'message' => 'login successful' , 'data'=>$data], 200);
    }

    public function addUserCategory(Request $request){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response(['status' => false, 'message' => 'Validation errors. ' .  $validator->errors(), 'data'=>false], 422);
        }
        
        $user = Auth::user();
        $input = $request->all();
        $category_id = $input['category_id'];
        //check if user category exist
        if(empty(UserCategory::whereId($category_id)->first())){
            return response()->json(['status' => false, 'message' => 'user category does not exist', 'data' => $input], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try{
            $user->category_id = $category_id;
            $user->update();
        }catch(\Exception $e){
            Log::error($e);
            return response(['status' => false, 'message' => 'user category not successful added' , 'data'=>['redirect_url'=>'']], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        //asign role
        $role = UserService::assignRole($user);
        if(!$role[0]){
            return response(['status' => false, 'message' => $role[1] , 'data'=>false], 422);
        }
        $redirect = self::redirect($user->cat);
        return response(['status' => true, 'message' => 'user category successful added' , 'data'=>['redirect_url'=>$redirect]], 200);

    }

    public function userProfile(Request $request)
    {
        try{
            $user = Auth::user();
        }catch(\Exception){
            return response()->json(['status' => false, 'message' => 'Error occured while fetching user profile', 'data' => null], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['status' => true, 'message' => 'User profile successfully fetched!', 'data' => $user], Response::HTTP_OK);
    }

    public function failedLogin(Request $request)
    {
        return response()->json(['status' => false, 'message' => 'authentication failed!', 'data' => null], 404);
        
    }

    public static function redirect($category){
        $base_url = url('');
        // return $base_url;
        $cat = strtolower($category);
        if(empty($category)){
            $redirect = route('add_category');
        }elseif($cat == 'admin'){
            $redirect = 'http://adminapi'. str_replace(['http://api.', 'https://api.'], '.', $base_url);
        }elseif(($cat == 'doctor') || ($cat =='nurse')){
            $redirect = 'http://doctor'. str_replace(['http://api.', 'https://api.'], '.', $base_url);
        }elseif($cat == 'pharmacy'){
            $redirect = 'http://pharmacy'. str_replace(['http://api.', 'https://api.'], '.', $base_url);
        }elseif($cat == 'patient'){
            $redirect = 'https://' . str_replace(['http://', 'https://'], '', $base_url);
        }elseif(($cat == 'manufacturer') || ($cat =='distributor')){
            $redirect = 'http://distributor'. str_replace(['http://api.', 'https://api.'], '.', $base_url);
        }elseif(($cat == 'hospital') || ($cat =='clinic')){
            $redirect = 'http://hospital'. str_replace(['http://api.', 'https://api.'], '.', $base_url);
        }elseif($cat == 'Other Service Providers'){
            $redirect = 'http://others'. str_replace(['http://api.', 'https://api.'], '.', $base_url);
        }else{
            $redirect = 'we could not get a redirect url for you category';
        }

        return $redirect;
    }

    public function resendOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
        ]);

        if ($validator->fails()) {
            Log::error($validator->errors());
            return response(['status' => false, 'message' => 'Invalid payload', 'data'=>false], 422);
        }
        
        $input = $request->all();
        $email = isset($input['email']) ? $input['email'] : false;
        $phone = isset($input['phone']) ? $input['phone'] : false;
        
         
        $resend = UserService::resendOtp($email, $phone);

        if(!$resend[0]){
            return response(['status' => false, 'message' => $resend[1] , 'data'=>false], 422);
        }

        return response(['status' => true, 'message' => "OTP code sent to " . $resend[1] , 'data'=>true], 200);

    }


    public function runCommand($cmd){
        try{
            Artisan::call($cmd);
        }catch(\Exception $e){
            return response(['status' => false, 'message' => 'Error occured while running command' , 'data'=>$e], 500);
        } 
        return response(['status' => true, 'message' => 'successful' , 'data'=>true], 200);
    }

}
