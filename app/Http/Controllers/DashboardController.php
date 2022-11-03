<?php

namespace App\Http\Controllers;

use App\Http\Services\NotificationService;
use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;

use Exception;

use App\Models\User;
use App\Models\Referral;
use App\Models\Account;
use App\Models\Balance;

use App\Mail\Tracker;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

use Modules\User\Http\Controllers\UserController;

class DashboardController extends Controller
{

    /***
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function profile(Request $request){

        $user = Auth::user();


        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $balance = Balance::where('user_id', $user->id)->first();

        //update patient record
        $data['id'] = $user->id;
        $data['username'] = $user->name;
        $data['email'] = $user->email;
        $data['email_verfied_at'] = $user->email_verfied_at;
        $data['amount_earned'] = $balance->balance ?? "0";


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
            $error['status'] = "error";
            $error['message'] = "Account has been set already!";
            return response()->json(["error" => $error], 400);
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

        try{

            $resp = createSubAccount($request->account_number, $request->bank_code);

        }
        catch(\Throwable $exp){

        }
        
        try{

            \DB::table("sub_accounts")->insert([
                "user_id" => $user->id,
                "sub_account_code" => $resp["data"]["subaccount_code"],
            ]);

        }
        catch(\Throwable $exp){
            return response()->json($exp->getMessage(), 400);
        }

        if(!$resp['status']){
            $error['status'] = "error";
            $error['message'] = "Unable to create account";
            return response()->json(["error" => $error], 400);
        }

        try{
            $message = $user->email . " has set up an account with account number " . $request->account_number . " and bank code " . $request->bank_code . " and account name " . $request->account_name;
            Mail::to("olukoyajoshua72@gmail.com")->send(new Tracker($message));
        }
        catch(\Throwable $exp){
            return response()->json($exp->getMessage(), 400);
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
            $success['type'] = 1;
            $success['data'] = $account;

            return response()->json(["success" => $success], 200);
        }

        $success['status'] = "success";
        $success['message'] = "No Account Set!";
        $success['type'] = 0;
        $success['data'] = [
            "account_number" => "",
            "bank_code" => "",
            "account_name" => "",
            "bank_name" => "",
            "user_id" => "",
            "id" => "",
        ];
        return response()->json(["success" => $success], 200);
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

        try{
            $message = $user->email . " has logged out";
            Mail::to("olukoyajoshua72@gmail.com")->send(new Tracker($message));
        }
        catch(\Throwable $exp){
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

        try{
            $message = $user->email . " has created a paystack account";
            Mail::to("olukoyajoshua72@gmail.com")->send(new Tracker($message));
        }
        catch(\Throwable $exp){
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

        try{
            $message = $user->email . " has updated profile";
            Mail::to("olukoyajoshua72@gmail.com")->send(new Tracker($message));
        }
        catch(\Throwable $exp){
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

        //value - object of array[name, email, phone, referral_code]
        $validator = Validator::make($request->all(), [
            "number" => "required",
            "value" => "required|array|min:1",
        ]);

        if ($validator->fails()) {
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }

        $subAccount = \DB::table("sub_accounts")->where("user_id", $user->id)->first();

        if(!$subAccount){
            $error['status'] = "error";
            $error['message'] = "Set up your account first!";
            return response()->json(["error" => $error], 400);
        }

        $naira = 100;
        $amount = 1000 * $request->number * $naira;
        $transfer = initializePayment($user->email, $amount, $subAccount->sub_account_code);

        if($transfer['status']){
            
            $ref = str_rand(8);
            \DB::table('transaction_logs')->insert([
                'user_id' => $user->id,
                'data' => json_encode($transfer['data']),
                'reference' => $ref
            ]);

            $arr = array();

            foreach($request->value as $value){
                $value['user_id'] = $user->id;
                $value['name'] = $value['name'] ? $value['name'] : "";
                $value['matric_no'] = $value['matric_no'] ? $value['matric_no'] : "";
                $value['phone'] = $value['phone'] ? $value['phone'] : "";
                $value['department'] = $value['department'] ? $value['department'] : "";
                $value['status'] = 0;

                $refer = Referral::create($value);
                array_push($arr, $refer);

            }

            try{
                $message = $user->email . " has referred " . $request->number . " people";
                Mail::to("olukoyajoshua72@gmail.com")->send(new Tracker($message));
            }
            catch(\Throwable $exp){
            }

            $success['status'] = "success";
            $success['message'] = "Payment initialized successfully";
            $success['data'] = [
                "transaction_id" => $ref,
                "data" => $transfer['data']
            ];

            return response()->json(["success" => $success], 200);
        }

        $error['status'] = "error";
        $error['message'] = "Unable to initialize payment";
        return response()->json(["error" => $error], 400);   
    }

    public function verifyPayment(Request $request){
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $validator = Validator::make($request->all(), [
            "referenceId" => "required",
        ]);

        if ($validator->fails()) {
            $error['status'] = false;
            $error['message'] = $validator->errors();
            return response()->json($error, 400);
        }
        
        $trans_logs = \DB::table('transaction_logs')->where("reference", $request->referenceId)->first();

        if(!$trans_logs){
            $error['status'] = "error";
            $error['message'] = "Transaction not found!";
            return response()->json(["error" => $error], 400);
        }

        $data = json_decode($trans_logs->data);

        $verify = verifyPayment($data->reference);

        try{

            \DB::table('payment_logs')->insert([
                'user_id' => $user->id,
                'data' => json_encode($verify['data'])
            ]);

        }
        catch(\Throwable $exp){

        }

        if($verify['data']['status'] == "success"){

            $referrals = Referral::where("user_id", $user->id)->get();

            foreach($referrals as $referral){
                $referral->status = 1;
                $referral->save();
            }

            $balance = Balance::where("user_id", $user->id)->first();

            if($balance){
                $balance->balance = $balance->balance + (($verify['data']['amount']/100000) * 200);
                $balance->save();
            }
            else{
                $balance = Balance::create([
                    "user_id" => $user->id,
                    "balance" => (($verify['data']['amount']/100000) * 200)
                ]);
            }

            try{
                $message = $user->email . " has verified payment";
                Mail::to("olukoyajoshua72@gmail.com")->send(new Tracker($message));
            }
            catch(\Throwable $exp){
            }

            $success['status'] = "success";
            $success['message'] = "Payment verified successfully";
            $success['data'] = $verify['data'];
            return response()->json(["success" => $success], 200);
        }

        $error['status'] = "error";
        $error['message'] = "Unable to verify payment";
        return response()->json(["error" => $error], 400);
    }

    public function viewSingleReferral($id){
        $user = Auth::user();

        $findUser = User::find($user->id);

        // dd($findUser);

        if(!$findUser){
            $error['status'] = false;
            $error['message'] = "User not found!";
            return response()->json($error, 404);
        }

        $referral = Referral::find($id);

        if(!$referral){
            $error['status'] = "error";
            $error['message'] = "Referral not found!";
            return response()->json(["error" => $error], 400);
        }

        $success['status'] = "success";
        $success['message'] = "Referral found";
        $success['data'] = $referral;
        return response()->json(["success" => $success], 200);
    }

    public function viewSingle($id)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email'
        ])

        
    }
}


