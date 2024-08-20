<?php
  include_once('../_config.php');
  $apiKey = $_SERVER['CASCADE_API'];
  $type = !empty($_GET['type']) ? $_GET['type'] : '';
  $id   = !empty($_GET['id']) ? $_GET['id'] : '';
  $url = "https://cms.umkc.edu/api/v1/";
  $oldContentTypeID = "69f90974ac1e04cd74e7d4f95db630ac"; // GREG_DEV - AU Pharmacy Content Type ID
  $newContentTypeID = "ce6ecbe1ac1e04cd7c22888496479b43"; // DEV - Framework
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
  ## FOLDER ID: 69f8e8c1ac1e04cd74e7d4f96a0e5549
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
    if ( $thisThing['contentTypeId'] == $oldContentTypeID ){
      $wysiwyg = $thisThing['structuredData']['structuredDataNodes'][1]['structuredDataNodes'][5]['text'];
      // Update the ContentType ID
      $thisThing['contentTypeId'] = $newContentTypeID;
      unset($thisThing['pageConfigurations']);
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
      $result['asset']['page']['structuredData']['structuredDataNodes'][1]['structuredDataNodes'][2]['text'] = $wysiwyg;
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
