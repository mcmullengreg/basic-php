<?php
  include_once('../_config.php');
  $apiKey = $_SERVER['CASCADE_API'];
  $auth = array(
    'authentication' => array(
      'apiKey' => $apiKey
    )
  );
  $auth = json_encode($auth);
  $url = "https://cms.umkc.edu/api/v1/";

  $ch = curl_init($url.'listSites/');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($auth)
  ));

  $result = json_decode(curl_exec($ch), true);
  // foreach($result['sites'] as $item){
  //   echo '"'.$item['path']['path'].'",';
  // }

  highlight_string(var_export((
    array(
      'total' => sizeof($result['sites']),
      'sites' => $result['sites']
    )
  ), true));
