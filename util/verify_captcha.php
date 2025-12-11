<?php

function validate_captcha($secret, $response) {

    $captcha_url = "https://www.google.com/recaptcha/api/siteverify";
    $captcha_url .= "?secret=".$secret;
    $captcha_url .= "&response=".$response;
    
    $ch = curl_init($captcha_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    $data = curl_exec($ch);
    
    curl_close($ch);
     
    $response=json_decode($data,true);
    
    if ($response["success"]) {
        return true;
    }
    else {
        return false;
    }

}