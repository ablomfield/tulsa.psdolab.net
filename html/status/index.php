<?php
// Retrieve Settings and Functions
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");

$officecount = 0;

// -------------------------------------------------
// Load devices from DB
// -------------------------------------------------
$devices = [];
$devsql = "SELECT deviceid, spacename FROM spaces";
$rsdev = $dbconn->query($devsql);

if ($rsdev->num_rows > 0) {
    while ($row = $rsdev->fetch_assoc()) {
        $devices[$row['deviceid']] = [
            'spacename' => $row['spacename'],
            'inuse'     => false,
            'count'     => null
        ];
    }
}

// -------------------------------------------------
// Prepare parallel cURL requests
// -------------------------------------------------
$mh = curl_multi_init();
$handles = [];

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accesstoken
];

foreach ($devices as $deviceid => $data) {

    // Room In Use
    $urlInUse = "https://webexapis.com/v1/xapi/status/?deviceId={$deviceid}&name=RoomAnalytics.RoomInUse";
    $ch1 = curl_init($urlInUse);
    curl_setopt_array($ch1, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers
    ]);
    curl_multi_add_handle($mh, $ch1);
    $handles[(string)$ch1] = ['deviceid' => $deviceid, 'type' => 'inuse'];

    // People Count
    $urlCount = "https://webexapis.com/v1/xapi/status/?deviceId={$deviceid}&name=RoomAnalytics.PeopleCount.Current";
    $ch2 = curl_init($urlCount);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers
    ]);
    curl_multi_add_handle($mh, $ch2);
    $handles[(string)$ch2] = ['deviceid' => $deviceid, 'type' => 'count'];
}

// -------------------------------------------------
// Execute all requests in parallel
// -------------------------------------------------
do {
    $status = curl_multi_exec($mh, $active);
    if ($active) {
        curl_multi_select($mh);
    }
} while ($active && $status == CURLM_OK);

// -------------------------------------------------
// Process responses
// -------------------------------------------------
foreach ($handles as $handleKey => $meta) {
    $ch = array_search($handleKey, array_map('strval', array_keys($handles))) !== false
        ? array_keys($handles)[array_search($handleKey, array_map('strval', array_keys($handles)))]
        : null;

    if (!$ch) continue;

    $response = curl_multi_getcontent($ch);
    $json = json_decode($response);

    $deviceid = $meta['deviceid'];

    if ($meta['type'] === 'inuse') {
        if (isset($json->result->RoomAnalytics->RoomInUse) &&
            $json->result->RoomAnalytics->RoomInUse === "True") {
            $devices[$deviceid]['inuse'] = true;
        }
    }

    if ($meta['type'] === 'count') {
        if (isset($json->result->RoomAnalytics->PeopleCount->Current)) {
            $devices[$deviceid]['count'] = (int)$json->result->RoomAnalytics->PeopleCount->Current;
        }
    }

    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

curl_multi_close($mh);

// -------------------------------------------------
// Render HTML
// -------------------------------------------------
echo "<table class=\"default\">\n";
echo "<thead>
        <tr>
            <th>Room</th>
            <th>In Use</th>
            <th>Count</th>
        </tr>
      </thead>\n<tbody>\n";

foreach ($devices as $device) {
    echo "<tr>";
    echo "<td>{$device['spacename']}</td>";

    // In Use column
    if ($device['inuse']) {
        echo "<td bgcolor=\"green\">True</td>";
    } else {
        echo "<td>False</td>";
    }

    // Count column
    if ($device['count'] !== null && $device['count'] > 0) {
        $officecount += $device['count'];
        echo "<td bgcolor=\"green\" align=\"center\">{$device['count']}</td>";
    } elseif ($device['inuse']) {
        $officecount += 1;
        echo "<td bgcolor=\"green\" align=\"center\">1</td>";
    } else {
        echo "<td align=\"center\">-</td>";
    }

    echo "</tr>\n";
}

echo "</tbody>
      <thead>
        <tr>
            <th colspan=\"2\">Total</th>
            <th>{$officecount}</th>
        </tr>
      </thead>
      </table>\n";
