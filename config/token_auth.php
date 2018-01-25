<?php

  // globals
  $encrypt_method = "AES-256-CBC";
  $secret_key = 'Key';
  $secret_iv = 'Iv';

  $key = hash('sha256', $secret_key);

  $iv = substr(hash('sha256', $secret_iv), 0, 16);

  // autenticate token key
  function auth($token) {
    $output = openssl_decrypt(base64_decode($token), "AES-256-CBC", $key, 0, $iv);

    echo $output;
  }

  // create new token key
  function new_auth($username) {
    $random_string = "asd";

    $output = openssl_encrypt($random_string,"AES-256-CBC", $key, 0, $iv);
    $new_token = base64_encode($output);

    // PDO this into DB
  }


 ?>
