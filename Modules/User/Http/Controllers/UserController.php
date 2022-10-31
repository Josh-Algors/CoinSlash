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
use App\Models\Otp;
use App\Models\Balance;

use App\Mail\NotificationMail;
use App\Mail\ForgotPasswordMail;

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
use Illuminate\Support\Facades\Mail;

use Carbon\Carbon;

class UserController extends Controller
{

    //first registration stage
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|email',
            'username' => 'required|string',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response(['status' => false,'message' => 'Validation errors' .  $validator->errors(), 'data'=>[]], 422);
        }

        $input = $request->all();
        $email = isset($input['email']) ? $input['email'] : false;
        $phone = isset($input['phone']) ? $input['phone'] : false;
        $username = $input['username'];
        $password = $input['password'];
        //check if user category exist
       
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
                'name' => $username,
                'email' => $email,
                'password' => Hash::make($input['password']),
            ]);
        }
        catch(\Throwable $exp)
        {
            Log::error($exp->getMessage());
            $error['status'] = false;
            $error['message'] = $exp->getMessage();
            return response()->json(["error" => $error], 400);
        }
    
        try{

            Mail::to($email)->send(new NotificationMail($username, $code));

        }
        catch(\Throwable $exp){
            Log::error($exp->getMessage());
            $error['status'] = false;
            $error['message'] = $exp->getMessage();
            return response()->json(["error" => $error], 400);
        }

        $user = User::where('email', $email)->first();
        try{
            Otp::create([
                'user_id' => $user->id,
                'otp' => $code,
                'used' => 0,
                'expired_at' => Carbon::now()->addMinutes(60)->timestamp,
            ]);
        }
        catch(\Throwable $exp)
        {
            Log::error($exp->getMessage());
            $error['status'] = false;
            $error['message'] = $exp->getMessage();
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
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response(['status' => false, 'message' => 'Validation errors. ' .  $validator->errors(), 'data'=>false], 422);
        }
        
        $input = $request->all();
        $email = isset($input['email']) ? $input['email'] : false;
        $otp = trim($input['otp']);


        $findUsermail = User::where('email', $email)->first();
        $findUsername = User::where('name', $email)->first();

        if(!$findUsermail && !$findUsername){
            $error['status'] = false;
            $error['message'] = 'Account does not exist';
            return response()->json(["error" => $error], 400);
        }

        if($findUsermail){
            $user = $findUsermail;
        }
        else{
            $user = $findUsername;
        }

        // $verify = OtpService::verifyOtp($email, $otp);
        $findOtp = Otp::where('user_id', $user->id)->where('used', 0)->orderBy('id', 'desc')->first();

        if(!$findOtp){
            $error['status'] = false;
            $error['message'] = 'Invalid OTP';
            return response()->json(["error" => $error], 400);
        }

        if($findOtp->otp != $otp){
            $error['status'] = false;
            $error['message'] = 'Invalid OTP';
            return response()->json(["error" => $error], 400);
        }

        $findOtp->used = 1;
        $findOtp->save();

        //update user
        try{

            $user->email_verfied_at = now();
            $user->save();

        }catch(\Throwable $exp){
            Log::error($exp->getMessage());
            $error['status'] = false;
            $error['message'] = $exp->getMessage();
            return response()->json(["error" => $error], 400);
        }

        // NotificationService::Email($user->email, 'Your email has been successfully verified. You can now proceed to onboarding stage');
        try{

            Balance::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);
            
        }
        catch(\Throwable $exp){

        }

        $tokenResult = $user->createToken('Personal Access Token')->accessToken;
        
        $success['status'] = "success";
        $success['message'] = 'Email successfully verified';
        $success['access_token'] = $tokenResult;
        return response()->json(["success" => $success], 200);
    }
    
    public function resendOtpp(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            $error['status'] = "error";
            $error['message'] = $validator->errors();
            return response()->json(["error" => $error], 400);
        }

        $user = User::where('email', $request->email)->first();
        $users = User::where('name', $request->email)->first();

        if(!$user && !$users){
            $error['status'] = "error";
            $error['message'] = 'Account does not exist';
            return response()->json(["error" => $error], 400);
        }

        if($user){
            $user = $user;
        }
        else{
            $user = $users;
        }

        if($user->email_verfied_at){
            $error['status'] = "error";
            $error['message'] = 'Account already verified';
            return response()->json(["error" => $error], 400);
        }

        $code = rand(1000, 9999);

        try{
            Otp::create([
                'user_id' => $user->id,
                'otp' => $code,
                'used' => 0,
                'expired_at' => Carbon::now()->addMinutes(60)->timestamp,
            ]);
        }
        catch(\Throwable $exp)
        {
            Log::error($exp->getMessage());
            $error['status'] = false;
            $error['message'] = $exp->getMessage();
            return response()->json(["error" => $error], 400);
        }

        try{

            Mail::to($user->email)->send(new NotificationMail($user->name, $code));

        }
        catch(\Throwable $exp){
            Log::error($exp->getMessage());
            $error['status'] = false;
            $error['message'] = $exp->getMessage();
            return response()->json(["error" => $error], 400);
        }

        $success['status'] = "success";
        $success['message'] = 'Kindly verify your account. We have sent you an email with the code.';
        $success['email'] = $user->email;
        return response()->json(["success" => $success], 200);
    }

    public function forgotPasswordd(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            $error['status'] = "error";
            $error['message'] = $validator->errors();
            return response()->json(["error" => $error], 400);
        }

        $usermail = User::where('email', $request->email)->first();
        $username = User::where('name', $request->email)->first();

        if(!$usermail && !$username){
            $error['status'] = "error";
            $error['message'] = 'Account does not exist';
            return response()->json(["error" => $error], 400);
        }

        if($usermail){
            $user = $usermail;
        }
        else{
            $user = $username;
        }

        if(!$user){
            $error['status'] = "error";
            $error['message'] = 'Account does not exist';
            return response()->json(["error" => $error], 400);
        }

        $code = rand(1000, 9999);

        Otp::create([
            'user_id' => $user->id,
            'otp' => $code,
            'used' => 0,
            'expired_at' => Carbon::now()->addMinutes(60)->timestamp,
        ]);

        try{
            

            Mail::to($request->email)->send(new ForgotPasswordMail($request->email, $code));

        }
        catch(\Throwable $exp){
            $error['status'] = false;
            $error['message'] = $exp->getMessage();
            return response()->json(["error" => $error], 400);
        }

        $success['status'] = "success";
        $success['message'] = 'Kindly verify your account. We have sent you an email with the code.';
        $success['email'] = $request->email;
        return response()->json(["success" => $success], 200);

    }

    public function setNewPassword(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'otp' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $error['status'] = "error";
            $error['message'] = $validator->errors();
            return response()->json(["error" => $error], 400);
        }

        $findUsermail = User::where('email', $request->email)->first();
        $findUsername = User::where('name', $request->email)->first();

        if(!$findUsermail && !$findUsername){
            $error['status'] = false;
            $error['message'] = 'Account does not exist';
            return response()->json(["error" => $error], 400);
        }

        if($findUsermail){
            $user = $findUsermail;
        }
        else{
            $user = $findUsername;
        }

        $findOtp = Otp::where('user_id', $user->id)->where('used', 0)->orderBy('id', 'desc')->first();

        if(!$findOtp){
            $error['status'] = false;
            $error['message'] = 'Invalid OTP';
            return response()->json(["error" => $error], 400);
        }

        if($findOtp->otp != $request->otp){
            $error['status'] = false;
            $error['message'] = 'Invalid OTP';
            return response()->json(["error" => $error], 400);
        }

        $findOtp->used = 1;
        $findOtp->save();

        //update user
        try{

            $user->password = Hash::make($request->password);
            $user->save();

        }catch(\Throwable $exp){
            $error['status'] = false;
            $error['message'] = $exp->getMessage();
            return response()->json(["error" => $error], 400);
        }

        $success['status'] = "success";
        $success['message'] = 'Password successfully changed';
        return response()->json(["success" => $success], 200);

    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['status' => false, 'message' => 'Validation errors. ' .  $validator->errors(), 'data'=>false], 422);
        }
        
        $email = $request->email;

        $userMail = User::where('email', $email)->first();
        $userName = User::where('name', $email)->first();

        if(!$userMail && !$userName){
            $error['status'] = false;
            $error['message'] = 'Account does not exist';
            return response()->json(["error" => $error], 400);
        }

        if($userMail){
            $user = $userMail;
        }
        else{
            $user = $userName;
        }

        if(!(Hash::check($request->password, $user->password))){
            $error['status'] = false;
            $error['message'] = 'Invalid password';
            return response()->json(["error" => $error], 400);
        }

        $tokenResult = $user->createToken('Personal Access Token')->accessToken;

        $success['status'] = "success";
        $success['message'] = 'Login successful';
        $success['access_token'] = $tokenResult;

        return response()->json(["success" => $success], 200);
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
