<?php
  // Remove /latest if you want the basic one...
  $url = 'https://www.name-coach.com/api/private/v5/participants/latest';
  $fields = array(
    'email_list' => 'string@of.address,to@look.up'
  );
  
  $getUri = $url."?".http_build_query($fields);
  
  // Take me out....
  $getUri = $url."?"."tokens=[`mcmulleng@xavier.edu`]";
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($ch, CURLOPT_URL, $getUri);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'Authorization: C-JyZjtGhy1-u-sNPyg5'
  ));
  
  $result = json_decode(curl_exec($ch), true);
  
  highlight_string(var_export($result, true));