<?php

$tuCurl = curl_init();
curl_setopt($tuCurl, CURLOPT_URL, "http://127.0.0.1:7700/keys");
curl_setopt($tuCurl, CURLOPT_HTTPHEADER, array("Authorization: Bearer testing"));

$tuData = curl_exec($tuCurl);

if(!curl_errno($tuCurl)){

  $info = curl_getinfo($tuCurl);

  echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];

} else {

  echo 'Curl error: ' . curl_error($tuCurl);

}



curl_close($tuCurl);

echo $tuData;

// require_once __DIR__ . '/vendor/autoload.php';
// use Meilisearch\Client;
//
// $client = new Client('http://localhost:7700', 'dt88BXFnTJVxOK-4YaavxwiwIr71sAa-wZ2HFCB_Ypw');
// // Authorization
// // $client->getKeys()
// echo $client->health();
//
// // Indexes
// $index = $client->index('movies');
// $hits = $index->search('wondre woman')->getHits();
//
// highlight_string(var_export($hits, true));
