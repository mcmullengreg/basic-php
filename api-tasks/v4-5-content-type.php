<?php
  include_once('../_config.php');  
  $type = !empty($_GET['type']) ? $_GET['type'] : '';
  $id   = !empty($_GET['id']) ? $_GET['id'] : '';  
  $oldContentTypeID = "6b47c0c30afd02580af96997ae1c5f2c";
  $newContentTypeID = "2480c31e0afd0258306942cd2ac7c7de";
?>
  
  <form method="GET">
    <label for="type">Type</label><br />
    <select name="type" id="type" disabled="">
      <option value="folder" selected="">Folder</option>
    </select>
    <br />
    <label for="id">Cascade ID</label><br />
    <input type="text" name="id" id="id" value="<?php echo ( !empty($id) ? $id : ''); ?>" />
    <br />
    <input type="submit" value="Do the magic!" />
  </form>
<?php
  //   header("Content-Type: application/json");  
  ## FOLDER ID: 6b47e3430afd02580af9699778f5b51a
  $url  = "https://cascadet.xavier.edu/api/v1/read";
  $auth = array(
    'authentication' => array(
      'username' => $_SERVER['CASCADE_USER'],
      'password' => $_SERVER['CASCADE_PASS'] 
    )
  );
  $auth = json_encode($auth);
  
  $ch = curl_init($url.'/folder/'.$id);
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
  do { // DO the execution.
    curl_multi_exec($mh, $index);
  } while ( $index > 0 );
  
  foreach ( $multiCurl as $k => $ch){
    $thisThing = json_decode(curl_multi_getcontent($ch), true);
    if ( $thisThing['asset']['page']['contentTypeId'] == $oldContentTypeID ){
      $thisThing = json_decode(curl_multi_getcontent($ch), true);
      $thisThing = $thisThing['asset'];

      // Existing MetaData
      $metaData = $thisThing['page']['metadata'];
      // Update some of the tags!
      $tags = array(); // Generic array, for pushing any needed tags to.
      // IF the current document INCLUDES Left Nav, we do NOT need the tag; otherwise we do!
      strpos($thisThing['page']['structuredData']['structuredDataNodes'][0]['text'], "::CONTENT-XML-CHECKBOX::Left Navigation") !== FALSE ? '' : array_push($tags, array('name'=>'no-nav'));
      // Old Wysiwyg content
      $wysiwyg = $thisThing['page']['structuredData']['structuredDataNodes'][3]['text'];
      // Update the ContentType ID
      $thisThing['page']['contentTypeId'] = $newContentTypeID;
      unset($thisThing['page']['pageConfigurations']);
      $fields = array(
        'authentication' => array(
        'username' => $_SERVER['CASCADE_USER'],
        'password' => $_SERVER['CASCADE_PASS'] 
        ),
        "asset" => $thisThing
      );
      
      $fields = json_encode($fields);
      // Here we go....
      $url  = "https://cascadet.xavier.edu/api/v1/edit";
      
      $ch = curl_init($url.'/page/'.$id);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($fields)
      ));
      
      $result = json_decode(curl_exec($ch), true);
      highlight_string(var_export($result, true)); 
      exit();
      // Set the new tags!
      $thisThing['page']['tags'] = $tags;
      // Restructure the Data Nodes
      
      $thisThing['page']['structuredData']['structuredDataNodes'][0] = array (
        'type' => 'group',
        'identifier' => 'hero',
        'structuredDataNodes' => 
        array (
          0 => 
          array (
            'type' => 'text',
            'identifier' => 'imgvid',
            'text' => 'image',
            'recycled' => false,
          ),
          1 => 
          array (
            'type' => 'asset',
            'identifier' => 'hero_image',
            'fileId' => '2a7621140afd02580ceb04ec1839091b',
            'filePath' => 'images/content_hero-1x.jpg',
            'assetType' => 'file',
            'recycled' => false,
          ),
          2 => 
          array (
            'type' => 'asset',
            'identifier' => 'hero_image',
            'fileId' => '2a7748fa0afd02580ceb04ec03b5f86d',
            'filePath' => 'images/content_hero-2x.jpg',
            'assetType' => 'file',
            'recycled' => false,
          ),
          3 => 
          array (
            'type' => 'text',
            'identifier' => 'video_id',
            'text' => '',
            'recycled' => false,
          ),
          4 => 
          array (
            'type' => 'asset',
            'identifier' => 'video_preview',
            'assetType' => 'file',
            'recycled' => false,
          ),
          5 => 
          array (
            'type' => 'text',
            'identifier' => 'video_cta',
            'text' => 'Learn More',
            'recycled' => false,
          ),
          6 => 
          array (
            'type' => 'asset',
            'identifier' => 'video_img',
            'assetType' => 'file',
            'recycled' => false,
          ),
        ),
        'recycled' => false,
      );
      $thisThing['page']['structuredData']['structuredDataNodes'][1] = array(
        'type' => 'text',
        'identifier' => 'wysiwyg',
        'text' => $wysiwyg,
        'recycled' => false
      );
      ## This will be the custom files!
      $thisThing['page']['structuredData']['structuredDataNodes'][2] = $thisThing['page']['structuredData']['structuredDataNodes'][5];
      unset($thisThing['page']['structuredData']['structuredDataNodes'][3]);
      unset($thisThing['page']['structuredData']['structuredDataNodes'][4]);
      unset($thisThing['page']['structuredData']['structuredDataNodes'][5]);
      // Exclude from Navigation
      $metaData['dynamicFields'][0]['fieldValues'][0]['value'] = $metaData['dynamicFields'][0]['fieldValues'][0]['value'] == "Yes" ? "Show" : "Hide";
      $authField = $metaData['dynamicFields'][1];
      $showInNav = $metaData['dynamicFields'][0];
      
      // highlight_string(var_export($thisThing, true));    
      curl_multi_remove_handle($mh, $ch);
      
      highlight_string(var_export($thisThing, true));
      

      
      
      $fields = array(
        'authentication' => array(
        'username' => $_SERVER['CASCADE_USER'],
        'password' => $_SERVER['CASCADE_PASS'] 
        ),
        "asset" => $thisThing
      );
      
      $fields = json_encode($fields);
      // Here we go....
      $url  = "https://cascadet.xavier.edu/api/v1/edit";
      
      $ch = curl_init($url.'/page/'.$id);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($fields)
      ));
      
      $result = json_decode(curl_exec($ch), true);
      highlight_string(var_export($result, true)); 
    }
  }
  curl_multi_close($mh);
