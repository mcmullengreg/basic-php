<!doctype html>
<html class="no-js" lang="">

<head>
  <meta charset="utf-8">
  <title>Campus Groups to LiveWhale</title>
  <meta name="robots" content="noindex, nofollow">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <meta name="theme-color" content="#fafafa">
  <style>
    @import"https://fonts.googleapis.com/css?family=Roboto:300,400,700&display=swap";
    html {
      box-sizing: border-box;
    }
    *, *:before, *:after {
      box-sizing: inherit;
    }
    @media(prefers-color-scheme: dark) {
      :root{
        --body-bg: #1b1b1b;
      }
    }
    :root{
      --global-padding: 1.875rem;
      --content-padding: calc(var(--global-padding) * 1.5);
      --logo-ratio: 2.29;
      --logo-height: 4.6875rem;
      --logo-color: var(--white);
      --white: #fff;--black: #000;
      --font-color: #4d4d4d;
      --umkc-gray: #f6f6f6;
      --umkc-blue: #06c;
      --umkc-dark-blue: #04487f;
      --umkc-light-blue: #3f95db;
      --umkc-yellow: #ffd52f;
      --roboto: "Roboto", sans-serif;
      --condense: "Roboto Condensed", sans-serif;
      --slab: "Roboto Slab", sans-serif
    }
    body {
      font-size: 16px;
      line-height: 1.4;
      background-color: var(--body-bg, var(--umkc-gray));
      font-family: var(--roboto);
      align-items: center;
      display: flex;
      font-size: 1rem;
      justify-content: center;
      height: 100vh;
      margin: 0;
      padding:0;
      overflow: none;
    }
    .app {
      background: var(--umkc-dark-blue);
      padding: var(--content-padding);
      color: var(--white);
    }
    form {
      margin: 0; padding: 0;
      max-width: 100%;
    }
    fieldset {
      border: none;
      margin: 0; padding: 0;
    }

    legend {
      font-size: 2rem;
    }
    label, input {
      display: block;
      margin-bottom: 0.5rem;
    }
    input[type="text"] {
      padding: 1rem 0.75rem;
      width: 100%;
      font-size: 1rem;
    }
    .button{
      padding: 1rem;
      display: inline-block;
      background: var(--umkc-yellow);
      border: none;
      border-radius: 0.5rem;
      font-size: 1.25rem;
      transition: background-color 250ms ease-in-out, color 250ms ease-in-out;
    }
    .button:hover, .button:focus, .button:active {
      background-color: var(--umkc-blue);
      color: white;
    }
    code {
      background: rgba(0,0,0, 0.75);
      padding: 1rem;
      margin-top: 0.5rem;
      display: block;
    }
  </style>
</head>

<body>
  <div class="app">
    <?php if ( !empty($_POST['link']) ) : ?>
      <?php
        preg_match('/type_id=(.*?)&/', $_POST['link'], $group_id);
       ?>
       <?php if ( empty($group_id[1]) ) : ?>
        <p>There was an error finding the group ID. Please check the RooGroup URL again.</p>
       <?php else : ?>
        <p>Copy and paste the link below into LiveWhale's calendar system:<br /></p>
        <code><?= $_SERVER['HTTP_ORIGIN'];?>/cglw/cg.php?group_id=<?= $group_id[1]; ?></code>
      <?php endif; ?>
    <?php else : ?>
    <form action="" method="post">
      <fieldset>
        <legend>RooGroups to LiveWhale Link Generator</legend>
        <label for="link">Campus Group URL:</label>
        <input type="text" name="link" />
        <button class="button" type="submit">Submit</button>
      </fieldset>
    </form>
    <?php endif; ?>
  </div>

</body>

</html>
