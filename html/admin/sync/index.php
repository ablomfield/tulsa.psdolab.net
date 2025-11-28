<?php
session_start();

// Check Logged In
include($_SERVER['DOCUMENT_ROOT'] . "/includes/checkaccess.php");

// Check Admin
if (!$_SESSION["isadmin"]) {
    header("Location: /");
}

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
            <table id="list">
                <thead>
                    <tr>
                        <td></td>
                        <th>Records</th>
                        <th>Last Synced</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    //People
                    $rssync = mysqli_query($dbconn, "SELECT x.recordcount, personsynced FROM people, (select count(*) as recordcount FROM people) as x ORDER BY personsynced LIMIT 1;") or die("Error in Selecting " . mysqli_error($dbconn));
                    $rowsync = mysqli_fetch_assoc($rssync);
                    $lastsyncdate = $rowsync["personsynced"];
                    $lastsynced = new DateTime($lastsyncdate, new DateTimeZone('GMT'));
                    $lastsynced->setTimezone(new DateTimeZone($timezone));
                    $lastsynced = $lastsynced->format('Y-m-d H:i:s');
                    echo "        <tr>\n";
                    echo "          <th>People</th>\n";
                    echo "          <td>" . $rowsync["recordcount"] . "</td>\n";
                    echo "          <td>" . $lastsynced . "</td>\n";
                    echo "        </tr>\n";
                    // Organizations
                    $rssync = mysqli_query($dbconn, "SELECT x.recordcount, orgsynced FROM organizations, (select count(*) as recordcount FROM organizations) as x ORDER BY orgsynced LIMIT 1;") or die("Error in Selecting " . mysqli_error($dbconn));
                    $rowsync = mysqli_fetch_assoc($rssync);
                    $lastsyncdate = $rowsync["orgsynced"];
                    $lastsynced = new DateTime($lastsyncdate, new DateTimeZone('GMT'));
                    $lastsynced->setTimezone(new DateTimeZone($timezone));
                    $lastsynced = $lastsynced->format('Y-m-d H:i:s');
                    echo "        <tr>\n";
                    echo "          <th>Organizations</th>\n";
                    echo "          <td>" . $rowsync["recordcount"] . "</td>\n";
                    echo "          <td>" . $lastsynced . "</td>\n";
                    echo "        </tr>\n";
                    // Roles
                    $rssync = mysqli_query($dbconn, "SELECT x.recordcount, rolesynced FROM roles, (select count(*) as recordcount FROM roles) as x ORDER BY rolesynced LIMIT 1;") or die("Error in Selecting " . mysqli_error($dbconn));
                    $rowsync = mysqli_fetch_assoc($rssync);
                    $lastsyncdate = $rowsync["rolesynced"];
                    $lastsynced = new DateTime($lastsyncdate, new DateTimeZone('GMT'));
                    $lastsynced->setTimezone(new DateTimeZone($timezone));
                    $lastsynced = $lastsynced->format('Y-m-d H:i:s');
                    echo "        <tr>\n";
                    echo "          <th>Roles</th>\n";
                    echo "          <td>" . $rowsync["recordcount"] . "</td>\n";
                    echo "          <td>" . $lastsynced . "</td>\n";
                    echo "        </tr>\n";
                    // Subscriptions
                    $rssync = mysqli_query($dbconn, "SELECT x.recordcount, subsynced FROM subscriptions, (select count(*) as recordcount FROM subscriptions) as x ORDER BY subsynced LIMIT 1;") or die("Error in Selecting " . mysqli_error($dbconn));
                    $rowsync = mysqli_fetch_assoc($rssync);
                    $lastsyncdate = $rowsync["subsynced"];
                    $lastsynced = new DateTime($lastsyncdate, new DateTimeZone('GMT'));
                    $lastsynced->setTimezone(new DateTimeZone($timezone));
                    $lastsynced = $lastsynced->format('Y-m-d H:i:s');
                    echo "        <tr>\n";
                    echo "          <th>Subscriptions</th>\n";
                    echo "          <td>" . $rowsync["recordcount"] . "</td>\n";
                    echo "          <td>" . $lastsynced . "</td>\n";
                    echo "        </tr>\n";
                    ?>
                </tbody>
            </table>
        </div>
        <div class="tulsa-footer">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
        </div>
    </div>
</body>

</html>