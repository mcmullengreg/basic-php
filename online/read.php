<?php
  $apiKey = "AIzaSyCZ6bEQsWc1F1qUkNCYVEmfmuKXSbG6JQc";
  $baseUrl = "https://dms-api-prod-gw-7yrc4yto.uc.gateway.dev";
  $requestHeaders = [
    "x-api-key: {$apiKey}"
  ];

  $endpoint = "/program-tuition";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $server_output = curl_exec($ch);

  curl_close($ch);

  highlight_string(var_export($server_output, true));
