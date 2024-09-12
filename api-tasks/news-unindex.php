<?php
  include_once('../_config.php');
  $apiKey = $_SERVER['CASCADE_API'];
  $type = !empty($_GET['type']) ? $_GET['type'] : '';
  $id   = !empty($_GET['id']) ? $_GET['id'] : '';
?>

  <form method="GET">
    <label for="type">Type</label><br />
    <select name="type" id="type">
      <option value="page" <?php echo ( $type == 'page' ) ? "selected" : ''; ?>>Page</option>
      <option value="folder" <?php echo ( $type == 'folder' ) ? "selected" : ''; ?>>Folder</option>
      <option value="file" <?php echo ( $type == 'file' ) ? "selected" : ''; ?>>File (image, pdf, etc)</option>
    </select>
    <br />
    <label for="id">Cascade ID</label><br />
    <input type="text" name="id" id="id" value="<?php echo ( !empty($id) ? $id : ''); ?>" />
    <br />
    <input type="submit" value="Do the magic!" />
  </form>
<?php
  $url  = "https://cms.umkc.edu/api/v1/";
  $auth = array(
    'authentication' => array(
      'apiKey' => $apiKey
    )
  );
  $auth = json_encode($auth);

  $ch = curl_init($url.'/read/'.$type.'/'.$id);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($auth)
  ));

  $result = json_decode(curl_exec($ch), true);

  $pages = $result['asset']['folder']['children'];

  foreach ( $pages as $page ){{
    echo $page['id'].",";
  }}
  exit();

  $multiCurl = array();
  $result = array();
  $mh = curl_multi_init();
// Loop each of the pages
  foreach ( $pages as $i => $page ){
    $id = $page['id'];
    $type = $page['type'];
    highlight_string(var_export($page, true));
    if ( $type == "page" ){
      $fetchURL = $url."read/".$type.'/' . $id;
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

  exit();

  $index = null;
  do { // DO the execution.
    curl_multi_exec($mh, $index);
  } while ( $index > 0 );

  foreach ( $multiCurl as $k => $ch){
    $thisThing = json_decode(curl_multi_getcontent($ch), true);
    $thisThing = $thisThing['asset'];
    // Existing MetaData
    $metaData = $thisThing['page']['metadata'];

    $thisThing['page']['shouldBeIndexed'] = false;

    $fields = array(
      'authentication' => array(
        'apiKey' => $apiKey
      ),
      "asset" => $thisThing
    );

    $fields = json_encode($fields);
    // Here we go....
    $url  = $url."edit";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($fields)
    ));

    $result = json_decode(curl_exec($ch), true);
    highlight_string(var_export($result, true));
    highlight_string(var_export($thisThing, true));
    curl_multi_remove_handle($mh, $ch);
}
curl_multi_close($mh);
