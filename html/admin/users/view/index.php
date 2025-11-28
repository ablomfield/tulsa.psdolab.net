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

if (isset($_REQUEST['pkid'])) {
  $pkid = $_REQUEST['pkid'];
} else {
  $pkid = '';
}

if (isset($_REQUEST['makeadmin'])) {
  $makeadmin = $_REQUEST['makeadmin'];
} else {
  $makeadmin = 0;
}

if ($action == "update" && $pkid <> "") {
  mysqli_query($dbconn,"UPDATE users SET isadmin = $makeadmin WHERE pkid = '$pkid'");
  header("Location: /admin/users/");
}

if ($action == "delete" && $pkid <> "") {
  mysqli_query($dbconn,"DELETE FROM users WHERE pkid = '$pkid'");
  header("Location: /admin/users/");
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
      <form method="post">
        <?php
        $rsuser = mysqli_query($dbconn, "SELECT * FROM users WHERE pkid = '" . $pkid . "'");
        $rowuser = mysqli_fetch_assoc($rsuser);
        ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="pkid" value="<?php echo ($pkid); ?>">
        <table>
          <tr>
            <td>Email Address</td>
            <td><input type="text" name="email" size="50" value="<?php echo $rowuser["email"]; ?>" disabled>
          </tr>
          <tr>
            <td>Display Name</td>
            <td><input type="text" name="email" size="50" value="<?php echo $rowuser["displayname"]; ?>" disabled>
          </tr>
          <tr>
            <td>Admin?</td>
            <td><input type="checkbox" name="makeadmin" <?php if ($rowuser["isadmin"]) { echo (" checked"); }?> value="1">
          </tr>          
          <tr>
            <td><input type="submit" value="Update User" class="button">
            </form>
            <form method="post" onsubmit="return confirm('Are you sure you want to delete <?php echo ($rowuser["email"]); ?>?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="pkid" value="<?php echo ($pkid); ?>">
            <td><input type="submit" value="Delete User" class="button"></td>
            </form>
          </tr>
        </table>
      </form>
      <p>
      </p>

    </div>
    <div class="tulsa-footer">
      <?php include $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php"; ?>
    </div>
  </div>
</body>

</html>