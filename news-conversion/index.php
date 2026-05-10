<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
?>
<?php include('./inc/base.php'); ?>
<?php
  $cms = new Cascade();
  $newsSite = "DEV-News-wwwnews";
?>
<?php ## highlight_string(var_export($pageIds, true)); ?>
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
    <!-- As a heading -->
    <nav class="navbar border-bottom border-body mb-4">
      <div class="container">
        <span class="navbar-brand mb-0 h1">V1 to V2 Conversion Tool</span>
      </div>
    </nav>
    <?php
      if ( empty($_POST['url']) || !filter_var($_POST['url'], FILTER_VALIDATE_URL) ) : ?>
        <div class="container">
          <div class="row">
            <div class="col-6">
              <form class="form" method="POST">
                <label for="url" class="form-label">JSON Url</label>
                <input type="text" id="url" name="url" class="form-control" aria-describedby="urlHelpText" />
                <div id="urlHelpText" class="form-text">
                  Use the JSON url that is setup on the site (ideally, should be a www2 url).
                </div>
                <button class="btn btn-primary" type="submit">Submit</button>
              </form>
            </div>
          </div>
        </div>
    <?php
      else :
        $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
        $pageIds = $cms->getPageIds($url)['message'];
        if ( empty($pageIds) ) {
          die("Nothing to be found...try a new URL");
        }
    ?>
    <div class="container">
      <div class="row">
        <div class="col-6">
          <h2>Articles: <?= sizeof($pageIds); ?></h2>
        <?php
          foreach ( $pageIds as $key => $item ) {
            if ( $key !== 0 ) { echo "<hr />"; }
            echo "<p><strong>${item['id']}</strong></p>";
            preg_match('/^site:\/\/AA - Insider - wwwinsider\/posts\/(\d{4})\/(\d{2})\/(.*)$/', $item['url'], $matches);
            $year = $matches[1];
            $month = $matches[2];
            $fullFolderLookUp = "/posts/{$year}/{$month}";
            // Sanity check to make sure the tool is working based on the path.
              // $checkFolder = $cms->read("{$newsSite}/posts", 'folder');
              // highlight_string(var_export($checkFolder['asset']['folder'], true));
            // End sanity Check
            // Check if Year folder exists
            $checkFolder = $cms->read("{$newsSite}/posts/{$year}", 'folder');
            if ( !$checkFolder['success'] ){
              $yearFolder = $cms->createFolder($newsSite, $year, "/posts");
            }
            // Check if Month folder exists, create it if not.
            $checkFolder = $cms->read("{$newsSite}/posts/{$year}/{$month}", 'folder');
            if ( !$checkFolder['success'] ){
              $monthFolder = $cms->createFolder($newsSite, $month, "/posts/{$year}");
            }
            // Snag the old content, for future use.
            $oldContent = $cms->read($item['id'], 'page');
            $oldImageId = $oldContent['asset']['page']['structuredData']['structuredDataNodes'][1]['structuredDataNodes'][2]['fileId'];
            // Content needs to include:
              // Intro copy
              // Content
            $introCopy = $oldContent['asset']['page']['structuredData']['structuredDataNodes'][2]['text'];
            $articleContent = $oldContent['asset']['page']['structuredData']['structuredDataNodes'][3]['text'];
            $oldPageContent = $introCopy . $articleContent;

            // Old Dynamic Field Values
            $oldDynamicFields = $oldContent['asset']['page']['metadata']['dynamicFields'];
            highlight_string(var_export($oldDynamicFields, true));
            echo '<hr />';
            // Update the ContentType and clear the Page Configuration
            $updateCT = $cms->editContentType($item['id']);
            if ( $updateCT['success'] ) {
              echo("Failed to update ContentType on asset {$item['id']}");
            }
            // Pass the old content into the Edit Content Function
            $newStructure = $cms->read($item['id'], 'page');
            // highlight_string(var_export($newStructure['asset']['page']['metadata'], true));
            echo '<hr />';
            $newContent = $newStructure['asset']['page']['structuredData']['structuredDataNodes'];
            // Controls Group Updates
            $newContent[0]['structuredDataNodes'][0]['text'] = 'internal'; // audience
            $newContent[0]['structuredDataNodes'][1]['text'] = 'standard'; // mode
            // Media Updates
            $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][0]['fieldId'] = $oldImageId; // Image Asset
            $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][1]['fieldId'] = ''; // Alt
            $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][2]['fieldId'] = ''; // Caption
            $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][3]['fieldId'] = ''; // Credit
            $newContent[1]['structuredDataNodes'][0]['structuredDataNodes'][4]['fieldId'] = 'default'; // Style
            // Basic Content
            $newContent[2]['structuredDataNodes'][0]['text'] = $oldPageContent;
            // Metadata Updates, dynamic fields ONLY needed.
            // GM 5.11.26 - Map the new and old fields that need to be placed. Get them written up so we can convert to
            // the new Metadata Set for Posts.

            $asset = [
              'asset' => [
                'page' => [
                  'dynamicFields' => array(
                    // 0 - Category
                    [],
                    // 1 - Unit
                    [],
                    // 2 - Featured
                    [],
                    // 3 - NoIndex
                    []
                  ),
                ]
              ]
            ];

            // Read the content, save it into a variable

            // Edit the Content Type

            // Edit the Content

            // Move the file

            die();
