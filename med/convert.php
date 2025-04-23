<?php
  include_once('../_config.php');
  $apiKey = $_SERVER['CASCADE_API'];
  $type = !empty($_GET['type']) ? $_GET['type'] : '';
  $id   = !empty($_GET['id']) ? $_GET['id'] : '';
  $url = "https://cms.umkc.edu/api/v1/";
  $oldContentTypeID = "c4673baaac1e007602a374a4757f0dfe"; // Today - Post
  $newContentTypeID = "247bfa60ac1e00763862b588df9c54ff"; // Insider - Post
?>

  <form method="GET">
    <label for="instance">Cascade Instance</label><br />
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
  ## FOLDER ID:
  $auth = array(
    'authentication' => array(
      'apiKey' => $apiKey
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
    // highlight_string(var_export($page, true));
    if ( $type == "page" ){
      $fetchURL = $url . '/read/'.$type.'/' . $id;
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
    $thisThing = $thisThing['asset']['page'];
    // highlight_string(var_export($thisThing, true));
    if ( $thisThing['contentTypeId'] == $oldContentTypeID ){
      $wysiwyg = $thisThing['structuredData']['structuredDataNodes'][0]['structuredDataNodes'][5]['text'];
      // var_dump($wysiwyg);
      // Update the ContentType ID
      $thisThing['contentTypeId'] = $newContentTypeID;
      unset($thisThing['pageConfigurations']);
      highlight_string(var_export($thisThing, true));
      // die("Checking the initial edit");
      $fields = array(
        'authentication' => array(
          'apiKey' => $apiKey
        ),
        "asset" => array(
            'page' => $thisThing
          )
      );

      $fields = json_encode($fields);
      // Here we go....to edit the contentTypeID
      // exit("STOPPED BEFORE EDIT");
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
      // After the ContentType is updated...edit the WYSIWYG field
      // highlight_string(var_export($thisThing, true));
      curl_multi_remove_handle($mh, $ch);
      echo "<h1>POST Content Type Update</h1>";
      highlight_string(var_export($result, true));
      echo "<hr />";
      $result = array();
      // Read the page again.
      $url  = $url."read";
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
      // Update the result with the WYSIWYG content from above since the ContentType has now changed
      $result['asset']['page']['structuredData']['structuredDataNodes'][3]['text'] = $wysiwyg;
      highlight_string(var_export($result, true));

      $fields = array(
        'authentication' => array(
          'apiKey' => $apiKey
        ),
        $result
      );
      $fields = json_encode($fields);
      #result
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
