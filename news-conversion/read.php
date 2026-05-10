<?php
  include_once('../_config.php');
  $apiKey = $_SERVER['CASCADE_API'];
  $type = !empty($_GET['type']) ? $_GET['type'] : '';
  $id   = !empty($_GET['id']) ? $_GET['id'] : '';
  $url  = "https://cms.umkc.edu/api/v1/read";
  $auth = array(
    'authentication' => array(
      'apiKey' => $apiKey
    )
  );
  $auth = json_encode($auth);
?>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>News Conversion Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body>
    <div class="container">
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
        <input type="submit" class="btn btn-primary" value="Do the magic!" />
      </form>
      <?php if ( $id && $type ) :
        $ch = curl_init($url.'/'.$type.'/'.$id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($auth)
        ));

        $result = json_decode(curl_exec($ch), true);
        echo "<p>Structured Data Node:</p>";
        highlight_string(var_export($result['asset']['page']['structuredData']['structuredDataNodes'], true));

        highlight_string(var_export($result, true));

        endif;
      ?>
    </div>
  </body>
</html>
