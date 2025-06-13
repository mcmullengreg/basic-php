<?php
// require_once 'config.php';
$env = file_get_contents(dirname(__DIR__, 1) . "/.env");
$lines = explode("\n",$env);
foreach($lines as $line){
  preg_match("/([^#]+)\=(.*)/",$line,$matches);
  if(isset($matches[2])){
    putenv(trim($line));
  }
}

function autoload($classname){
  $filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'class.'.strtolower($classname).'.php';
  if ( is_readable($filename)) {
    require $filename;
  }
}

spl_autoload_register('autoload');
