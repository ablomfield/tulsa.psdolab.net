<?php
// Retrieve Settings and Functions
include($_SERVER['DOCUMENT_ROOT'] . "/includes/settings.php");
$officecount = 0;
echo ("			<table class=\"default\">\n");
echo ("				<thead>\n");
echo ("					<tr>\n");
echo ("						<th>Room</th>\n");
echo ("						<th>In Use</th>\n");
echo ("						<th>Count</th>\n");
echo ("					</tr>\n");
echo ("				</thead>\n");
echo ("				<tbody>\n");
$devsql = "SELECT deviceid, spacename FROM spaces";
$rsdev = $dbconn->query($devsql);
if ($rsdev->num_rows > 0) {
    // Output data of each row
    while ($row = $rsdev->fetch_assoc()) {
        echo ("					<tr>\n");
        echo ("						<td>" . $row["spacename"] . "</td>\n");
        $deviceurl = "https://webexapis.com/v1/xapi/status/?deviceId=" . $row["deviceid"] . "&name=RoomAnalytics.RoomInUse";
        //echo $deviceurl;
        $getdevice = curl_init($deviceurl);
        curl_setopt($getdevice, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($getdevice, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $getdevice,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accesstoken
            )
        );
        $devicejson = curl_exec($getdevice);
        $devicearray = json_decode($devicejson);
        //print_r($devicejson);
        if (isset($devicearray->result->RoomAnalytics->RoomInUse)) {
            if ($devicearray->result->RoomAnalytics->RoomInUse == "True") {
                echo ("						<td bgcolor=\"green\">" . $devicearray->result->RoomAnalytics->RoomInUse . "</td>\n");
            } else {
                echo ("						<td>" . $devicearray->result->RoomAnalytics->RoomInUse . "</td>\n");
            }
        } else {
            echo ("						<td>False</td>\n");
        }
        $deviceurl = "https://webexapis.com/v1/xapi/status/?deviceId=" . $row["deviceid"] . "&name=RoomAnalytics.PeopleCount.Current";
        //echo $deviceurl;
        $getdevice = curl_init($deviceurl);
        curl_setopt($getdevice, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($getdevice, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $getdevice,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accesstoken
            )
        );
        $devicejson = curl_exec($getdevice);
        $devicearray = json_decode($devicejson);
        //print_r($devicejson);
        if (isset($devicearray->result->RoomAnalytics->PeopleCount->Current)) {
            if ($devicearray->result->RoomAnalytics->PeopleCount->Current > 0) {
                $officecount = $officecount + $devicearray->result->RoomAnalytics->PeopleCount->Current;
                echo ("						<td bgcolor=\"green\" align=\"center\">" . $devicearray->result->RoomAnalytics->PeopleCount->Current . "</td>\n");
            } else {
                echo ("						<td align=\"center\">0</td>\n");
            }
        } else {
            echo ("						<td align=\"center\">-</td>\n");
        }
        echo ("					</tr>\n");
    }
}
echo ("				</tbody>\n");
echo ("				<thead>\n");
echo ("					<tr>\n");
echo ("						<th colspan=\"2\">Total</th>\n");
echo ("						<th>$officecount</th>\n");
echo ("					</tr>\n");
echo ("				</thead>\n");
echo ("			</table>\n");
