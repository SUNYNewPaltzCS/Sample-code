<?php
require_once 'google-api-php-client/src/Google/autoload.php';

session_start();
global $selectOption, $client, $drive_service;

$client = new Google_Client();
$client->setAuthConfigFile('../private/client_secrets.json');
$client->addScope(Google_Service_Fusiontables::FUSIONTABLES);
$client->setAccessType("offline");
$client->setApprovalPrompt('force');
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $drive_service = new Google_Service_Fusiontables($client);
  $files_list = $drive_service->query->sql("show tables");
  $table = $files_list['rows'][0][0];
} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/ft_test/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
?> 

  <html>
  <head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="dstyle.css">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<title>BDSP App Builder</title>
    <script>
      var FormStuff = {
        
        init: function() {
          this.applyConditionalRequired();
          this.bindUIActions();
        },
        
        bindUIActions: function() {
          $("input[type='radio'], input[type='checkbox']").on("change", this.applyConditionalRequired);
        },
        
        applyConditionalRequired: function() {
        	
          $(".require-if-active").each(function() {
            var el = $(this);
            if ($(el.data("require-pair")).is(":checked")) {
              el.prop("required", true);
            }else {
              el.prop("required", false);
            }
          });
          
        }
        
      };
      
      FormStuff.init();
    </script>
  </head>
  
<body>
<div class="col-sm-4 col-sm-offset-4">
    <img src="res/BClogo.png" alt="Benjamin Center @ SUNY New Paltz" id="bcl">
			<form action="download.php" method="post" enctype="multipart/form-data">
			  
			<?php
				// GET TABLE ID
				$selectOption = $_POST['list_of_forms'];
				
				echo "<br/><br/>";
				
				// GET TABLE NAME
				for ($i = 0; $i < sizeof($files_list['rows']); $i++) {
					if($files_list['rows'][$i][0] == $selectOption) {
						$tableName = $files_list['rows'][$i][1];
					}
				}
				
				echo "You choose table " .$tableName ."<br/>";
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/fusiontables/v2/tables/$selectOption/columns?key=AIzaSyCyyX6rCnLqaWh-2fOc6-EZvhg1fENwEYw");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$file = curl_exec($ch);
				curl_close($ch);
				$array = json_decode($file, true);
				global $array2;
				$array2 = $array["items"];
				
			?>
			
		<input type="hidden" name="tableName" value="<?php echo $tableName; ?>">
			
		  <!--- CAMERA --->
		<hr \>	
		  <h4>Camera in this project?</h4>
		  <div>
			  <input type="radio" value="1" name="choice-cam" id="choice-camera-yes" required>
			  <label for="choice-camera-yes">Yes</label>
			  
			  <input type="radio" value="0" name="choice-cam" id="choice-cam-no">
			  <label for="choice-cam-no">No</label>
		  </div>
		  
		  <!--- GPS LOC --->
		  <hr \>
		  <h4>GPS Single Point data collection in this project?</h4>
			<div>
				<input type="radio" value="1" name="choice-gps-ping" id="choice-gps-yes" required>
				<label for="choice-gps-ping">Yes</label>
				
				<input type="radio" value="0" name="choice-gps-ping" id="choice-gps-no">
				<label for="choice-gps-ping">No</label>
			</div>
			
		  <!--- GPS TRACKER --->
		  <hr \>
		  <h4>GPS Tracking in this project? (KML)</h4>
			<div>
			  <div>
				<input type="radio" value="1" name="choice-gps-tracker" id="choice-gps-tracker-yes" required>
				<label for="choice-gps-tracker-yes">Yes</label>
			  
				<div class="reveal-if-active">              
			<!--	  
				  <br/><br/>
			
				  <label for="which-table">Manual or Automatic?</label>
				  <select name="gps-tracker-type" size="2">
					<option value="manual">manual</option>
					<option value="automatic">automatic</option>
				  </select>
			-->
				</div>
			  </div>
			  
			  <div>
				<input type="radio" value="0" name="choice-gps-tracker" id="choice-gps-tracker-no">
				<label for="choice-gps-tracker-no">No</label>
				<br \>  
				<label for="which-table">How often? (in seconds)</label>
				  <input name="gps-tracker-frequency" type="number" step="any">
			  </div>
			</div>
			
		  <!--- ID COLUMN --->
			
		  <hr \>
		  <h4>What is the ID column?</h4>
			<div>
			  <select name="id-column" size="4" class = "require-if-active">
				<?php
				foreach($array2 as $value)
				{
				  echo "<option value=\"".$value["name"]."\">".$value["name"]."</option>";
				}
			  ?>
			  </select>
			</div>
			
		  <!--- RUN COLUMN --->
			
		  <hr \>
		  <h4>Keep Track of Runs?</h4>
			<div>
				<input type="radio" value="1" name="choice-run" id="choice-run-yes" required>
				<label for="choice-run-yes">Yes</label>
			  
				<input type="radio" value="0" name="choice-run" id="choice-run-no">
				<label for="choice-run-no">No</label>
			</div>
			
		  <!--- UNIQUE COLUMN --->
			
		  <hr \>
		  <h4>Select other unique fields</h4>
		  <div>
			<select name="unique[]" id="multSel" multiple="multiple" size="4">
			  <?php
			  foreach($array2 as $value)
					  {
						echo "<option value=\"".$value["name"]."\">".$value["name"]."</option>";
					  }
			  ?>
			  <script>
			 /* var items = [];
			  $('#multSel').on('change', function(){
			  $('#multSel').each(function(i, sel){ 
			  alert( $(sel).val()); 
			  });
			  }
			  )*/
			  </script>

			</select>
		  </div>
		  
		  <!--- Upload LOGO --->
		   
		  <hr \>
		  <h4>Select Logo to Upload</h4>
		  <div>
			  <input type="file" name="fileToUpload" id="fileToUpload">
		  </div>
		  
		  <!--- Color Option --->
		  
		  <!--<h4>Choose a color for the app</h4>
		  <div>
			<label for="background-color"></label>
			<input name="color-option" id="background-color" type="color" />
		  </div>-->
		  
		  <!-- Point On Map Option -->
		  <hr \>
		  <h4> Include GPS Coordinates with Submission</h4>
		  <div>
			  <input type="radio" value="1" name="choice-locSub" id="choice-locSub-yes" required>
			  <label for="choice-locSub-yes">Yes</label>
			  
			  <input type="radio" value="0" name="choice-locSub" id="choice-locSub-no">
			  <label for="choice-locSub-no">No</label>
		  </div>
			
		  <!--- Directory --->
		   
		  <hr \>
		  <h4>Project Name</h4>
		  <div>
			  <input type="text" name="directory-name" id="directory-name" minlength="3">
		  </div>
		  

		  <!--- Finish Button --->
		<br \>	
			<div>
				<input type="submit" value="Submit" name="submit">
			</div>

		</form>
	</div>
  </body>
</html>
