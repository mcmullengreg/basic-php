<?php
  $xml = simplexml_load_file("umkcschoolofmedicine.WordPress.2024-08-13.xml", null, LIBXML_NOCDATA);

  foreach( $xml->channel->item as $item) :
    $date = $item->pubDate;
    $date = date_create($date);
    $yearFolder = date_format($date, 'Y');
    $monthFolder = date_format($date, 'M');
    $title = $item->title;
    $description = $item->description;
    $content = $item->xpath('content:encoded')[0];
    highlight_string(var_export($item, true));
  endforeach;
