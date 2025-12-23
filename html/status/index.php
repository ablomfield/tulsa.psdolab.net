<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");

$officecount = 0;
$devices = [];
$requests = [];

// ----------------------------------------
// Load devices
// ----------------------------------------
$rsdev = $dbconn->query("SELECT deviceid, spacename FROM spaces");
while ($row = $rsdev->fetch_assoc()) {
    $devices[$row['deviceid']] = [
        'spacename' => $row['spacename'],
        'inuse' => false,
        'count' => null
    ];
}

// ----------------------------------------
// Init multi-curl
// ----------------------------------------
$mh = curl_multi_init();

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accesstoken
];

// ----------------------------------------
// Create requests
// ----------------------------------------
foreach ($devices as $deviceid => $_) {

    $metrics = [
        'inuse' => 'RoomAnalytics.RoomInUse',
        'count' => 'RoomAnalytics.PeopleCount.Current'
    ];

    foreach ($metrics as $type => $metric) {

        $ch = curl_init(
            "https://webexapis.com/v1/xapi/status/?deviceId={$deviceid}&name={$metric}"
        );

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10
        ]);

        curl_multi_add_handle($mh, $ch);

        // Store metadata separately
        $requests[] = [
            'handle'   => $ch,
            'deviceid' => $deviceid,
            'type'     => $type
        ];
    }
}

// ----------------------------------------
// Execute in parallel
// ----------------------------------------
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

// ----------------------------------------
// Process responses
// ----------------------------------------
foreach ($requests as $req) {

    $response = curl_multi_getcontent($req['handle']);
    $json = json_decode($response);

    if ($req['type'] === 'inuse') {
        if (($json->result->RoomAnalytics->RoomInUse ?? '') === 'True') {
            $devices[$req['deviceid']]['inuse'] = true;
        }
    }

    if ($req['type'] === 'count') {
        if (isset($json->result->RoomAnalytics->PeopleCount->Current)) {
            $devices[$req['deviceid']]['count'] =
                (int)$json->result->RoomAnalytics->PeopleCount->Current;
        }
    }

    curl_multi_remove_handle($mh, $req['handle']);
    curl_close($req['handle']);
}

curl_multi_close($mh);

// ----------------------------------------
// Render table
// ----------------------------------------
echo '<table class="default">
<thead>
<tr><th>Room</th><th>In Use</th><th>Count</th></tr>
</thead><tbody>';

foreach ($devices as $device) {

    echo "<tr><td>{$device['spacename']}</td>";

    echo $device['inuse']
        ? '<td bgcolor="green">True</td>'
        : '<td>False</td>';

    if ($device['count'] > 0) {
        $officecount += $device['count'];
        echo "<td bgcolor=\"green\" align=\"center\">{$device['count']}</td>";
    } elseif ($device['inuse']) {
        $officecount++;
        echo '<td bgcolor="green" align="center">1</td>';
    } else {
        echo '<td align="center">-</td>';
    }

    echo '</tr>';
}

echo "</tbody>
<thead>
<tr><th colspan=\"2\">Total</th><th>{$officecount}</th></tr>
</thead>
</table>";
