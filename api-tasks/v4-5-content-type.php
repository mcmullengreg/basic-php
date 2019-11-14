<?php
  include_once('../_config.php');
  header("Content-Type: application/json");  
  ## FOLDER ID: 6b47e3430afd02580af9699778f5b51a
  $url  = "https://cascadet.xavier.edu/api/v1/read";
  $auth = array(
    'authentication' => array(
      'username' => $_SERVER['CASCADE_USER'],
      'password' => $_SERVER['CASCADE_PASS'] 
    )
  );
  $auth = json_encode($auth);
  
  $ch = curl_init($url.'/folder/6b47e3430afd02580af9699778f5b51a');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($auth)
  ));
  
  $result = json_decode(curl_exec($ch), true);
  
  $pages = $result['asset']['folder']['children'];
  
  foreach ( $pages as $page ){
    var_dump($page);
  }