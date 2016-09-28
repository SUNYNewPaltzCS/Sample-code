 <?php
require_once 'google-api-php-client/src/Google/autoload.php';

session_start();
$client = new Google_Client();
$client->setAuthConfigFile('../public/client_secrets.json');
$client->addScope(Google_Service_Fusiontables::FUSIONTABLES);
$client->setAccessType("offline");
$client->setApprovalPrompt('force');

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $drive_service = new Google_Service_Fusiontables($client);
  $files_list = $drive_service->query->sql("show tables");
  $table = $files_list['rows'][0][0];
} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

?>

  <html>
  <head>
    <link rel="stylesheet" type="text/css" href="style.css">
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
    <img src="BClogo.png" alt="Benjamin Center @ SUNY New Paltz" id="bcl">
    <form action="builder.php" method="post" enctype="multipart/form-data">
      
      <h4>List of Fusion Tables</h4>
      <select name="list_of_forms" id="forms" size="5">
        <?php
          for ($i = 0; $i < sizeof($files_list['rows']); $i++) {
              echo '<option value="' . $files_list['rows'][$i][0];
              echo '">';
              print $files_list['rows'][$i][1];
              echo '</option>';
          }
        ?>
      </select>
      <br/><br/>
      <input type="submit" value="Submit" name="submit">
    </form>
  </body>
</html>
