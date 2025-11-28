<?php
// Retrieve YAML Settings
$yamlsettings = yaml_parse_file('/opt/tulsa/settings.yaml');
$dbserver = $yamlsettings['Database']['ServerName'];
$dbuser = $yamlsettings['Database']['Username'];
$dbpass = $yamlsettings['Database']['Password'];
$dbname = $yamlsettings['Database']['DBName'];

// Load Settings
$dbconn = new mysqli($dbserver, $dbuser, $dbpass, $dbname);
if ($dbconn->connect_error) {
    die("Connection failed: " . $dbconn->connect_error);
}
$rssettings = mysqli_query($dbconn, "SELECT * FROM settings") or die("Error in Selecting " . mysqli_error($dbconn));
$rowsettings = mysqli_fetch_assoc($rssettings);
$sitetitle = $rowsettings["sitetitle"];
$client_id = $rowsettings["client_id"];
$client_secret = $rowsettings["client_secret"];
$integration_id = $rowsettings["integration_id"];
$oauth_url = $rowsettings["oauth_url"];
$accesstoken  = $rowsettings["accesstoken"];
$accessexpires  = $rowsettings["accessexpires"];
$refreshtoken  = $rowsettings["refreshtoken"];
$refreshexpires = $rowsettings["refreshexpires"];

// Set Debug
if (isset($_SESSION["enabledebug"])) {
    if ($_SESSION['enabledebug'] <> 1) {
        $_SESSION["enabledebug"] = 0;
    }
} else {
    $_SESSION["enabledebug"] = 0;
}
