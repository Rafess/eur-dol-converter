<?php

$objectify_xml = simplexml_load_file("http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml");

function getCurrency($currency_code,$xml_path) {     
  $xml_path->registerXPathNamespace("ecb", "http://www.ecb.int/vocabulary/2002-08-01/eurofxref");
  $currency_list = $xml_path->xpath("//ecb:Cube[@currency='".$currency_code."']/@rate");
  $rate = (string) $currency_list[0]['rate'];
  return $rate;
} 
function convertUSDtoAny($currency_code, $xml_path) {
  $euro_to_dolar = getCurrency('USD', $xml_path);
  $euro_to_any = getCurrency($currency_code,$xml_path);
  $money_converted = number_format(($euro_to_any / $euro_to_dolar),2);
  return $money_converted;
}
$date = date('Y-m-d');

function writeCSV($archive) {
  $headers = ['Currency Code', 'Rate'];
  fputcsv($archive, $headers, ';');
}

function fillArchive($archive, $data) {
   foreach($data as $line) {
    fputcsv($archive, $line, ';');
  }
}

$archive = fopen("usd_currecny_rates_${date}.csv", 'w');

function fillData($archive, $objectify_xml) {
  $currency_codes_list = ['JPY', 'BGN', 'CZK', 'DKK', 'GBP', 'HUF', 
  'PLN', 'RON', 'SEK', 'CHF', 'ISK', 'NOK', 'HRK', 'RUB', 'TRY', 'AUD', 'BRL', 'CAD', 'CNY', 
  'HKD', 'IDR', 'ILS', 'INR', 'KRW', 'MXN', 'MYR', 'NZD', 'PHP', 'SGD', 'THB', 'ZAR'];

  $currency_list_length = count($currency_codes_list);
  writeCSV($archive);
  for($i=0;$i<$currency_list_length;$i++) {
    foreach($currency_codes_list as $code) {
      $data[$i]['code'] = $code;
      $data[$i]['rate'] = convertUSDtoAny($code,$objectify_xml) . " $code";
      fillArchive($archive, $data);
    }  
    fclose($archive);
  }
}

$data = fillData($archive, $objectify_xml);

    
    