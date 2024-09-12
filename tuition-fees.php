<?php
  include_once("_config.php");
  $block_id = "e3af19a9ac1e04cd5e95457e070344e4";
  $apiKey = $_SERVER['CASCADE_API'];
  $url = "https://cms.umkc.edu/api/v1/read";
  $auth = array(
    'authentication'  => array(
      'apiKey' => $apiKey
    )
  );
  $auth = json_encode($auth);
  $ch = curl_init($url."/block/".$block_id);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($auth)
  ));

  $result = json_decode(curl_exec($ch), true);
  $result = $result['asset']['xhtmlDataDefinitionBlock']['structuredData']['structuredDataNodes'];

  foreach ( $result as $k => $group ) :
    foreach ( $group['structuredDataNodes'] as $k => $cat ) :
      if ( $k == 0 ) :
        echo "<h2>${cat['text']}</h2>";
      elseif ( end($group['structuredDataNodes']) == $cat ) :
        echo "<hr />";
      endif;
      if ( !empty($cat['structuredDataNodes']) ):
        foreach ( $cat['structuredDataNodes'] as $k => $type ) :
          if ( $k == 0 ) :
            echo "<h3>${type['text']}</h3>";
          elseif ( end($cat['structuredDataNodes']) == $type ) :
            echo "</ul>";
          endif;
          if ( !empty($type['structuredDataNodes']) ):
            foreach ( $type['structuredDataNodes'] as $k => $fee) :
              switch( $k ) {
                case (3) :
                  $lead = "Missouri/Kansas Rate: ";
                  break;
                case (4) :
                  $lead = "Heatland Rate: ";
                  break;
                case (5) :
                  $lead = "Nonresident Rate: ";
                  break;
                default:
                  $lead = "";
              }
              if ( $k === 0 ) :
                echo "<h4>${fee['text']}</h4><ul>";
              elseif ( $k !== 1) :
                echo !empty($fee['text']) ? "<li>${lead}${fee['text']}</li>" : '';
              endif;
              if ( $fee == end($type['structuredDataNodes'])) :
                echo "</ul>";
              endif;
            endforeach;
          endif;
        endforeach;
      endif;
    endforeach;
  endforeach;


  // highlight_string(var_export($result, true));
