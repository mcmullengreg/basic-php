<?php include('./inc/base.php'); ?>
<?php $cms = new Cascade(); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>V2 Conversion Test</title>
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  </head>
  <body>
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <h2>Old Data Definition</h2>
          <?php // ID of page with Factoids: e9476305ac1e04cd3d3a61c2fa0c1f36
            // ID of page with Regular Text Block: 86731cc4ac1e00760bf9647e0b149c10
            // LOTS of accordions: 53926312ac1e00760cb997e4c4c60d23
            // Some accordions: 1bf17912ac1e00767922334d75c556c8
          ?>
          <?php
            $v1Page =  $cms->read('53926312ac1e00760cb997e4c4c60d23', 'page');
            $v1Page = $v1Page['asset']['page'];
            $mainContentGroup = $v1Page['structuredData']['structuredDataNodes'][1]; // Gets the main content group (of 1), skipping the hero
            $mainContentSDN = $mainContentGroup['structuredDataNodes']; // Gets the structured Data Nodes
            $multiColSDN = $v1Page['structuredData']['structuredDataNodes'][2];
            $accordionSDN = $v1Page['structuredData']['structuredDataNodes'][3];
            $includeFactoids = $mainContentSDN[2]['text']; // Grabs if the page has factoids, for the content grab [value of Yes or No ]
            $isResourceList = !empty($mainContentSDN[3]['text']) ? $mainContentSDN[3]['text'] : false;
            $html = "";
            if ( $includeFactoids == "Yes" ) {
              // Content group is multiple. In Group 4
              $factoids = $mainContentSDN[4]['structuredDataNodes'];
              foreach ( $factoids as $factoid ) {
                if ( !empty($factoid['text']) ) :
                  $html .= $factoid['text'];
                endif;
              }
            } elseif ( $isResourceList == "Yes" ){
            } else { // No Factoid, Not a resource list
              $html .= $mainContentSDN[5]['text'];
            }

            // Accordion loop (adds HTML to the main page)
            $accordionSDN = $v1Page['structuredData']['structuredDataNodes'][3];
            highlight_string(var_export($accordionSDN, true));
            foreach ( $accordionSDN as $key => $acc ) {
              if ( $key == 1 ) { // Group Text
              }
              if ( $key == 1 ) { // Group Text
              }
              if ( $key == 3 ) { // Group Text
              }
              // highlight_string(var_export($acc, true));
              // $groupText = !empty($acc[0]['text']) ? $acc[0]['text'] : '';

              echo "<br /><br />";
              // $itemSDN = $acc[3]['structuredDataNodes'];
              // $title = !empty($acc[0]['text']) ? `<h2>$acc[0]['text']</h2>` : '';
              // $intro = !empty($acc[1]['text']) ? $acc[1]['text'] : '';
              // foreach ( $acc[3]['structuredDataNodes'] as $key => $item){
              //   $title = !empty($item['']) ? $item[''] : '';
              // }
              // $html .= "$title";
            }
            // Needs:
              // Resource List
              // Text without Factoids
              // General Text Block


            $values = array(
              "contentTypeId" => $v1Page['contentTypeId'],
              "mainContent" => $html,
            );
          ?>

          <?php ## highlight_string(var_export($v1Page, true)); ?>
          <?php ## highlight_string(var_export($values, true)); ?>
        </div>

        <div class="col-6">
          <h2>New Data Definition</h2>
          <?php $v2Page =  $cms->read('eb8a0325ac1e04cd3dfe9d7048810882', 'page');
            $v2Page = $v2Page['asset']['page'];
            $values = array(
              "contentTypeId" => $v2Page['contentTypeId'],
              "mainContent" => $v2Page['structuredData']['structuredDataNodes'][1]['structuredDataNodes'][2]['text'],
              "hasToc" => $v2Page['structuredData']['structuredDataNodes'][1]['structuredDataNodes'][1]['text']
            );
          ?>
          <?php ## highlight_string(var_export($v2Page['structuredData'], true)); ?>
          <?php ## highlight_string(var_export($values, true)); ?>
        </div>
      </div>
    </div>
  </body>
</html>

