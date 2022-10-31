<?php

namespace Modules\User\Http\Controllers;

use App\Http\Services\NotificationService;
use App\Http\Services\OtpService;
use App\Http\Services\UserService;

use App\Models\Country;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\Patient;
use App\Models\Balance;

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
       dd("user");
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response(['status' => false,'message' => 'Validation errors' .  $validator->errors(), 'data'=>[]], 422);
        }

        $input = $request->all();
        $email = isset($input['email']) ? $input['email'] : false;
        $phone = isset($input['phone']) ? $input['phone'] : false;
        $name = $input['name'];

        $user = User::where('email', $email)->first();

        if($user){
            $error['status'] = false;
            $error['message'] = 'Email already exists';
            return response()->json(["error" => $error], 400);
        }

        $findUsername = User::where('name', $username)->first();

        if($findUsername){
            $error['status'] = false;
            $error['message'] = 'Username already exists';
            return response()->json(["error" => $error], 400);
        }

        $code = rand(1000, 9999);
        try{
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($input['password']),
            ]);
        }
        catch(\Throwable $exp)
        {
            Log::error($exp->getMessage());
            $error['status'] = false;
            $error['message'] = 'Something went wrong';
            return response()->json(["error" => $error], 400);
        }
    
        try{
            Mail::to($email)->send(new RegisterMail($username, $code));
        }
        catch(\Throwable $exp){
            Log::error($exp->getMessage());
            $error['status'] = false;
            $error['message'] = 'Something went wrong';
            return response()->json(["error" => $error], 400);
        }

        $success['status'] = "success";
        $success['message'] = 'Kindly verify your account. We have sent you an email with the code.';
        $success['email'] = $email;
        return response()->json(["success" => $success], 200);
       
    }
    

    public static function verifyEmail(Request $request){
        // return User::find(1)->createToken('myapp')->accessToken;
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'email' => 'required_without:phone|email',
        ]);

        if ($validator->fails()) {
            // send validation failed message
            $errors = $validator->errors()->all();
            $error["message"] = $errors[0];
            $error['status'] = 'ERROR';
            $error['code'] = 'VALIDATION_ERROR';
            return response()->json($error, 400);
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
        try{
            $checkMailPatient = Patient::where('email', $email)->first();
            $checkPhonePatient = Patient::where('phone', $phone)->first();
            if($checkMailPatient){
                $checkMailPatient->is_verified = 1;
                $checkMailPatient->update();
            }
            if($checkPhonePatient){
                $checkPhonePatient->is_verified = 1;
                $checkPhonePatient->update();
            }
        }
        catch(\Exception $e){
            $error['status'] = 'error';
            $error['message'] = $e->getMessage();
            return response()->json($error, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        NotificationService::Email($user->email, 'Your email has been successfully verified. You can now proceed to onboarding stage');
       
        // dd($user);
        // generate token for user 
        $tokenResult = $user->createToken('Personal Access Token')->accessToken;
        
        $category = strtolower($user->cat);
        
        $redirect = self::redirect($category);

        $data = [
            'redirect_url' =>  $redirect,
            'access_token' => $tokenResult,
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

        $category = strtolower($user->cat);
        
        $redirect = self::redirect($category);

        $data = [
            'redirect_url' =>  $redirect,
            'access_token' => $tokenResult,
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
            $redirect = 'https://patientapi' . str_replace(['http://', 'https://'], '.', $base_url);
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