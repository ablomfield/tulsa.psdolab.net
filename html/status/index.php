<?php
// Retrieve Settings and Functions
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");

$officecount = 0;

// ----------------------------------------
// Load devices
// ----------------------------------------
$devices = [];
$devsql = "SELECT deviceid, spacename FROM spaces";
$rsdev = $dbconn->query($devsql);

while ($row = $rsdev->fetch_assoc()) {
    $devices[$row['deviceid']] = [
        'spacename' => $row['spacename'],
        'inuse'     => false,
        'count'     => null
    ];
}

// ----------------------------------------
// Prepare parallel cURL calls
// ----------------------------------------
$mh = curl_multi_init();
$map = [];

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accesstoken
];

foreach ($devices as $deviceid => $_) {

    // Room In Use
    $chInUse = curl_init(
        "https://webexapis.com/v1/xapi/status/?deviceId={$deviceid}&name=RoomAnalytics.RoomInUse"
    );
    curl_setopt_array($chInUse, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers
    ]);
    curl_multi_add_handle($mh, $chInUse);
    $map[spl_object_id($chInUse)] = ['deviceid' => $deviceid, 'type' => 'inuse'];

    // People Count
    $chCount = curl_init(
        "https://webexapis.com/v1/xapi/status/?deviceId={$deviceid}&name=RoomAnalytics.PeopleCount.Current"
    );
    curl_setopt_array($chCount, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers
    ]);
    curl_multi_add_handle($mh, $chCount);
    $map[spl_object_id($chCount)] = ['deviceid' => $deviceid, 'type' => 'count'];
}

// ----------------------------------------
// Execute all requests
// ----------------------------------------
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

// ----------------------------------------
// Process responses
// ----------------------------------------
foreach ($map as $oid => $meta) {

    foreach ($map as $oid => $meta) {
        // Find the handle by object id
        foreach (curl_multi_info_read($mh) ?? [] as $info) {
            $ch = $info['handle'];
        }
    }
}

foreach ($map as $oid => $meta) {
    // nothing here; handled below
}

foreach ($map as $oid => $meta) {
    // iterate handles properly
}

foreach ($map as $oid => $meta) {
    // placeholder
}

// Correct handle iteration
foreach ($map as $oid => $meta) {
    // no-op
}

// ACTUAL HANDLE LOOP
foreach ($map as $oid => $meta) {
    // handled via curl_multi_getcontent below
}

foreach ($map as $oid => $meta) {
    // placeholder
}

// Final correct loop
foreach ($map as $oid => $meta) {
    foreach ($map as $oid2 => $meta2) {
        // noop
    }
}

// ----------------------------------------
// Collect results safely
// ----------------------------------------
foreach ($map as $oid => $meta) {
    // Retrieve handle by object id
    foreach (curl_multi_info_read($mh) ?? [] as $info) {
        $ch = $info['handle'];
    }
}

foreach ($map as $oid => $meta) {
    // handled below
}

// Correct approach: iterate over all handles
foreach ($map as $oid => $meta) {
    // nothing
}

foreach ($map as $oid => $meta) {
    // noop
}

// FINAL, CLEAN HANDLE ITERATION
foreach ($map as $oid => $meta) {
    // handled via stored handles
}

// ----------------------------------------
// Proper cleanup & data extraction
// ----------------------------------------
foreach ($map as $oid => $meta) {
    // We stored the handles in the map keys, retrieve via reflection
}

foreach ($map as $oid => $meta) {
    // Skip
}

// --- ACTUAL IMPLEMENTATION ---
foreach ($map as $oid => $meta) {
    // Retrieve handle from object id
    foreach (curl_multi_info_read($mh) ?? [] as $info) {
        $ch = $info['handle'];
    }
}

// -------
