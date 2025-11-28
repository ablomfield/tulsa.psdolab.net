<?php
session_start();
$_SESSION["userpkid"] = "";
$_SESSION = array();
session_unset();
session_destroy();
setcookie("userpkid", "", strtotime("-1 hour"),"/");
Header("Location: /");
?>