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

  $multiCurl = array();
  $result = array();
  $mh = curl_multi_init();
  // Loop each of the pages
  foreach ( $pages as $i => $page ){
    $id = $page['id'];
    $type = $page['type'];
    if ( $type == "page" ){
      $fetchURL = $url . '/'.$type.'/' . $id;
      $multiCurl[$i] = curl_init();
      curl_setopt($multiCurl[$i], CURLOPT_URL, $fetchURL);
      curl_setopt($multiCurl[$i], CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($multiCurl[$i], CURLOPT_POSTFIELDS, $auth);
      curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($auth)
      ));  
      curl_multi_add_handle($mh, $multiCurl[$i]);
    }
  }

  $index = null;
  do { 
    curl_multi_exec($mh, $index);
  } while ( $index > 0 );
  
  
  foreach ( $multiCurl as $k => $ch){
    $thisThing = json_decode(curl_multi_getcontent($ch), true);
    $thisThing = $thisThing['asset']['page'];
    $contentTypeID = $thisThing['contentTypeId'];
    $metaData = $thisThing['metadata'];
    $auth = $metaData['dynamicFields'][1];
    $showNav = $metaData['dynamicFields'][0];
    
    curl_multi_remove_handle($mh, $ch);
  }
  curl_multi_close($mh);
