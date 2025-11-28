<?php
session_start();

// Import Settings
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");

if (isset($_GET['code'])) {
    // Retrieve Code
    $oauth_code = $_GET['code'];
    $oauth_state = $_GET['state'];
    $accessarr = array(
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'https://tulsa.psdolab.net/login/',
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
    //print_r($accessdata);
    $authtoken = $accessjson->access_token;
    $authexpires = $accessjson->expires_in;
    $refreshtoken = $accessjson->refresh_token;
    $refreshexpires = $accessjson->refresh_token_expires_in;
    $authexpires = date("Y-m-d H:i:s", time() + $authexpires);
    $refreshexpires = date("Y-m-d H:i:s", time() + $refreshexpires);
    $lastaccess = date("Y-m-d H:i:s", time());

    $lastaccess = date("Y-m-d H:i:s", time());


    // Retrieve Details using authtoken
    $personurl = "https://webexapis.com/v1/people/me";
    $getperson = curl_init($personurl);
    curl_setopt($getperson, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($getperson, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $getperson,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $authtoken
        )
    );
    $persondata = curl_exec($getperson);
    $personjson = json_decode($persondata);
    $personid = $personjson->id;
    $displayname = $personjson->displayName;
    $emailarr = $personjson->emails;
    $email = strtolower($emailarr[0]);
    $emaildomain = substr($email, strpos($email, '@') + 1);
    $timezone = $personjson->timeZone;

    // Check if User Exists in Database
    $rsusercheck = mysqli_query($dbconn, "SELECT * FROM users WHERE email = '" . $email . "'");
    if (mysqli_num_rows($rsusercheck) == 0) {
        // Check Self Registration and Deny if Disabled
        if ($selfregistration == 0) {
            header("Location: /accessdenied?reason=selfregistration");
            exit("Denied - Self Registration Not Allowed");
        }
        // Check Allowed Domains and Deny if Notin List
        if ($lockdomains == 1) {
            $rsdomaincheck = mysqli_query($dbconn, "SELECT * FROM regdomains WHERE domain = '" . $emaildomain . "'");
            if (mysqli_num_rows($rsdomaincheck) == 0) {
                header("Location: /accessdenied?reason=domainlock&domain=" . $emaildomain);
                exit("Denied - domain not allowed ($emaildomain)");
            }
        }
        $insertsql = "INSERT INTO users (personid, displayname, email, lastaccess, timezone) VALUES('" . $personid . "', '" . str_replace("'", "''", $displayname) . "', '" . $email . "', '" . $lastaccess . "', '" . $timezone . "')";
        mysqli_query($dbconn, $insertsql);
        $userpkid = $dbconn->pkid;
        $_SESSION["userpkid"] = $userpkid;
        $_SESSION["personid"] = $personid;
        $_SESSION["email"] = $email;
        $_SESSION["displayname"] = $displayname;
        $_SESSION["timezone"] = $timezone;
        $_SESSION["authtoken"] = $authtoken;
        $_SESSION["timezone"] = $timezone;
        header("Location: /");
    } else {
        $rowusercheck = mysqli_fetch_assoc($rsusercheck);
        $isadmin = $rowusercheck["isadmin"];
        $userpkid = $rowusercheck["pkid"];
        $timezone = $rowusercheck["timezone"];
        $orgid = $rowusercheck["lastorg"];
        if ($orgid != NULL) {
            // Retrieve Org Details using authtoken
            $orgurl = "https://webexapis.com/v1/organizations/" . $orgid;
            $getorg = curl_init($orgurl);
            curl_setopt($getorg, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($getorg, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $getorg,
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $authtoken
                )
            );
            $orgdata = curl_exec($getorg);
            $orgjson = json_decode($orgdata);
            $orgname = $orgjson->displayName;
            $_SESSION["orgid"] = $orgid;
            $_SESSION["orgname"] = $orgname;
        }
        $updatesql = "UPDATE users SET personid = '" . $personid . "', displayname = '" . str_replace("'", "''", $displayname) . "', email = '" . $email . "', lastaccess = '" . $lastaccess . "' WHERE email = '" . $email . "'";
        mysqli_query($dbconn, $updatesql);
        $_SESSION["userpkid"] = $userpkid;
        $_SESSION["personid"] = $personid;
        $_SESSION["email"] = $email;
        $_SESSION["displayname"] = $displayname;
        $_SESSION["timezone"] = $timezone;
        $_SESSION["authtoken"] = $authtoken;
        $_SESSION["isadmin"] = $isadmin;
        $_SESSION["timezone"] = $timezone;
        header("Location: /");
    }
} else {
    header("Location: /");
}
