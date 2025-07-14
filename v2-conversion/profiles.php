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
    <!-- As a heading -->
    <nav class="navbar border-bottom border-body mb-4">
      <div class="container">
        <span class="navbar-brand mb-0 h1">V1 to V2 Profile Conversion</span>
      </div>
    </nav>
    <div class="container">
      <div class="row">
        <div class="col-6">
          <?php if ( empty($_POST['fid']) ) : ?>
            <form class="form" method="POST">
              <label for="fid" class="form-label">Folder ID</label>
              <input type="text" id="fid" name="fid" class="form-control" aria-describedby="urlHelpText" />
              <div id="urlHelpText" class="form-text">
                Snag the ID of the profiles folder.
              </div>
              <button class="btn btn-primary" type="submit">Submit</button>
            </form>
          <?php else : ?>
            <?php $cms->editProfiles($_POST['fid'], 'folder'); ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </body>
</html>

