<?php
// Import Settings
include("/opt/tulsa/html/includes/settings.php");
$myfile = fopen("/opt/tulsa/log/scripts.log", "a+");
$rsdata = mysqli_query($dbconn, "SELECT accesstoken, accessexpires, refreshtoken, refreshexpires FROM settings ") or die("Error in Selecting " . mysqli_error($dbconn));
if ($rsdata) {
    if (mysqli_num_rows($rsdata) > 0) {
        while ($row = mysqli_fetch_assoc($rsdata)) {
            $accesstoken = $row["accesstoken"];
            $accessexpires = new DateTime($row["accessexpires"], new DateTimeZone('GMT'));
            $refreshtoken = $row["refreshtoken"];
            $refreshexpires = $row["refreshexpires"];
        }
    }
}
$expiresthreshold = new DateTime("now", new DateTimeZone('GMT'));
$expiresthreshold =  date_add($expiresthreshold, date_interval_create_from_date_string("1 day"));
echo ("Admin Token Expires: " . $accessexpires->format('Y-m-d g:ia') . "\n");
echo ("Refresh Threshold: " . $expiresthreshold->format('Y-m-d g:ia') . "\n");
if ($expiresthreshold > $accessexpires) {
    echo ("Tokens need to be refreshed.\n");
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
    $accesstoken = $accessjson->access_token;
    $accessexpires = $accessjson->expires_in;
    $refreshtoken = $accessjson->refresh_token;
    $refreshexpires = $accessjson->refresh_token_expires_in;
    $updatesql = "UPDATE settings SET accesstoken = '$accesstoken', accessexpires = NOW() + INTERVAL $accessexpires SECOND,  refreshtoken = '$refreshtoken', refreshexpires = NOW() + INTERVAL $refreshexpires SECOND";
    mysqli_query($dbconn, $updatesql);

    echo ("New Admin Token: " . $accesstoken . " (Expires in $accessexpires)\n");
    echo ("New Refresh Token: " . $refreshtoken . " (Expires in $refreshexpires)\n");
    echo ("Updated tokens save.\n");
    fwrite($myfile, date("Y-m-d H:i:s") . " - Checked access token. Refresh completed.\n");
} else {
    echo ("Tokens do not need to be refreshed.\n");
    fwrite($myfile, date("Y-m-d H:i:s") . " - Checked access token. No updated needed.\n");
}