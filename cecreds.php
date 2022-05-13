<?php
  // Remove /latest if you want the basic one...
  $url = 'https://test.secure.cecredentialtrust.com:8086/v2/cecredentialvalidate';
  $clientid = $SERVER['CLIENTID'];
  $cedid = $_SERVER['CEDID'];
  
  
  if ( !defined('CURL_SSLVERSION_TLSV1_2')) {
    define('CURL_SSLVERSION_TLSV1_2', 6);
  }
  
  $ch = curl_init("$url/$clientid/$cedid");
  curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSV1_2);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    array(
      'ContentType: application/json',
      'Accept: application/json'
    )
  );
  
  $result = json_decode(curl_exec($ch), true);
  
  highlight_string(var_export($result, true));