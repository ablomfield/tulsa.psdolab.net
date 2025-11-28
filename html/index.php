<?php
session_start();

// Check Logged In
include($_SERVER['DOCUMENT_ROOT'] . "/includes/checkaccess.php");

// Retrieve Settings and Functions
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");

// Retrieve and Perform Actions
if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
} else {
    $action = "";
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
            if ($loggedin) {
                echo ("			<table class=\"default\">\n");
                echo ("				<thead>\n");
                echo ("					<tr>\n");
                echo ("						<th>Room</th>\n");
                echo ("						<th>In Use</th>\n");
                echo ("						<th>Count</th>\n");
                echo ("					</tr>\n");
                echo ("				</thead>\n");
                echo ("				<tbody>\n");
                $devsql = "SELECT deviceid, spacename FROM spaces";
                $rsdev = $dbconn->query($devsql);
                if ($rsdev->num_rows > 0) {
                    // Output data of each row
                    while ($row = $rsdev->fetch_assoc()) {
                        echo ("					<tr>\n");
                        echo ("						<td>" . $row["spacename"] . "</td>\n");
                        $deviceurl = "GET https://webexapis.com/v1/xapi/status/?deviceId=" . $row["deviceid"] . "&name=RoomAnalytics.RoomInUse";
                        echo $deviceurl;
                        $getdevice = curl_init($deviceurl);
                        curl_setopt($getdevice, CURLOPT_CUSTOMREQUEST, "GET");
                        curl_setopt($getdevice, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt(
                            $getdevice,
                            CURLOPT_HTTPHEADER,
                            array(
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . $accesstoken
                            )
                        );
                        $devicejson = curl_exec($getdevice);
                        $devicearray = json_decode($devicejson);
                        print_r($getdevice);
                        if (isset($devicearray->result->RoomAnalytics->RoomInUse)) {
                            echo ("						<td>" . $devicearray->result->RoomAnalytics->RoomInUse . "</td>\n");
                        } else {
                            echo ("						<td>Nope</td>\n");
                        }
                        echo ("						<td>&nbsp;</td>\n");
                        echo ("						<td>&nbsp;</td>\n");
                        echo ("					</tr>\n");
                    }
                }
                echo ("				</tbody>\n");
                echo ("				<thead>\n");
                echo ("					<tr>\n");
                echo ("						<th colspan=\"2\">Total</th>\n");
                echo ("						<th>X</th>\n");
                echo ("					</tr>\n");
                echo ("				</thead>\n");
                echo ("			</table>\n");
            } else {
                echo ("          <a href=\"" . $oauth_url . "\">\n");
                echo ("            <img width=\"400\" src=\"/images/signin.png\" alt=\"Sign In with Webex\" />\n");
                echo ("          </a>\n");
            }
            ?>
        </div>
        <div class="tulsa-footer">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
        </div>
    </div>
</body>

</html>