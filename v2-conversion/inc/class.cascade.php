<?php
class Cascade {
  private $_cmsKey;
  private $_auth;
  public function __construct() {
    $this->_cmsUrl = "https://cms.umkc.edu/api/v1";
    $this->_cmsKey = getenv('CMS_KEY');
    $this->_auth = array(
      'authentication' => array(
        'apiKey' => $this->_cmsKey
      )
    );
  }
  public function read($id, $type) {
    $auth = json_encode($this->_auth);
    $ch = curl_init($this->_cmsUrl.'/read/'.$type.'/'.$id);
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

  public function getPageIds($jsonURL) {
    $url = filter_var($jsonURL, FILTER_SANITIZE_URL);

    if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);
      $result = json_decode(curl_exec($ch), true);
      curl_close($ch);
      if ( curl_errno($ch) ) {
        $result = curl_error($ch);
      }
      $return = array("status" => "valid", "message" => $result );
    } else {
      $return = array("status" => "error", "message" => "Invalid web address");
    }

    return $return;
  }
  public function editContentType($pid, $contentTypeId="ce6ecbe1ac1e04cd7c22888496479b43"){
    $asset = $this->read($pid, "page");
    $asset['asset']['page']['contentTypeId'] = $contentTypeId;
    unset($asset['asset']['page']['pageConfigurations']);
    $fields = array(
      'authentication' => array(
        'apiKey' => $this->_cmsKey
      ),
      'asset' => $asset['asset']
    );

    $ch = curl_init($this->_cmsUrl.'/edit/page/'.$pid);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen(json_encode($fields))
    ));
    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $result;
  }

  public function editContent($pid, $content){
    $asset = $this->read($pid, "page");
    echo "<hr />";
    // Hero Update
    $asset['asset']['page']['structuredData']['structuredDataNodes'][0]['structuredDataNodes'][0]['text'] = "roomascot-pattern-03";
    // Text Block
    $asset['asset']['page']['structuredData']['structuredDataNodes'][1]['structuredDataNodes'][2]['text'] = $content;
    $fields = array(
      'authentication' => array(
        'apiKey' => $this->_cmsKey
      ),
      'asset' => $asset['asset']
    );
    $ch = curl_init($this->_cmsUrl.'/edit/page/'.$pid);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen(json_encode($fields))
    ));
    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $result;
  }

  public function createAccordionBlock($content, $type="block"){
    $ch = curl_init($this->_cmsUrl.'/create');
    $fields = array(
      'authentication' => array(
        'apiKey' => $this->_cmsKey
      ),
      "asset" => $content
    );

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen(json_encode($fields))
    ));

    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $result;
  }
}
