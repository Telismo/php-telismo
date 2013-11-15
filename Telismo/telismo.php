<?php
abstract class Telismo
{
  public static $apiKey;
  public static $apiBase = 'https://telismo.com';
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

  public static function listTasks($params) {
    $request =  array(
                  'from'=>$params['from'],
                  'to'=>$params['to']
                );

    return self::curlUrl("list", $request);
  }

  public static function accountBalance() {
    $request =  array();

    return self::curlUrl("balance", $request);
  }

  public static function fetchTask($taskId) {
    $request =  array();

    return self::curlUrl("fetch/$taskId", $request);
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

  public static function quote($params) {
    $request =  array(
                  'number'=>$params['number'],
                  'name'=>$params['number'],
                  'instruction'=>array(
                    'sample'=>$params['sample'],
                    'text'=>$params['description']
                  ),
                  'fields'=>$params['fields'],
                  'type'=>$params['type']
                );

    return self::curlUrl("quote", $request);
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
    curl_setopt( $ch, CURLOPT_CAINFO,
                  dirname(__FILE__) . '/ca-certificates.crt');

    if (!Telismo::$verifySslCerts) {
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
    }else{
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true);
    }

    $response = curl_exec( $ch );

    if ($response === false) {
      $errno = curl_errno($ch);
      $message = curl_error($ch);
      curl_close($ch);
      self::handleCurlError($errno, $message);
    }

    return json_decode($response);
  }

  public static function handleCurlError($errno, $message)
  {
    $apiBase = Telismo::$apiBase;
    switch ($errno) {
    case CURLE_COULDNT_CONNECT:
      $msg = "Could not connect to Telismo ($apiBase).  Please check your internet connection and try again.  If this problem persists, you should check Stripe's service status at https://twitter.com/Telismo, or let us know at support@telismo.com.";
      break;
    case CURLE_COULDNT_RESOLVE_HOST:
      $msg = "Couldn't resolve host. Please check your DNS settings to ensure that they are ok.";
      break;
    case CURLE_OPERATION_TIMEOUTED:
      $msg = "Could not connect to Telismo ($apiBase).  Please check your internet connection and try again.  If this problem persists, you should check Stripe's service status at https://twitter.com/Telismo, or let us know at support@telismo.com.";
      break;
    case CURLE_SSL_CACERT:
      $msg = "Peer certificate cannot be authenticated with known CA certificates";
    case CURLE_SSL_PEER_CERTIFICATE:
      $msg = "Could not verify Telismo's SSL certificate.  Please make sure that your network is not intercepting certificates.  (Try going to $apiBase in your browser.)  If this problem persists, let us know at support@telismo.com.";
      break;
    default:
      $msg = "Unexpected error communicating with Telismo.  If this problem persists, let us know at telismo@stripe.com.";
    }

    $msg .= "\n\n(Network error [errno $errno]: $message)";
    throw new Exception($msg);
  }
}
?>