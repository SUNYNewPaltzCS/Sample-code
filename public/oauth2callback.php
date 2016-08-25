<?php
require_once 'google-api-php-client/autoload.php';
session_start();
$client = new Google_Client();
$client->setAuthConfigFile('/var/www/client_secrets.json');
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/ft_test/oauth2callback.php');
$client->addScope(Google_Service_Fusiontables::FUSIONTABLES);
$client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
$client->setAccessType("offline");
$client->setApprovalPrompt('force');
//$client->setScopes('https://www.googleapis.com/auth/userinfo.email');
if (! isset($_GET['code'])) {
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
  $test = $client->authenticate($_GET['code']);
  $refreshToken = json_decode($test,true)['refresh_token'];
  $r_file = fopen("/var/www/refresh.tkn","a");
  $email_svc = new Google_Service_Oauth2($client);
  $email = $email_svc->userinfo->get()->email;
  $_SESSION['email'] = $email; 
  $r_file_str = file_get_contents("/var/www/refresh.tkn");
  $r_file_lines = explode("\n",$r_file_str);
  $needsEntry = true;
  foreach( $r_file_lines as $line ) {
    $line_split = explode(",",$line);
    if($email == $line_split[0]){
      $needsEntry = false;
    }
  }
  if($needsEntry) { 
    fwrite($r_file,$email . "," . $refreshToken . "\n");
  } 
  $_SESSION['access_token'] = $client->getAccessToken();
  fclose($r_file);
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/ft_test/';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
