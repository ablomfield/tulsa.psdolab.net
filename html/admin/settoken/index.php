<?php
session_start();

// Retrieve Settings and Functions
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");

if (isset($_GET['code'])) {
    // Retrieve Code
    $oauth_code = $_GET['code'];
    $oauth_state = $_GET['state'];
    $accessarr = array(
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'https://tulsa.psdolab.net/admin/settoken/',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $oauth_code
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
    $authtoken = $accessjson->access_token;
    $authexpires = $accessjson->expires_in;
    $refreshtoken = $accessjson->refresh_token;
    $refreshexpires = $accessjson->refresh_token_expires_in;
    $authexpires = date("Y-m-d H:i:s", time() + $authexpires);
    $refreshexpires = date("Y-m-d H:i:s", time() + $refreshexpires);

    mysqli_query($dbconn,"UPDATE settings SET accesstoken='$authtoken', accessexpires='$authexpires', refreshtoken='$refreshtoken', refreshexpires='$refreshexpires'");

    header("Location: /admin/settings/");
    } else {
    header("Location: /admin/settings/");
}
