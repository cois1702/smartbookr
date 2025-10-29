<?php
function sendPushNotification($token, $title, $body) {
    $data = [
        "to" => $token,
        "sound" => "default",
        "title" => $title,
        "body" => $body,
    ];

    $ch = curl_init("https://exp.host/--/api/v2/push/send");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Example usage
$token = "ExponentPushToken[xxxxxxxxxxxxxxxxxx]";
echo sendPushNotification($token, "New Booking Received", "Someone just made a booking!");
?>
