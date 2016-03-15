<?php
var_dump($_FILES);
var_dump($_POST); 
var_dump($_GET);
	if($_FILES['image']) {
		$file_tmp = $_FILES['image']['tmp_name'];
		$file_name = $_FILES['image']['name'];
		move_uploaded_file($file_tmp, "images/".$file_name);
	}
?>
