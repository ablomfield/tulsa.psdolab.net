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
  <script>
    $(document).ready(function() {
      $('#list').DataTable({
        autoWidth: false,
        order: [
          [0, 'asc']
        ]
      });
    });
  </script>    
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
                        <th>Username</th>
                        <th>Last Access</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rsdata = mysqli_query($dbconn, "SELECT * FROM users WHERE pkid > 0 ORDER BY email") or die("Error in Selecting " .
                        mysqli_error($dbconn));
                    if ($rsdata) {
                        if (mysqli_num_rows($rsdata) > 0) {
                            while ($row = mysqli_fetch_assoc($rsdata)) {
                                if ($row["lastaccess"] != null) {
                                    $lastaccess = new DateTime($row["lastaccess"], new DateTimeZone('GMT'));
                                    $lastaccess->setTimezone(new DateTimeZone($timezone));
                                    $lastaccess = $lastaccess->format('Y-m-d H:i:s');
                                } else {
                                    $lastaccess = "-";
                                }
                                echo "        <tr>\n";
                                echo "          <td>" . $row["email"] . "</td>\n";
                                echo "          <td>" . $lastaccess . "</td>\n";
                                echo "          <form action=\"view/\" method=\"post\">\n";
                                echo "          <input type=\"hidden\" name=\"pkid\" value=\"" . $row["pkid"] . "\">\n";
                                echo "          <td align=\"right\"><input type=\"submit\" value=\"Details\" class=\"button\"></d>\n";
                                echo "          </form>\n";
                                echo "        </tr>\n";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
            <p>
            <form method="post" action="/admin/users/add/">
                <input type="submit" value="Add User" class="button">
            </form>
            </p>
        </div>
        <div class="tulsa-footer">
            <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
        </div>
    </div>
</body>

</html>