<?php
class Cascade {
  private $_cmsKey;
  private $_auth;
  public function __construct() {
    $this->_cmsKey = getenv('CMS_KEY');
    $this->_auth = array(
      'authentication' => array(
        'apiKey' => $this->_cmsKey
      )
    );
  }

  public function read($id, $type) {
    $url  = "https://cms.umkc.edu/api/v1/read";
    $auth = json_encode($this->_auth);

    $ch = curl_init($url.'/'.$type.'/'.$id);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($auth)
    ));

    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $result;
  }
}
