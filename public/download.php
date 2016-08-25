<?php
session_start();
$email = $_SESSION['email'];

/* LOGO UPLOAD */

if(isset($_POST['submit'])){
    // Collect Directory Name
    $directoryName = $_POST['directory-name'];
}

// Create the project directory and images directory

if (!file_exists("projects/" .$directoryName)) {
    mkdir("projects/" .$directoryName, 0700, true);
    mkdir("projects/" .$directoryName ."/images/", 0700, true);
	$directoryName = "projects/" .$directoryName;
}

$target_dir = $directoryName."/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
echo $target_file;
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    if($_FILES["fileToUpload"]["tmp_name"]) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    }
    else {
        $check = FALSE;
        $uploadOk = 0;
    }
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ". ";
        $uploadOk = 1;
    } else {
        echo "File is not an image. ";
        $uploadOk = 0;
    }
}
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded. ";
}
else {
    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists. ";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        echo "Sorry, your file is too large. ";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    
    // if everything is ok, try to upload file
    else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded to directory: " .$target_dir;
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Save Options from Form:

    if(isset($_POST['submit'])){
        
		// Collect the table name
		$tableName = $_POST['tableName'];
		
        // Collect Camera Variables
        $cameraOption = $_POST['choice-cam'];
        
        // Collect GPS Ping Variables
        $gpsPingOption = $_POST['choice-gps-ping'];
        
        // Collect GPS Tracker Variables
        $gpsTrackerOption = $_POST['choice-gps-tracker'];
        $gpsTrackerFreq = $_POST['gps-tracker-frequency'];
		$gpsTrackerFreq = $gpsTrackerFreq * 1000;
        $gpsTrackerType = $_POST['gps-tracker-type'];
        
        // Collect ID Column
        $IDColumn = $_POST['id-column'];
        
        // Collect ID Column
        $runOption = $_POST['choice-run'];
        // $runColumn = $_POST['run-column'];
        
        // Collect ID Column
		$uniqueColumns = $_POST['unique'];
        
        // Select Logo is already stored in $target_file;
        
        // Collect Directory Name
        // $directoryName = $_POST['directory-name'];
        
        // Collect Directory Name
        $colorOption = $_POST['color-option'];
        
        // Output for testing
		echo "</br></br>Table Name: " .$tableName;
        echo "</br></br>Camera Option: " .$cameraOption;
        echo "</br>Camera Column: " .$cameraColumn;
        echo "</br></br>GPS Ping Option: " .$gpsPingOption;
        echo "</br>GPS Ping Column: " .$gpsPingColumn;
        echo "</br></br>GPS Tracker Option: " .$gpsTrackerOption;
        echo "</br>GPS Tracker Column: " .$gpsTrackerColumn;
        echo "</br>GPS Tracker Frequency: " .$gpsTrackerFreq;
        echo "</br>GPS Tracker Type: " .$gpsTrackerType;
        //echo "</br></br>Color Option: " .$colorOption;
        echo "</br></br>ID Column: " .$IDColumn;
        echo "</br></br>Email: " . $email;
        echo "</br></br>Run Option: " .$runOption;
        echo "</br></br>Run Column: " .$runColumn;
        echo "</br></br>Unique Columns: " .var_dump($uniqueColumns);
        echo "</br></br>Directory Name: " .$directoryName; 
        echo "</br></br>Directory Name: " .$target_file ."<br/><br/>"; 

        
    }
    
    // Build the buildApp.txt file
    
    $file = fopen("buildApp.txt","w");
    echo fwrite($file,"table $tableName\n"); // Table Name
    echo fwrite($file,"camera $cameraOption image\n"); // Camera
    echo fwrite($file,"gpsLoc $gpsPingOption latitude longitude\n"); // GPS location
    echo fwrite($file,"gpsTracker $gpsTrackerOption $gpsTrackerFreq latTrack longTrack\n"); // GPS Tracker
    echo fwrite($file,"datetime date\n");
    echo fwrite($file,"id $IDColumn\n");
    echo fwrite($file,"run $runOption run\n"); //ID
    echo fwrite($file,"directory $directoryName\n"); //dir
    echo fwrite($file,"email $email\n"); //email
    // echo fwrite($file,"color $colorOption\n"); //Color
    foreach ($uniqueColumns as $value) {
        echo fwrite($file,"unique $value\n");
    }
    echo fwrite($file,"endFile\n");  //<EOF>
    fclose($file);
	
	// Run Jess' Glorious Script
	
    $buildApp = shell_exec('../private/build_apk.sh /var/www/app.apk /var/www/test.apk /var/www/html/ft_test/buildApp.txt /var/www/html/ft_test/projects/'.$target_file);
//	Debug for script when uncommented
//	echo "<pre>$buildApp</pre>";
	
	// Make a copy of the output file for download
	copy('/var/www/test.apk', '/var/www/html/ft_test/test.apk');
?>

<center><a href="test.apk" style="font-size:30px;">Download APK File</a></center><br/><br/>
