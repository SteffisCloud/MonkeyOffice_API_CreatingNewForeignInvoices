<?php

$url      = "http://127.0.0.1:8084/monkeyOfficeConnectJSON";
$login    = "api";
$passwort = "passwort";

$json = '{
    "firmaGet":""
}';

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => $json,
    CURLOPT_USERPWD        => $login . ":" . $passwort,
    CURLOPT_HTTPAUTH       => CURLAUTH_ANY,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'mbl-ident: 4F01644397CE0566C14398B5'
    ]
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo curl_error($ch);
} else {
    echo $response;
}

curl_close($ch);