<?php
  include_once('../_config.php');
  $type = !empty($_GET['type']) ? $_GET['type'] : '';
  $id   = !empty($_GET['id']) ? $_GET['id'] : '';
  $url = ( !empty($_GET['instance']) && $_GET['instance'] == "prod" ? "https://cascade.xavier.edu/api/v1/" : "https://cascadet.xavier.edu/api/v1/");
  $oldContentTypeID = "399bb6890afd01585ce8912e5e799f72";
  $newContentTypeID = "1f26c6690afd015800eafaaeba730f6b";
?>
  
  <form method="GET">
    <label for="instance">Cascade Instance</label><br />
    <select name="instance" id="instane">
      <option value="dev">DEVELOPMENT (Cadet)</option>
      <option value="prod">PRODUCTION</option>
    </select><br />
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
  <hr />
<?php
  //   header("Content-Type: application/json");  
  ## FOLDER ID: 6b47e3430afd02580af9699778f5b51a
  $auth = array(
    'authentication' => array(
      'username' => $_SERVER['CASCADE_USER'],
      'password' => $_SERVER['CASCADE_PASS'] 
    )
  );
  $auth = json_encode($auth);
  
  $ch = curl_init($url.'read/folder/'.$id);
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
    highlight_string(var_export($page, true));
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
      // Hide this page from Nav
      $hideFromNav = ( !empty($metaData['dynamicFields'][0]['fieldValues']) && $metaData['dynamicFields'][0]['fieldValues'][0]['value'] == "Yes" ) ? "Show" : "Hide";
      $authKey     = array_search("requireAuth", array_column($thisThing['page']['metadata']['dynamicFields'], 'name'));
      if ( isset($authKey) ){
        $includeAuth = $thisThing['page']['metadata']['dynamicFields'][$authKey];
      }
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
      $url  = $url."edit";
      
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

      // Set the new tags!
      $thisThing['page']['tags'] = $tags;
      // Exclude from Navigation...
      $thisThing['page']['metadata']['dynamicFields'] = array(
        array(
          'name' => 'NavInclude',
          'fieldValues' => array(
            array(
              "value" => $hideFromNav
            )
          )
        ), $includeAuth
      );
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
      $url  = $url."edit";
      
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
