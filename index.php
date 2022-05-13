<?php
  //Get a list of file paths using the glob function.
  $fileList = glob('*.php');
 
  //Loop through the array that glob returned.
  foreach($fileList as $filename){
     //Simply print them out onto the screen.
     echo $filename, '<br>'; 
  }