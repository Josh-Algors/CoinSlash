<?php

namespace App\Http\Controllers;

use App\Http\Services\NotificationService;
use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;

use Exception;

use App\Models\User;
use App\Models\Referral;
use App\Models\Account;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\User\Http\Controllers\UserController;

class DashboardController extends Controller
{

    public function profile(Request $request){

        $user = Auth::user();


        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        //update patient record
        $data['id'] = $user->id;
        $data['username'] = $user->name;
        $data['email'] = $user->email;
        $data['email_verfied_at'] = $user->email_verfied_at;



        $success['status'] = "success";
        $success['message'] = "User details fetched successfully";
        $success['data'] = $data;
        return response()->json(["success" => $success], 200);
    }

    public function allReferrals(){
     
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $referrals = Referral::where('user_id', $user->id)->get();

        $success['status'] = "success";
        $success['message'] = "User referrals fetched successfully";
        $success['data'] = $referrals;

        return response()->json(["success" => $success], 200);
    }

    public function getBnkCodes()
    {
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $bnkCodes = \DB::table("bank_codes")->get();

        $success['status'] = "success";
        $success['message'] = "Bnk codes fetched successfully";
        $success['data'] = $bnkCodes;

        return response()->json(["success" => $success], 200);
    }

    public function getAccount(Request $request)
    {
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }


        $validator = Validator::make($request->all(), [
            'account_number' => 'required',
            'bank_code' => 'required',
        ]);

        if ($validator->fails()) {
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        $account_number = $request->account_number;
        $bank_code = $request->bank_code;
        $response = bankVerify($account_number, $bank_code);

        if(count($response) > 0){
            $success['status'] = "success";
            $success['message'] = "Account details fetched successfully";
            $success['data'] = $response["account_name"];

            return response()->json(["success" => $success], 200);
        }

        $error['status'] = "error";
        $error['message'] = "Unable to fetch account details";
        return response()->json(["error" => $error], 400);
   
    }

    public function setAccount(Request $request)
    {
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'account_number' => 'required',
            'bank_code' => 'required',
            'account_name' => 'required',
        ]);

        if ($validator->fails()) {
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        $findAccount = Account::where('user_id', $user->id)->first();

        $getBank = \DB::table("bank_codes")->where("code", $request->bank_code)->first();

        if(!$getBank)
        {
            $error['status'] = false;
            $error['message'] = "Invalid bank code";
            return response()->json($error, 400);
        }
        
        if($findAccount){
            $findAccount->delete();
        }

        try{

            $account = new Account();
            $account->user_id = $user->id;
            $account->account_number = $request->account_number;
            $account->bank_code = $request->bank_code;
            $account->account_name = $request->account_name;
            $account->bank_name = $getBank->name;
            $account->save();

        }
        catch(\Throwable $exp){
            $error['status'] = false;
            $error['message'] = $e->getMessage();
            return response()->json($error, 400);
        }

        $success['status'] = "success";
        $success['message'] = "Account details saved successfully";
        $success['data'] = $account;

        return response()->json(["success" => $success], 200);


    }

    public function getPersonalAcc()
    {
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $account = Account::where('user_id', $user->id)->first();

        if($account){
            $success['status'] = "success";
            $success['message'] = "Account details fetched successfully";
            $success['data'] = $account;

            return response()->json(["success" => $success], 200);
        }

        $error['status'] = "error";
        $error['message'] = "No Account Set!";
        return response()->json(["error" => $error], 400);
    }

    public function logout()
    {
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $user = $user->token();
        $user->revoke();

        $success['status'] = "success";
        $success['message'] = "User logged out successfully";
        return response()->json(["success" => $success], 200);
    }

    public function createPaystackAccount(Request $request){

        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            'account_number' => 'required',
            'bank_code' => 'required',
            'account_name' => 'required',
            'bank_name' => 'required',
        ]);

        if ($validator->fails()) {
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        $resp = createSubAccount($request->account_number, $request->bank_code);

        if(!$resp['status']){
            $error['status'] = "error";
            $error['message'] = "Unable to create account";
            return response()->json(["error" => $error], 400);
        }
        
        $success['status'] = "success";
        $success['message'] = "Sub-account created successfully";
        $success['data'] = $resp['data'];
        return response()->json(["success" => $success], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        try{   
            $findUser->name = $request->name ? $request->name : $findUser->name;
            $findUser->email = $request->email ? $request->email : $findUser->email;
            $findUser->password = Hash::make($request->password) ? Hash::make($request->password) : $findUser->password;
            $findUser->save();
        } 
        catch(\Throwable $exp){
            $error['status'] = "error";
            $error['message'] = "Unable to update profile! Either email or username has been used.";
            return response()->json(["error" => $error], 400);
        }

        $success['status'] = "success";
        $success['message'] = "Profile updated successfully";
        $success['data'] = $findUser;
        
        return response()->json(["success" => $success], 200);
    }

    public function referAndEarn(Request $request)
    {
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $getAccount = Account::where('user_id', $user->id)->first();

        if(!$getAccount){
            $error['status'] = "error";
            $error['message'] = "No Account Set!";
            return response()->json(["error" => $error], 400);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        $findReferee = User::where('email', $request->email)->first();

        if($findReferee){
            $error['status'] = "error";
            $error['message'] = "User already exists!";
            return response()->json(["error" => $error], 400);
        }

        $referral = new Referral();
        $referral->user_id = $user->id;
        $referral->email = $request->email;
        $referral->save();

        $success['status'] = "success";
        $success['message'] = "Referral sent successfully";
        $success['data'] = $referral;
        
        return response()->json(["success" => $success], 200);
    }
}
