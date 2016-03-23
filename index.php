<?php
require_once 'google-api-php-client/src/Google/autoload.php';

session_start();
$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->addScope(Google_Service_Fusiontables::FUSIONTABLES);

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $drive_service = new Google_Service_Fusiontables($client);
  $files_list = $drive_service->query->sql("show tables");
  echo json_encode(var_dump($files_list['rows']));
  $table = $files_list['rows'][0][0];
  echo "<br><br>";
  echo $table;
  $ins = $drive_service->query->sql("INSERT INTO \"1-gSXaHgsT3bQsQIpQFZRhNsoh-MEkuYHFOKa4bZs\" ('IMG URL') VALUES ('test')");
} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/ft_test/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}