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
  $children = $result['asset']['folder']['children'];

  // highlight_string(var_export($children, true));

  $multiCurl = array();
  $result = array();
  $mh = curl_multi_init();

  foreach ( $children as $i => $child ){
    $id = $child['id'];
    $type = $child['type'];
    $fetchURL = $url ."read/${type}/".$id;
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
  $index = null;
  do { // Do the thing
    curl_multi_exec($mh, $index);
  } while ( $index > 0 );

  foreach ( $multiCurl as $k => $ch ) {
    $oldPage = json_decode(curl_multi_getcontent($ch), true);
    $oldPage = $oldPage['asset']['page'];
    $id = $oldPage['id'];
    $path = $oldPage['path'];
    $contentTypeId = $oldPage['contentTypeId'];
    $sdn = $oldPage['structuredData']['structuredDataNodes'];
    if ( $contentTypeId == $oldContentTypeID ) {
      $oldPage['contentTypeId'] = $newContentTypeID;
      // unset($oldPage['structuredData']['structuredDataNodes']);
      unset($oldPage['pageConfigurations']);

      $path = explode("/", $path);
      $level = str_contains($path[1], "undergraduate") ? "undergraduate" : "graduate";
      $tuition = ( $level == "undergraduate" ) ? "ug-" : "grad-";
      if ( str_contains($path[2], "conservatory") ) {
        $tuition .= "conservatory";
      } elseif ( str_contains($path[2], "bloch") ) {
        $tuition .= "bloch";
      } elseif ( str_contains($path[2], "dentistry") ) {
        $tuition .= "dentistry";
      } elseif ( str_contains($path[2], "social-work") ) {
        $tuition .= "seswps";
      } elseif ( str_contains($path[2], "law") ) {
        $tuition .= "law-jd";
      } elseif ( str_contains($path[2], "medicine") ) {
        $tuition .= "med-early";
      } elseif ( str_contains($path[2], "nursing") ) {
        $tuition .= "nursing";
      } elseif ( str_contains($path[2], "pharmacy") ) {
        $tuition .= "";
      } elseif ( str_contains($path[2], "engineering") ) {
        $tuition .= "sse";
      } else {
        $tuition .= "other";
      }
      // echo "<h1>OldPage</h1>";
      // highlight_string(var_export($oldPage, true));
      // echo "<hr/>";
      // Page Variables to pull over...
      $title = $oldPage['metadata']['title'];
      $basic = $sdn[2]['structuredDataNodes'];
      $degree = str_replace('::CONTENT-XML-SELECTOR::', '', $basic[0]['text']);
      $degree = $degree == "masters" ? "Master's" : ucfirst($degree);
      $format = $basic[7]['text']; // Cleanup as needed for multi select
      $format = str_replace("in-person", "In-Person", $format);
      $format = str_replace("hybrid", "Hybrid", $format);
      $format = str_replace("online", "Online", $format);
      // Basic Information Group = $sdn[2]
      $programType = $basic[0]['text']; // Needs to be matched appropriately
      $academicUnit = $basic[2]['text']; // Needs to be matched appropriately
      $tuitionRate = ""; // Probably needs to be a switch of some sort
      $whyTitle = "Why UMKC";
      $supplemental = $sdn[8]['structuredDataNodes'];
      $storyTellingText = !empty($supplemental[1]['text']) ? $supplemental[1]['text'] : false;
      $storyTellingVideo = !empty($supplemental[2]['text']) ? $supplemental[2]['text'] : false;
      $storyTellingImage = $supplemental[0];
      $courses = $sdn[7];
      $coursesTitle = "Potential Courses";
      $coursesIntro = "";
      $course = !empty($courses['text']) ? $courses['text'] : false;
      $applicationMessage = $programType;
      $financialAid = "";

      $fields = array(
        'authentication' => array(
          'apiKey' => $apiKey
        ),
        "asset" => array(
          "page" => $oldPage
        )
      );
      $fields = json_encode($fields);
      $ch = curl_init($url.'edit/page/'.$id);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($fields)
      ));
      $result = json_decode(curl_exec($ch), true);
      curl_close($ch);

      // echo "<h2>First Edit</h2>";
      // highlight_string(var_export($result, true));
      // After the content type is updated, read the file....
      $ch = curl_init($url.'read/page/'.$id);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($auth)
      ));

      $newPage = json_decode(curl_exec($ch), true);
      // echo "<h2>New Page Read</h2>";
      // highlight_string(var_export($newPage, true));
      // Set the new Fields with the old values
      $npsdn = $newPage['asset']['page']['structuredData']['structuredDataNodes'];
      // Details = $npsdn[3];
      $npsdn[3]['structuredDataNodes'][2]['text'] = $format; // Format
      $npsdn[3]['structuredDataNodes'][3]['text'] = $level; // Level
      if ( $level == "undergraduate" ) {
        $npsdn[3]['structuredDataNodes'][4]['text'] = $tuition; // UG Tuition
      } else {
        $npsdn[3]['structuredDataNodes'][5]['text'] = $tuition; // Grad Tuition
      }
      $npsdn[3]['structuredDataNodes'][6]['text'] = ucfirst($degree); // Make sure this matches!
      // Why UMKC
      $npsdn[5]['structuredDataNodes'][0]['text'] = "Why UMKC";

      // StoryTelling
      $npsdn[7]['structuredDataNodes'][1] = $storyTellingImage;
      $npsdn[7]['structuredDataNodes'][3] = $storyTellingText;
      $npsdn[7]['structuredDataNodes'][7] = $storyTellingVideo;
      // Courses
      $npsdn[9]['structuredDataNodes'][0] = $coursesTitle; // Title
      $npsdn[9]['structuredDataNodes'][1] = ""; // WYSIWYG
      $npsdn[9]['structuredDataNodes'][2]['structuredDataNodes'] = format_courses($course); // Individual Courses...

      // Application
      $npsdn[10]['structuredDataNodes'][0]['text'] = $level;
      // Finaid
      $npsdn[12]['structuredDataNodes'][0]['text'] = "Reduce your costs. Maximize your investments.";

      // Edit the page with the new values in npsdn.
      $newPage['asset']['page']['structuredData']['structuredDataNodes'] = $npsdn;
      highlight_string(var_export($newPage, true));
      $fields = array(
        'authentication' => array(
          $apiKey
        ),
        $newPage
      );

      $fields = json_encode($fields);
      $ch = curl_init($url."edit/page/".$id);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($fields)
      ));
      $final = json_decode(curl_exec($ch), true);
      echo "<h2>Final</h2>";
      highlight_string(var_export($final, true));
      break;
    }
  }
  curl_multi_close($mh);
function explode_li($string) {
  $array = explode("</li>", $string);
  foreach ( $array as $key => $val ) {
    $array[$key] = strip_tags($val, "<strong>");
  }
  array_pop($array);
  return $array;
}

function format_courses($string) {
  $courseArray = explode_li($string);
  $cleanArray = array();
  $pattern = '/\<strong\>(.*?)\<\/strong\>/';
  foreach ( $courseArray as $key => $val ){
    preg_match($pattern, $val, $title);
    $content = preg_replace("$pattern", "", $val);
    $cleanArray[$key] = array(
      'type' => 'group',
      'identifier' => 'card',
      'structuredDataNodes' => array(
        array(
          'type' => 'text',
          'identifier' => 'title',
          'text' => strip_tags($title[0]),
          'recycled' => false,
        ), array(
          'type' => 'text',
          'identifier' => 'description',
          'text' => strip_tags($content),
          'recycled' => false,
        ),
      ),
      'recycled' => false
    );
      // "title" => strip_tags($title[0]), "content" => $content);
  }
  return $cleanArray;
}
