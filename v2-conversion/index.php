<?php include('./inc/base.php'); ?>
<?php $cms = new Cascade(); ?>
<?php $pageIds = $cms->getPageIds("https://www2.umkc.edu/law-test/json-list.json")['message']; ?>
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
            foreach ( $pageIds as $key => $item ) {
              $content = file_get_contents($item['link']);
              echo "$content";
            }
           ?>
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

