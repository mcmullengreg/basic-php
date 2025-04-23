<?php
class Career {

  private $_lcId;
  private $_lcKey;
  private $_headers;
  private $_oauth;
  public function __construct() {
    $this->_lcId = getenv('LIGHTCAST_ID');
    $this->_lcKey = getenv('LIGHTCAST_SECRET');
    $this->_oauth = $this->getOAuth();
  }

  private function getOAuth() {
    $headers = [
      CURLOPT_URL => "https://auth.emsicloud.com/connect/token",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "client_id={$this->_lcId}&client_secret={$this->_lcKey}&grant_type=client_credentials&scope=careers",
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded"
      ],
    ];
    $ch = curl_init();
    curl_setopt_array($ch, $headers);
    ## If oauth doesn't exist, or is expired.
    $server_output = curl_exec($ch);
    curl_close($ch);

    return json_decode($server_output, true);
  }

  public function search($query, $searchFields) {
    $oauth = $this->_oauth['access_token'];
    $searchFields = implode("%2C", $searchFields);
    $headers = [
      CURLOPT_URL => "https://cc.emsiservices.com/careers/us/search?query=${query}&fields=${searchFields}",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => [
        "Authorization: Bearer {$oauth}"
      ]
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $headers);
    $response = curl_exec($ch);
    $err = curl_error($ch);

    if ( $err ) {
      return $err;
    }

    return json_decode($response, true);
  }

  private function growthData($data) {
    $data = json_decode($data, true);
    $formatter = new NumberFormatter('en_US', NumberFormatter::PERCENT);
    $response = null;
    if ( !empty($data['data']) ){
      $data = $data['data']['attributes']['employment'];
      $start = $data[0]['number'];
      $end = end($data)['number'];
      $n = sizeOf($data);
      // Compound Annual Growth Rate (CAGR)
      // (final / start)^1/[number of years] - 1
      $growth = pow(($end/$start), 1/$n) - 1;
      if ( $growth >= 0 ) {
        $response = $formatter->format($growth);
      }
    }

    return $response;
  }

  public function getDataById($onetId) {
    $onetId = trim($onetId);
    $oauth = $this->_oauth['access_token'];
    $baseURL = "https://cc.emsiservices.com";
    $fields = array(
      'employment',
      'title',
      'median-earnings',
      // 'annual-earnings',
      // 'annual-openings',
      // 'employment-current',
      // 'abilities',
      // 'age',
      // 'categories',
      // 'core-tasks',
      // 'description',
      // 'education-attainment-levels',
      // 'hourly-earnings',
      // 'humanized-title',
      // 'knowledge',
      // 'lay-titles',
      // 'mocs',
      // 'national-lq',
      // 'onet-id',
      // 'pathways',
      // 'percent-female',
      // 'percent-male',
      // 'similar-by-capabilities-interests',
      // 'similar-by-skills-experience',
      // 'skills',
      // 'soc-id',
      // 'soc-title',
      // 'title-slug',
      // 'typical-ed-level'
    );
    $fields = implode(',', $fields);

    $headers = [
      CURLOPT_URL => "$baseURL/careers/us/nation/0/${onetId}?fields=${fields}",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => [
        "Authorization: Bearer {$oauth}"
      ]
    ];
    $ch = curl_init();
    curl_setopt_array($ch, $headers);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    var_dump($response);
    if ( $err ) {
      return $err;
    }

    $title = $this->getTitle($response);
    $income = $this->medianIncome($response);
    $growth = $this->growthData($response);

    $response = array(
      "title" => $title,
      "income" => $income,
      "growth" => $growth
    );

    return $response;
  }

  private function medianIncome($data) {
    $data = json_decode($data, true);
    $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
    $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
    $response = null;
    if ( !empty($data['data']) ) {
      $data = $data['data']['attributes']['median-earnings'];
      $income = $formatter->format($data);
      $response = $income;
    }
    return $response;
  }

  private function getTitle($data) {
    $response = null;
    $data = json_decode($data, true);
    if ( !empty($data['data']) ){
      $data = $data['data']['attributes']['title'];
      $response = $data;
    }
    return $response;
  }
}
