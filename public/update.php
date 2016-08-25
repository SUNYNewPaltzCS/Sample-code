<?php
require_once 'google-api-php-client/src/Google/autoload.php';

session_start();

global $selectOption, $client, $ft_service;

function calcDistance($lat1,$lon1,$lat2,$lon2) {
	$lat1 = deg2rad($lat1);
	$lat2 = deg2rad($lat2);
	$lon1 = deg2rad($lon1);
	$lon2 = deg2rad($lon2);
	$dlon = $lon2 - $lon1;
	$dlat = $lat2 - $lat1;
	$a = pow((sin($dlat/2)),2) + cos($lat1) * cos($lat2) * pow((sin($dlon/2)),2); 
	$c = 2 * atan2(sqrt($a),sqrt(1-$a));
	$d = 3961 * $c;
	return $d;
}
//get identifiers from URL args
$id_key = $_GET["idk"];
$id_value = $_GET["idv"];
$email = $_GET["email"];
$table = $_GET["table"];

//------------------------------------------------------
//instantiate new google client, give it access to fusiontables
//set access type to offline and force it to prompt user for approval
//every time
//------------------------------------------------------

$client = new Google_Client();
$client->setAuthConfigFile('/var/www/client_secrets.json');
$client->addScope(Google_Service_Fusiontables::FUSIONTABLES);
$client->setAccessType("offline");
$client->setApprovalPrompt('force');

//load list of refresh tokens
$r_file_str = file_get_contents("/var/www/refresh.tkn");
$r_file_lines = explode("\n",$r_file_str);
$rkey = "";

//------------------------------------------------------
//parse and search list of refresh tokens for the token that matches email
//------------------------------------------------------

foreach( $r_file_lines as $line ) {
  $line_split = explode(",",$line);
  if($email == $line_split[0]){
    $rkey = $line_split[1];
    break;
  }
}

//use refresh token to get new access token
$client->refreshToken($rkey);

$newtoken = $client->getAccessToken();

//now that oauth is done, connect to fusiontables  
$ft_service = new Google_Service_Fusiontables($client);

//------------------------------------------------------
//get list of fusion tables that 'email' has access to, 
//find table id that corresponds to 'table'
//------------------------------------------------------

$files_list = $ft_service->query->sql("show tables");
foreach( $files_list['rows'] as $tableInfo) {
        $searchRes = array_search($table,$tableInfo);
	if($searchRes !== FALSE) {
		$selectOption = $tableInfo[0];
		break;
	}
}

//read json string posted to this php page
global $jsonFromApp;
  $jsonFromApp = json_decode(file_get_contents('php://input'),true);

//------------------------------------------------------
//writes submitted information out to a file, for debug
//------------------------------------------------------

  $fName = fopen("test.txt", 'w');
  fwrite($fName, file_get_contents('php://input'));
  $fName = fopen("test.txt", 'a');
  fwrite($fName, "\n" . $email);
  fclose($fName);
//end debug

//initialize KML string
  $strKMLBegin="<LineString><tessellate>1</tessellate><coordinates>";
  $strKMLEnd="</coordinates></LineString>";
  $strKML;

//------------------------------------------------------
//parse each line sent from app, if the line is a tracking line,
//add it to the KML string
//------------------------------------------------------
  
  foreach($jsonFromApp as $value)
  { 
    $run = $value["run"];
		$date = substr($value["date"], 0, 10);
    if(isset($value["longTrack"])) {

			$currentRun =& $strKML[$date][$run];
			if(isset($currentRun["lastLat"])) {
				$currentRun["distance"] += 
					calcDistance($currentRun["lastLat"],$currentRun["lastLon"],
							$value["latTrack"],$value["longTrack"]);
			}
			$currentRun["lastLat"] = $value["latTrack"];
			$currentRun["lastLon"] = $value["longTrack"];
      $strKML[$date][$run]["kml"] .= $value["longTrack"].",".$value["latTrack"]." ";
      $strKML[$date][$run]["run"] = $run;
      if(!isset($strKML[$date][$run]["start"])){
          $strKML[$date][$run]["start"] = $value["date"];
      }
      $gps_exists = true;
      $strKML[$date][$run]["end"] = $value["date"];
      $strKML[$date][$run]["value"] = $value;
    } 
  }
 
  $fName = fopen("query.txt", 'w');
  fwrite($fName, print_r($strKML, true) . "\n");
  fclose($fName);

//------------------------------------------------------
// Old code that supported separate lines for kml and data
//------------------------------------------------------

 /*  if(isset($gps_exists) && $gps_exists) { 
    foreach($strKML as $kml) {
      $kml["kml"] = $strKMLBegin . $kml["kml"] . $strKMLEnd;
      $query = "INSERT INTO \"$selectOption\" (";
      $query .= "'start', 'end', 'geometry', '";
      $query .= implode("', '", array_keys($kml["value"]));
      $query .= "') VALUES ('";
      $query .= $kml["start"] . "', '" . $kml["end"] . "', '" . $kml["kml"] . "', '";
      $query .= implode("', '", $kml["value"]);
      $query .=  "')";

      //$ft_service->query->sql($query);
    }
  }
*/

//------------------------------------------------------
//format the query for submission to the Fusion Table
//------------------------------------------------------

  foreach($jsonFromApp as $value) {
    if(!isset($value["longTrack"])) {
			$date = substr($value["date"], 0, 10);
      if(array_key_exists($date, $strKML) && 
					array_key_exists($value["run"], $strKML[$date])) {
				$kmlData = $strKML[$date][$value["run"]];
        $value["geometry"] = $strKMLBegin . $kmlData["kml"] . $strKMLEnd;
				$value["distance"] = $kmlData["distance"];
      	unset($value["date"]);
      	$value["start"] = $kmlData["start"];
      	$value["end"] = $kmlData["end"];
      }
      //$value["start"] = $value["date"];
      unset($value["date"]);
      $query = "INSERT INTO \"$selectOption\" ('";
      $query .= implode("', '", array_keys($value));
      $query .= "') VALUES ('";
      $query .= implode("', '", $value);
      $query .= "')";

      $ft_service->query->sql($query);
    }
  }
?>
