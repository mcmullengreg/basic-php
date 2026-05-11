<?php
class Cascade {
  private $_cmsUrl = "https://cms.umkc.edu/api/v1";
  private $_newsSite = "DEV-News-wwwnews";
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

  public function createFolder($siteName, $folderName, $parentFolderPath = "/posts") {
    $asset = array(
      "asset" => array(
        "folder" => array(
          "name" => $folderName,
          "metadataSetId" => "ce324366ac1e04cd2e8e237fa0c03295",
          "parentFolderPath" => $parentFolderPath,
          "siteName" => $siteName,
        )
      )
    );

    $fields = json_encode(
      array(
        'authentication' => array(
          'apiKey' => $this->_cmsKey
          ),
        'asset' => $asset['asset']
      )
    );
    $ch = curl_init($this->_cmsUrl.'/create');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($fields)
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
      curl_setopt($ch, CURLOPT_USERAGENT, "MCOM Web App: Hi Donald.");
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
  public function editContentType($pid, $contentTypeId="ff17d1dbac1e04cd0d7c54bffdd682f7"){
    return array( "success" => true, "message" => "CALLED. Did not run.\n\n");
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

  public function editContent($asset){
    $pid = !empty($asset['page']['id']) ? $asset['page']['id']: die("<p><strong>No Asset ID Found</strong></p>") ;
    $fields = array(
      'authentication' => array(
        'apiKey' => $this->_cmsKey
      ),
      'asset' => $asset
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

  public function convertInsider($item) {
    preg_match('/^site:\/\/AA - Insider - wwwinsider\/posts\/(\d{4})\/(\d{2})\/(.*)$/', $item['url'], $matches);
    $year = $matches[1];
    $month = $matches[2];
    $fullFolderLookUp = "/posts/{$year}/{$month}";
    // Sanity check to make sure the tool is working based on the path.
      // $checkFolder = $this->read("{$this->_newsSite}/posts", 'folder');
      // highlight_string(var_export($checkFolder['asset']['folder'], true));
    // End sanity Check
    // Check if Year folder exists
    $checkFolder = $this->read("{$this->_newsSite}/posts/{$year}", 'folder');
    if ( !$checkFolder['success'] ){
      $yearFolder = $this->createFolder($this->_newsSite, $year, "/posts");
    }
    // Check if Month folder exists, create it if not.
    $checkFolder = $this->read("{$this->_newsSite}/posts/{$year}/{$month}", 'folder');
    if ( !$checkFolder['success'] ){
      $monthFolder = $this->createFolder($this->_newsSite, $month, "/posts/{$year}");
    }
    // Snag the old content, for future use.
    $oldContent = $this->read($item['id'], 'page');
    $oldImageId = $oldContent['asset']['page']['structuredData']['structuredDataNodes'][1]['structuredDataNodes'][2]['fileId'];
    // Content needs to include:
      // Intro copy
      // Content
    $introCopy = $oldContent['asset']['page']['structuredData']['structuredDataNodes'][2]['text'];
    $articleContent = $oldContent['asset']['page']['structuredData']['structuredDataNodes'][3]['text'];
    $oldPageContent = $introCopy . $articleContent;

    // Old Dynamic Field Values
    $oldDynamicFields = $oldContent['asset']['page']['metadata']['dynamicFields'];
    highlight_string(var_export($oldDynamicFields, true));
    echo '<hr />';
    // Update the ContentType and clear the Page Configuration
    $updateCT = $this->editContentType($item['id']);
    if ( $updateCT['success'] ) {
      echo("Failed to update ContentType on asset {$item['id']}");
    }
    // Pass the old content into the Edit Content Function
    $newStructure = $this->read($item['id'], 'page');
    // highlight_string(var_export($newStructure['asset']['page']['metadata'], true));
    echo '<hr />';
    $newContent = $newStructure['asset']['page']['structuredData']['structuredDataNodes'];
    // Controls Group Updates
    $newContent[0]['structuredDataNodes'][0]['text'] = 'internal'; // audience
    $newContent[0]['structuredDataNodes'][1]['text'] = 'standard'; // mode
    // Media Updates
    $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][0]['fieldId'] = $oldImageId; // Image Asset
    $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][1]['text']    = ''; // Alt
    $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][2]['text']    = ''; // Caption
    $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][3]['text']    = ''; // Credit
    $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][4]['text']    = 'default'; // Style
    // Basic Content
    $newContent[2]['structuredDataNodes'][0]['text'] = $oldPageContent;
    // Metadata Updates, dynamic fields ONLY needed.
    // GM 5.11.26 - Map the new and old fields that need to be placed. Get them written up so we can convert to
    // the new Metadata Set for Posts.
    $newMetadata = $newStructure['asset']['page']['metadata'];
    $newMetadata['dynamicFields'][0][''] = '';
    $newMetadata['dynamicFields'][1][''] = '';
    $newMetadata['dynamicFields'][2][''] = '';
    $asset = [
      'asset' => [
        'page' => [
          'dynamicFields' => array(
            // 0 - Category
            [],
            // 1 - Unit
            [],
            // 2 - Featured
            [],
            // 3 - NoIndex
            []
          ),
        ]
      ]
    ];

  }
}
