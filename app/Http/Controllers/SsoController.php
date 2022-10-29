<?php

  

namespace App\Http\Controllers;

use App\Http\Services\NotificationService;
use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;

use Exception;

use App\Models\User;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\User\Http\Controllers\UserController;

class SsoController extends Controller

{

    public function redirectToSSO($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        $target_url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
        return response(['status' => true, 'message' => "redirect to target url", 'data' =>  ['target_url'=>$target_url]], 200);

    }



    public function handleSsoCallback($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }
        try {
            $user = Socialite::driver($provider)->stateless()->user();
        } catch (ClientException $exception) {
            return response(['status' => false, 'message' => "Invalid credentials provided.", 'data' =>  []], 422);

        }

        $finduser = User::where('email', $user->getEmail())->first();   

        if($finduser){
            $msg = "Login successful";
            $token = $finduser->createToken('token-name')->accessToken;
            $redirect =  UserController::redirect($finduser->cat);
        }else{
            $randPassword = Str::random(8);
            //send password to user
            $body = "Your default password to login to the platform is ". $randPassword;
            $userCreated = User::firstOrCreate(
                [
                    'email' => $user->getEmail()
                ],
                [
                    'phone_email_verified'=>true,
                    'phone_email_verified_at' => now(),
                    'name' => $user->getName(),
                    'status' => true,
                    'password'=>Hash::make($randPassword),
                    $provider.'_id' => $user->getId(),
                    ]
                );
            NotificationService::Email($user->getEmail(), $body);
            $msg = "Registration successful";
            $token = $userCreated->createToken($provider. ' token')->accessToken;
            
            
            $redirect =  UserController::redirect($userCreated->cat);
        }
        
        $data = [
            'redirect_url' => $redirect,
            'access_token'=>$token
        ];

        return response(['status' => true, 'message' => $msg, 'data' =>  $data], 200);
    }

    /**
     * @param $provider
     * @return JsonResponse
     */
    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            return response(['status' => false, 'message' => 'Please login using facebook, google', 'data' => []], 422);
        }
    }

}
