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

  // Takes the existing V1 Profile form the Folder ID and edits them to be a V2 profile
  public function editProfiles($fid, $type){
    $root = $this->read($fid, "folder");
    $children = $root['asset']['folder']['children'];
    foreach($children as $key => $item){
      if ( $item['type'] == "folder" ) {
        echo "<p><strong>Folder:</strong> " . $item['path']['path'] . '</p>';
        $this->editProfiles($item['id'], 'folder');
      } elseif ( $item['type'] == "page" ) {
        $page = $this->read($item['id'], 'page');
        echo "<p><strong>Page:</strong> " . $page['asset']['page']['name'] . " <br /><strong>Path:</strong> " . $item['path']['path'] . "</p>";
        if ( $page['asset']['page']['contentTypeId'] != '2f03ca41ac1e04cd71fdb91e333c92d6' ){
          echo "<ul>";
          echo "<li>Editing Content Type: <br />";
          var_dump($this->editContentType($item['id'], "2f03ca41ac1e04cd71fdb91e333c92d6"));
          echo "</li><li>Editing Page Content: <br />";
          var_dump($this->editProfile($item['id'], $page));
          echo "</li></ul>";
        } else {
          echo "<p>Page already converted, please do an integrity check.</p>";
        }
      }
    }

    return false;
  }

  public function editProfile($id, $page) {
    $sdn = $page['asset']['page']['structuredData']['structuredDataNodes'];
    $md  = $page['asset']['page']['metadata'];
    $df  = $md['dynamicFields'];
    $cid = "2f03ca41ac1e04cd71fdb91e333c92d6";
    $pid = $page['asset']['page']['parentFolderId'];
    $sid = $page['asset']['page']['siteId'];
    $siteName = $page['asset']['page']['siteName'];
    $department = array(
      'type' => 'text',
      'identifier' => 'department',
      'text' => '::CONTENT-XML-SELECTOR::{dept}',
      'recycled' => false,
    );
    $metaTitle = $df[1]['fieldValues'][0]['value'] . " " . $df[2]['fieldValues'][0]['value'];
    // Department -- look at the site name, and get the value.
    switch($siteName) {
      case str_contains($siteName, "DEV - MCOM"):
        $department['text'] = str_replace("{dept}", "marketing-communications", $department['text']);
        break;
      case str_contains($siteName, "AU - Law"):
        $department['text'] = str_replace("{dept}", "law", $department['text']);
        break;
      case str_contains($siteName, "AU - Conservatory"):
        $department['text'] = str_replace("{dept}", "cons", $department['text']);
        break;
      case str_contains($siteName, "AD - Roo Advising"):
       $department['text'] = str_replace("{dept}", "academic-advising", $department['text']);
       break;
      case str_contains($siteName, ""):
        $department['text'] = str_replace("{dept}", "academic-innovation", $department['text']);
        break;
      case str_contains($siteName, ""):
        $department['text'] = str_replace("{dept}", "academic-support", $department['text']);
        break;
      case str_contains($siteName, "AD - Admissions"):
        $department['text'] = str_replace("{dept}", "admissions", $department['text']);
        break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "bloch-scholars", $department['text']);
      //   break;
      case str_contains($siteName, "AU - Bloch"):
        $department['text'] = str_replace("{dept}", "bloch", $department['text']);
        break;
      case str_contains($siteName, "AD - Career Services"):
        $department['text'] = str_replace("{dept}", "career-services", $department['text']);
        break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "counseling-health-testing-disability-services", $department['text']);
      //   break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "curriculum-assessment", $department['text']);
      //   break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "data-software-application-services", $department['text']);
      //   break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "enrollment-management", $department['text']);
      //   break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "faculty-affairs", $department['text']);
      //   break;
      case str_contains($siteName, "AD - Financial Aid"):
        $department['text'] = str_replace("{dept}", "financial-aid", $department['text']);
        break;
      case str_contains($siteName, "AD - Information Services"):
        $department['text'] = str_replace("{dept}", "information-services", $department['text']);
        break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "information-research", $department['text']);
      //   break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "institutional-effectiveness", $department['text']);
      //   break;
      case str_contains($siteName, "AD - Office of Research Development"):
        $department['text'] = str_replace("{dept}", "institutional-research", $department['text']);
        break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "peer-academic-leadership-program", $department['text']);
      //   break;
      case str_contains($siteName, "AD - Professional Career Escalators"):
        $department['text'] = str_replace("{dept}", "professional-career-escalators", $department['text']);
        break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "registration-records", $department['text']);
      //   break;
      case str_contains($siteName, "AD - Residential Life"):
        $department['text'] = str_replace("{dept}", "residential-life", $department['text']);
        break;
      case str_contains($siteName, "AD - School of Graduate Studies"):
        $department['text'] = str_replace("{dept}", "sgs", $department['text']);
        break;
       case str_contains($siteName, "AU - SESWPS"):
        $department['text'] = str_replace("{dept}", "seswps", $department['text']);
        break;
      case str_contains($siteName, "AU - Humanities and Social Sciences"):
        $department['text'] = str_replace("{dept}", "shss", $department['text']);
        break;
      case str_contains($siteName, "AU - SONHS"):
        $department['text'] = str_replace("{dept}", "sonhs", $department['text']);
        break;
      case str_contains($siteName, "AU - Science and Engineering"):
        $department['text'] = str_replace("{dept}", "sse", $department['text']);
        break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "student-success", $department['text']);
      //   break;
      case str_contains($siteName, "AU - Student Affairs"):
        $department['text'] = str_replace("{dept}", "student-affairs", $department['text']);
        break;
      case str_contains($siteName, "AD - Office of Student Involvement"):
        $department['text'] = str_replace("{dept}", "student-engagement-involvement", $department['text']);
        break;
      case str_contains($siteName, "AD - Multicultural Student Affairs"):
        $department['text'] = str_replace("{dept}", "student-support-multicultural-affairs", $department['text']);
        break;
      // case str_contains($siteName, ""):
      //   $department['text'] = str_replace("{dept}", "student-conduct-civility", $department['text']);
      //   break;
      case str_contains($siteName, "AD - Undergraduate Research and Creative"):
        $department['text'] = str_replace("{dept}", "undergraduate-research", $department['text']);
        break;
      case str_contains($siteName, "AD - Library"):
        $department['text'] = str_replace("{dept}", "library", $department['text']);
        break;
      //case str_contains($siteName, ""):
      //  $department['text'] = str_replace("{dept}", "", $department['text']);
      //  break;
      default:
        $department = null;
    }
    $sortName = array(
      'name' => 'sort',
      'fieldValues' => array(
        array(
          'value' => !empty($df[2]['fieldValues'][0]['value']) ? $df[2]['fieldValues'][0]['value'] : null
        )

      )
    );

    $title = array(
      'type' => 'text',
      'identifier' => 'title',
      'text' => !empty($df[4]['fieldValues'][0]['value']) ? $df[4]['fieldValues'][0]['value'] : null,
      'recycled' => false
    );

    $additionalTitle = !empty($df[5]['fieldValues'][0]['value']) ? array(
      'type' => 'text',
      'identifier' => 'title',
      'text' => !empty($df[5]['fieldValues'][0]['value']) ? $df[5]['fieldValues'][0]['value'] : null,
      'recycled' => false,
    ) : null;

    $pronouns = array(
      'type' => 'text',
      'identifier' => 'pronouns',
      'text' => !empty($df[3]['fieldValues'][0]['value']) ? $df[3]['fieldValues'][0]['value'] : null,
      'recycled' => false
    );

    $phone = array(
      'type' => 'text',
      'identifier' => 'phone',
      'text' => !empty($df[7]['fieldValues'][0]['value']) ? $df[7]['fieldValues'][0]['value'] : null,
      'recycled' => false
    );

    $email = array(
      'type' => 'text',
      'identifier' => 'email',
      'text' => !empty($df[8]['fieldValues'][0]['value']) ? $df[8]['fieldValues'][0]['value'] : null,
      'recycled' => false
    );

    $office = array(
      'type' => 'text',
      'identifier' => 'office',
      'text' => !empty($df[9]['fieldValues'][0]['value']) ? $df[9]['fieldValues'][0]['value'] : null,
      'recycled' => false
    );

    $expertise = !empty( $df[10]['fieldValues'][0]['value']) ? array(
      'type' => 'group',
      'identifier' => 'extra',
      'structuredDataNodes' => array(
        array(
          'type' => 'text',
          'identifier' => 'feature',
          'text' => 'no',
          'recycled' => false
        ),
        array(
          'type' => 'text',
          'identifier' => 'label',
          'text' => 'Expertise',
          'recycled' => false
        ),
        array(
          'type' => 'text',
          'identifier' => 'details',
          'text' => !empty($df[10]['fieldValues'][0]['value']) ? $df[10]['fieldValues'][0]['value'] : null,
          'recycled' => false
        )
      )
    ) : null;

    $image = array(
      'type' => 'asset',
      'identifier' => 'image',
      'fileId' => !empty($sdn[0]['fileId']) ? $sdn[0]['fileId'] : null,
      'assetType' => 'file',
      'recycled' => false
    );

    $cv = array(
      'type' => 'asset',
      'identifier' => 'cv',
      'fileId' => !empty($sdn[1]['fileId']) ? $sdn[0]['fileId'] : null,
      'assetType' => 'file',
      'recycled' => false
    );
    $linksGroup = $sdn[2]['structuredDataNodes'];
    $links = array(
      'type' => 'group',
      'identifier' => 'link',
      'structuredDataNodes' => array()
    );

    $bio = array(
      'type' => 'text',
      'identifier' => 'wysiwyg',
      'text' => !empty($sdn[3]['text']) && $sdn[3]['text'] !== "Bio goes here..." ? $sdn[3]['text'] : null,
      'recycled' => false
    );

    foreach( $linksGroup as $key => $link ) {
      $sdn = $link['structuredDataNodes'];
      if ( !empty($sdn[0]['text']) ) {
        array_push($links['structuredDataNodes'],
            array(
              'type' => 'text',
              'identifier' => 'type',
              'text' => 'external'
            ),
            array(
              'type' => 'text',
              'identifier' => 'text',
              'text' => $sdn[1]['text']
            ),
            array(
              'type' => 'text',
              'identifier' => 'external',
              'text' => $sdn[0]['text']
            ),
            array (
              'type' => 'text',
              'identifier' => 'callout',
              'text' => 'no',
              'recycled' => false,
            )
        );
      }
    }

    $asset = array(
      "asset" => array(
        "page" => array(
          "id" => $id,
          "contentTypeId" => $cid,
          "parentFolderId" => $pid,
          "siteId" => $sid,
          "structuredData" => array(
            "structuredDataNodes" => array()
          ),
          'metadata' => array(
            'title' => $metaTitle,
            'dynamicFields' => array()
          )
        )
      )
    );

    if ( $image ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $image);
    }
    if ( $cv ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $cv);
    }
    if ( $title ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $title);
    }
    if ( $additionalTitle ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $additionalTitle);
    }
    if ( $department ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $department);
    }
    if ( $pronouns ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $pronouns);
    }
    if ( $phone ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $phone);
    }
    if ( $email ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $email);
    }
    if ( $office ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $office);
    }
    if ( $bio ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $bio);
    }
    if ( $links ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $links);
    }
    if ( $expertise ) {
      array_push($asset['asset']['page']['structuredData']['structuredDataNodes'], $expertise);
    }
    if ( $sortName ) {
      array_push($asset['asset']['page']['metadata']['dynamicFields'], $sortName);
    }

    $ch = curl_init($this->_cmsUrl.'/edit/page'.$id);
    $fields = array(
      'authentication' => array(
        'apiKey' => $this->_cmsKey
      ),
      "asset" => $asset['asset']
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
