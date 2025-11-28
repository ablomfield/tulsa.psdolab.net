<?php
session_start();

// Check Logged In
include($_SERVER['DOCUMENT_ROOT'] . "/includes/checkaccess.php");

// Retrieve Settings and Functions
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/webexfunctions.php");

// Retrieve and Perform Actions
if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
} elseif (isset($_GET["action"])) {
    $action = $_GET["action"];
} else {
    $action = '';
}

if ($action == "update") {
    $sitetitle = $_REQUEST["sitetitle"];
    $appversion = $_REQUEST["appversion"];
    $client_id = $_REQUEST["client_id"];
    $client_secret = $_REQUEST["client_secret"];
    $oauth_url = $_REQUEST["oauth_url"];

    $sqlquery = "UPDATE settings SET";
    $sqlquery = $sqlquery . " sitetitle='$sitetitle'";
    $sqlquery = $sqlquery . ", client_id='$client_id'";
    $sqlquery = $sqlquery . ", client_secret='$client_secret'";
    $sqlquery = $sqlquery . ", oauth_url='$oauth_url'";
    mysqli_query($dbconn, $sqlquery);
}

if ($action == "refresh") {
    $accessarr = array(
        'grant_type' => 'refresh_token',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'refresh_token' => $refreshtoken
    );
    $accessenc = http_build_query($accessarr);
    $getaccess = curl_init();
    curl_setopt_array($getaccess, array(
        CURLOPT_URL => 'https://webexapis.com/v1/access_token',
        CURLOPT_RETURNTRANSFER => true, // return the transfer as a string of the return value
        CURLOPT_TIMEOUT => 0,   // The maximum number of seconds to allow cURL functions to execute.
        CURLOPT_POST => true,   // This line must place before CURLOPT_POSTFIELDS
        CURLOPT_POSTFIELDS => $accessenc // Data that will send
    ));
    $accessdata = curl_exec($getaccess);
    $accessjson = json_decode($accessdata);
    //print_r($accessdata);
    $newaccesstoken = $accessjson->access_token;
    $newaccessexpires = $accessjson->expires_in;
    $newaccessexpires = date("Y-m-d H:i:s", time() + $newaccessexpires);
    $newrefreshtoken = $accessjson->refresh_token;
    $newrefreshexpires = $accessjson->refresh_token_expires_in;
    $newrefreshexpires = date("Y-m-d H:i:s", time() + $newrefreshexpires);
    $sqlquery = "UPDATE settings SET accesstoken = '" . $newaccesstoken . "', accessexpires = '" . $newaccessexpires . "', refreshtoken = '" . $newrefreshtoken . "', refreshexpires = '" . $newrefreshexpires . "'";
    $dbconn->query($sqlquery);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo ($sitetitle); ?></title>
    <link rel="icon" type="image/png" href="/images/tulsa.png">
    <link rel="stylesheet" href="/css/tulsa.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src='https://code.jquery.com/jquery-1.4.2.js'></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-dark-grey.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
</head>

<body>
    <div class="parent">
        <div class="tulsa-logo">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/logo.php"; ?>
        </div>
        <div class="tulsa-title">
            <?php echo ($sitetitle); ?>
        </div>
        <div class="tulsa-avatar">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/avatar.php"; ?>
        </div>
        <div class="tulsa-menu">
            <?php if ($userpkid != "") {
                include $_SERVER['DOCUMENT_ROOT'] . "/includes/menu.php";
            } ?>
        </div>
        <div class="tulsa-body" style="width: 800px">
            <?php
            $rssetedit = mysqli_query($dbconn, "SELECT * FROM settings");
            $rowsetedit = mysqli_fetch_assoc($rssetedit);
            ?>
            <form method="post">
                <input type="hidden" name="action" value="update">
                <table>
                    <tr>
                        <th colspan="2">Site Settings</td>
                    </tr>
                    <tr>
                        <td>Site Title</td>
                        <td><input type="text" name="sitetitle" size="50" value="<?php echo $rowsetedit["sitetitle"]; ?>"></td>
                    </tr>
                    <tr>
                        <th colspan="2">Integration Settings</th>
                    </tr>
                    <tr>
                        <td>Client ID</td>
                        <td><input type="text" name="client_id" size="50" value="<?php echo ($rowsetedit["client_id"]); ?>"></td>
                    </tr>
                    <tr>
                        <td>Client Secret</td>
                        <td><input type="text" name="client_secret" size="50" value="<?php echo ($rowsetedit["client_secret"]); ?>"></td>
                    </tr>
                    <tr>
                        <td>OAuth URL</td>
                        <td><input type="text" name="oauth_url" size="50" value="<?php echo ($rowsetedit["oauth_url"]); ?>"></td>
                    </tr>
                    <tr>
                        <th colspan="2">Token Settings</th>
                    </tr>
                    <tr>
                        <td>Admin Token</td>
                        <td><input type="text" name="accesstoken" size="50" value="<?php echo ($rowsetedit["accesstoken"]); ?>" disabled></td>
                    </tr>
                    <?php
                    if ($rowsetedit["accesstoken"] <> "") {
                        echo ("                    <tr>\n");
                        echo ("                      <td></td>\n");
                        echo ("                      <td>Token User: " . webexgetmyname($rowsetedit["accesstoken"]) . "</td>\n");
                        echo ("                    </tr>\n");
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td>Expires: <?php echo ($rowsetedit["accessexpires"]); ?></td>
                    </tr>
                    <tr>
                        <td>Refresh Token</td>
                        <td><input type="text" name="refreshtoken" size="50" value="<?php echo ($rowsetedit["refreshtoken"]); ?>" disabled></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>Expires: <?php echo ($rowsetedit["refreshexpires"]); ?></td>
                    </tr>
                    <tr>
                        <td><input type="submit" value="Update Settings" class="widebutton"></td>
            </form>
            <form method="post">
                <input type="hidden" name="action" value="refresh">
                <td><input type="submit" value="Refresh Token" class="widebutton">&nbsp;&nbsp;&nbsp;<a href="<?php echo (str_replace("%2Flogin%2F", "%2Fadmin%2Fsettoken%2F", $oauth_url)); ?>" class="widebutton" onclick="return confirm('Are you sure you want to set the access token? This will overwrite any existing token.');">Set Token</a>
                </td>
            </form>
            </tr>
            </table>
        </div>
        <div class="tulsa-footer">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
        </div>
    </div>
</body>

</html>