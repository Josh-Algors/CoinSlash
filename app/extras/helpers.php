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