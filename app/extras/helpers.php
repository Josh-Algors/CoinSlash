<?php

use App\Models\User;
use App\Models\Patient;


function formatName($name){
    $name = strtolower($name);
    return $name;
}

function checkPatient($user_id){

    $patient = Patient::where('user_id', $user_id)->first();
   
    if($patient){
        return true;
    }
    else{
        return false;
    }
}

function str_rand($length, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
    if (!is_int($length) || $length < 0) {
        return false;
    }
    //$characters_length = strlen($characters) - 1;
    $string = '';

    for ($i = $length; $i > 0; $i--)
    {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}

 function bankVerify(string $accountnumber, string $bankcode)
{
    // dd($accountnumber, $bankcode);
    $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://nubapi.com/api/verify?account_number=". $accountnumber . '&bank_code=' . $bankcode,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => json_encode([
                "account_number" => $accountnumber,
                "bank_code" => $bankcode
            ]),
            CURLOPT_HTTPHEADER => [
              'Authorization: Bearer ZYrZxaN7zkyKSk20woPvxuPQj4KtVlJqZR8w3rcg',
              "content-type: application/json"
            ]
          ));

          $response = curl_exec($curl);
          $err = curl_error($curl);

          if($err){
            // there was an error contacting the myIdentitypass API
            die('Curl returned error: ' . $err);
          }

          $tranx = json_decode($response, true);

          return $tranx;
}

function createSubAccount(string $accountnumber, string $bankcode)
{
    // dd($accountnumber, $bankcode);
    $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/subaccount",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                "business_name" => "CoinSlash", 
                "bank_code" => $bankcode, 
                "account_number" => $accountnumber,
                "percentage_charge" => 80, 
            ]),
            CURLOPT_HTTPHEADER => [
            "Authorization: Bearer sk_test_261b476a34373366572bbd0a3bd2951f84689140",
            "content-type: application/json"
            ]
          ));

          $response = curl_exec($curl);
          $err = curl_error($curl);

          if($err){
            // there was an error contacting the myIdentitypass API
            die('Curl returned error: ' . $err);
          }

          $tranx = json_decode($response, true);

        //   print_r($tranx['detail']);

          return $tranx;
}

function transferToSubAccount()
{

}

function verifyPayment($reference)
{
    $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/".$reference,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
            // "Authorization: Bearer sk_live_e3490d42f30765f4f76d39a7a653c773d5f3b257",
            "Authorization: Bearer sk_test_261b476a34373366572bbd0a3bd2951f84689140",
            "content-type: application/json"
            ]
          ));

          $response = curl_exec($curl);
          $err = curl_error($curl);

          if($err){
            // there was an error contacting the myIdentitypass API
            die('Curl returned error: ' . $err);
          }

          $tranx = json_decode($response, true);

        //   print_r($tranx['detail']);

          return $tranx;
}
//sk_test_261b476a34373366572bbd0a3bd2951f84689140
//sk_live_e3490d42f30765f4f76d39a7a653c773d5f3b257
//sk_live_e3490d42f30765f4f76d39a7a653c773d5f3b257
function initializePayment($email, $amount, $subaccount)
{
        // dd($accountnumber, $bankcode);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                "email" => $email, 
                "amount" => $amount,
                "subaccount" => $subaccount, 
            ]),
            CURLOPT_HTTPHEADER => [
            // "Authorization: Bearer sk_live_e3490d42f30765f4f76d39a7a653c773d5f3b257",
            "Authorization: Bearer sk_test_261b476a34373366572bbd0a3bd2951f84689140",
            "content-type: application/json"
            ]
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if($err){
            // there was an error contacting the myIdentitypass API
            die('Curl returned error: ' . $err);
        }

        $tranx = json_decode($response, true);

        //   print_r($tranx['detail']);

        return $tranx;
}