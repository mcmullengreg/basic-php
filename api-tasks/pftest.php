<?php
  include_once('../_config.php');
  $apiKey = $_SERVER['CASCADE_API'];
  $type = !empty($_GET['type']) ? $_GET['type'] : '';
  $id   = !empty($_GET['id']) ? $_GET['id'] : '';
  $url = "https://cms.umkc.edu/api/v1/";

  $oldContentTypeID = "a6e1173cac1e007617dc343cd6a481a2"; //Program Finder - Program Page
  $newContentTypeID = "b09237bcac1e04cd17b5ac0d9776b6ae"; //MCOM - Global Framework Program Finder Page
?>
  <form method="GET">
    <label for="instance">Cascade Instance</label><br />
    <label for="type">Type</label><br />
    <select name="type" id="type" disabled="">
      <option value="page" selected="">Page</option>
    </select>
    <br />
    <label for="id">Cascade ID</label><br />
    <input type="text" name="id" id="id" value="<?php echo ( !empty($id) ? $id : ''); ?>" />
    <br />
    <input type="submit" value="Do the magic!" />
  </form>
  <hr />
<?php
  $auth = array(
    'authentication' => array(
      'apiKey' => $apiKey
    )
  );

  $ch = curl_init($url.'read/page/'.$id);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($auth));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($auth))
  ));
  $result = json_decode(curl_exec($ch), true);
  curl_close($ch);


  $page = $result['asset']['page'];
  $id = $page['id'];
  $page['contentTypeId'] = $newContentTypeID;
  unset($page['pageConfigurations']);

  $fields = array(
    'authentication' => array(
      'apiKey' => $apiKey
    ),
    "asset" => array(
      "page" => $page
    )
  );
  $ch = curl_init($url.'edit/page/'.$id);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($fields))
  ));
  $result = json_decode(curl_exec($ch), true);
  curl_close($ch);

  var_dump($result);
