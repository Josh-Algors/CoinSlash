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

function checkRes(){
    return "ghjkl";
}

 function bankVerify(string $accountnumber, string $bankcode)
{
    // dd($accountnumber, $bankcode);
    $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://maylancer.org/api/nuban/api.php?account_number=".$accountnumber."&bank_code=".$bankcode,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => json_encode([
                "account_number" => $accountnumber,
                "bank_code" => $bankcode
            ]),
            CURLOPT_HTTPHEADER => [
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