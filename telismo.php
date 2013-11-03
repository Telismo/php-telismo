<?php

if (!function_exists('curl_init')) {
  throw new Exception('Telismo needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Telismo needs the JSON PHP extension.');
}


require(dirname(__FILE__) . '/Telismo/Telismo.php');


?>