//             $options = array('http' => array('user_agent' => "MCOM Web App: Hi Donald"));
//             $context = stream_context_create($options);
//             $content = file_get_contents($item['link'], false, $context);
//             $content = json_decode($content, true);
//             if ( !empty($content) ) {
//               // Edit the Content Type
//               echo "<p>...editing Content Type...<p>";
//               $status = $cms->editContentType($item['assetId']);
//
//               if ( $status['success'] == false ){
//                 var_dump($status);
//                 echo "<p><strong>Error: </strong>" . $status['message'] . "</p>";
//               } else {
//                 echo "<p>Successfully changed Content Type</p>";
//               }
//
//               // Edit the Content Area
//               echo "<p>...editing Content Area(s)...</p>";
//               $status = $cms->editContent($item['assetId'], $content['content']);
//               if ( $status['success'] == false ){
//                 var_dump($status);
//                 echo "<p><strong>Error: </strong>" . $status['message'] . "</p>";
//               } else {
//                 echo "<p>Successfully updated content</p>";
//               }
            // } else {
              // echo "<p><strong>This page needs edited further before editing is allowed.</strong><p>";
            // }
          }
        ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php /*
    <div class="container-fluid">
      <div class="row">
        <div class="col-6">
          <h2>Old Data Definition</h2>
          <?php // ID of page with Factoids: e9476305ac1e04cd3d3a61c2fa0c1f36
            // ID of page with Regular Text Block: 86731cc4ac1e00760bf9647e0b149c10
            // LOTS of accordions: 53926312ac1e00760cb997e4c4c60d23
            // Some accordions: 1bf17912ac1e00767922334d75c556c8
          ?>
          <?php
            // Read the accordion block
            $block = $cms->read('407d7bf8ac1e04cd3c64bafc8641037f', "block");
            // highlight_string(var_export($block, true));
           ?>
        </div>

        <div class="col-6">
          <h2>Old Content JSON</h2>
          <?php

          ?>
        </div>
        <div class="col-6">
          <h2>New Data Definition</h2>
          <!--e9473197ac1e04cd3d3a61c25cd6cc9f-->
          <!-- V2 Page: eb8a0325ac1e04cd3dfe9d7048810882 -->
          <?php $v2Page =  $cms->read('e9473197ac1e04cd3d3a61c25cd6cc9f', 'page');
            // highlight_string(var_export($v2Page, true));
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
    <?php */ ?>
  </body>
</html>

