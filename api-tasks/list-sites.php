<?php
  include_once('../_config.php');
  
  $data       = json_decode(file_get_contents("php://input"), true);  
  
  highlight_string(var_export($data, true));
  
  exit();
  
  
  $apiKey = $_SERVER['CASCADE_API'];
  $auth = array(
    'authentication' => array(
      'apiKey' => $apiKey
    )
  );
  $auth = json_encode($auth);
  $url = "https://cascade.xavier.edu/api/v1/";

  $ch = curl_init($url.'listSites/');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($auth)
  ));
  
  $result = json_decode(curl_exec($ch), true);
  
  echo json_encode(
    array(
      'total' => sizeof($result['sites']),
      'sites' => $result['sites']
    )
  );