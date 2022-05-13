<?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  include_once('../_config.php');  
  // Read the spreadsheet....
  
  // Insert it into Cascade...
  $url  = "https://cascade.xavier.edu/api/v1/";
  
  $count = 1;
  $file = fopen("alumni-directory.csv", "r");
  
  // Build out the asset based on the CSV
  while (($row = fgetcsv($file, 0, ",")) !== FALSE){
    if($count == 1){ $count++; continue; } // Skip row 1
/*
    These are the columns to match the data fields.
    $row[0] = bannerId
    $row[1] = First
    $row[2] = Last
    $row[3] = Email
    $row[4] = Cell Phone Number
    $row[5] = Class Year
    $row[6] = Business Name
    $row[7] = Business Website
    $row[8] = Street
    $row[9] = City
    $row[10] = ZIP
    $row[11] = Business Description
    $row[12] = Business Phone Number
    $row[13] = Minority Owned 
    $row[14] = Discount 
    $row[15] = Facebook 
    $row[16] = Twitter
    $row[17] = Instagram
    $row[18] = LinkedIn
    $row[19] = Category
    $row[20] = Category Other -- somtimes empty
    $row[21] = state
*/
    switch( $row[19] ){
      case "Independent Contractors/ Construction / Manufacturing ":
        $cat = "construction";
        break;
      case "Professional Services ":
        $cat = "professional";
        break;
      case "Real Estate and Wealth Management":
        $cat = "real-estate";
        break;
      case "Arts/Entertainment ":
        $cat = "arts";
        break;
      case "Education ":
        $cat = "education";
        break;
      case "Education":
        $cat = "education";
        break;
      case "Health & Wellness":
        $cat = "health";
        break;
      case "Retail":
        $cat = "retail";
        break;
      case "Travel":
        $cat = "travel";
        break;
      case "Restaurant/ Bar ":
        $cat = "other";
        $row[20] = "Restaurant/Bar";
        break;
      case "Other":
        $cat = "other";
        break;
    };

    // Build out the Cascade Asset!
    $asset = array(
      'page' => array(
        'shouldBeIndexed' => true,
        'name' => strtolower(preg_replace('/[^a-zA-Z0-9-]/','', $row[6])),
        'parentFolderPath' => "_businesses",
        'siteId' => '755082140afd01581f68ccf365bff78b',
        'contentTypeId' => '48c1673d0afd015877c3e0623998e9e5',
        'metadata' => array(
          'title' => preg_replace('/[^a-zA-Z0-9-_\.]/', ' ', $row[6])
        ),
        'structuredData' => array(
          'structuredDataNodes' => array(
            array(
              'type' => 'text',
              'identifier' => 'name',
              'text' => "$row[1] $row[2]",
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'email',
              'text' => $row[3],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'phone',
              'text' => $row[4],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'classYear',
              'text' => $row[5],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'businessName',
              'text' => preg_replace('/[^a-zA-Z0-9-_\.]/', ' ', $row[6]),
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'category', // Make sure to map this correctly!!!!!!
              'text' => $cat,
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'categoryOther',
              'text' => !empty($row[20]) ? $row[20] : '',
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'link',
              'text' => $row[7],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'street',
              'text' => $row[8],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'city',
              'text' => $row[9],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'zip',
              'text' => $row[10],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'description',
              'text' => $row[11],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'busPhone',
              'text' => $row[12],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'minorityOwned',
              'text' => !empty($row[13]) ? $row[13] : '',
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'discount',
              'text' => $row[14],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'facebook',
              'text' => $row[15],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'twitter',
              'text' => $row[16],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'instagram',
              'text' => $row[17],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'linkedIn',
              'text' => $row[18],
              'recycled' => false,
            ),
            array(
              'type' => 'text',
              'identifier' => 'state',
              'text' => $row[21],
              'recycled' => false,
            )
          )
        )
      )
    );
    $fields = array(
      'authentication' => array(
        'username' => $_SERVER['CASCADE_USER'],
        'password' => $_SERVER['CASCADE_PASS'] 
      ),
      "asset" => $asset
    );
    
    $fields = $fields;
    //highlight_string(var_export($fields, true));
    $ch = curl_init($url."create");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen(json_encode($fields))
    ));

    $result = json_decode(curl_exec($ch), true);
    highlight_string(var_export($result, true));
    
    curl_close($ch);

  }
  fclose($file);