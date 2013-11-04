<?php
abstract class Telismo
{
  public static $apiKey;
  public static $apiBase = 'http://telismo.com';
  public static $apiVersion = null;
  public static $verifySslCerts = true;
  
  const VERSION = '1.0.0';

  public static function getApiKey()
  {
    return self::$apiKey;
  }

  public static function setApiKey($apiKey)
  {
    self::$apiKey = $apiKey;
  }

  public static function getApiVersion()
  {
    return self::$apiVersion;
  }

  public static function setApiVersion($apiVersion)
  {
    self::$apiVersion = $apiVersion;
  }

  public static function getVerifySslCerts() {
    return self::$verifySslCerts;
  }

  public static function setVerifySslCerts($verify) {
    self::$verifySslCerts = $verify;
  }

  public static function processCallback() {
    $output = file_get_contents('php://input');
    return json_decode($output);
  }

  public static function createTask($params) {
    $request =  array(
                  'number'=>$params['number'],
                  'name'=>$params['number'],
                  'instruction'=>array(
                    'sample'=>$params['sample'],
                    'text'=>$params['description']
                  ),
                  'fields'=>$params['fields'],
                  'type'=>$params['type'],
                  'callback'=>$params['callback']
                );

    return self::curlUrl("create", $request);
  }

  public static function cancelTask($ids) {
    $request =  array(
                  'id'=>$ids
                );

    return self::curlUrl("cancel", $request);
  }

  private static function curlUrl($method, $params) {
    $url = self::$apiBase . "/api/v1/$method";

    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt( $ch, CURLOPT_USERPWD, self::$apiKey . ":");  
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec( $ch );

    return json_decode($response);
  }
}
?>