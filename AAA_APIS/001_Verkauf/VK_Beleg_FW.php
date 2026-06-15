<?php

$sdkFile = __DIR__ . '/../../sdk/PHP/Native/cubeSQLServer.php';

echo "SDK-Datei: " . $sdkFile . PHP_EOL;

if (!file_exists($sdkFile)) {
    die("SDK-Datei nicht gefunden: " . $sdkFile . PHP_EOL);
}

include_once $sdkFile;

if (!class_exists('cubeSQLServer')) {
    die("Klasse cubeSQLServer wurde nicht geladen." . PHP_EOL);
}


function getWaehrungsNotiz(cubeSQLServer $cubesql, string $datum, string $waehrung): string
{
    $waehrung = strtoupper($waehrung);

    $sql = "
        SELECT
            kursdatum,
            waehrung,
            ezb_kurs,
            eur_pro_einheit,
            quelle
        FROM wechselkurse
        WHERE UPPER(waehrung) = UPPER('{$waehrung}')
          AND kursdatum <= '{$datum}'
        ORDER BY kursdatum DESC
        LIMIT 1
    ";

    $rs = $cubesql->select($sql);

    if ($cubesql->isError()) {
        return "Fehler bei der Kursabfrage: " . $cubesql->errorMessage;
    }

    if (!$rs || count($rs) === 0) {
        return "Kein Wechselkurs für {$waehrung} gefunden.";
    }

    $row = $rs[0];

    return sprintf(
        "Währungsumrechnung gemäß EZB-Referenzkurs vom %s.\n1 EUR = %.6f %s\n1 %s = %.6f EUR\nQuelle: %s",
        date("d.m.Y", strtotime($row["kursdatum"])),
        (float)$row["ezb_kurs"],
        $waehrung,
        $waehrung,
        (float)$row["eur_pro_einheit"],
        $row["quelle"] ?: "EZB"
    );
}


// cubeSQL Verbindung

$cubesql = new cubeSQLServer();

$rc = $cubesql->connect_database(
    "192.168.178.xxx",
    4430,
    "admin",
    "Passwort_DB_Waehrung",
    "waehrung"
);

if (!$rc) {
    die("cubeSQL Fehler: " . $cubesql->errorMessage . PHP_EOL);
}


// Belegdaten

$datum    = $_GET['datum'] ?? '';
$waehrung = $_GET['waehrung'] ?? '';

$notizen = getWaehrungsNotiz(
    $cubesql,
    $datum,
    $waehrung
);

echo "Notiz:" . PHP_EOL;
echo $notizen . PHP_EOL . PHP_EOL;


// MonKey Office API

$url      = "http://127.0.0.1:8084/monkeyOfficeConnectJSON";
$login    = "api";
$passwort = "Passwort_API";
$firmaId  = "4F01644397CE0566C14398B5"; // FirmenID


// Verkaufbeleg

$data = [
    "verkaufbelegAdd" => [
        "VerkaufbelegAddItem" => [

            "Adresse_ID" => "480C644B85E2297DB23598B64C1B",// AdressID
            "Ansprechpartner_ID" => "",
            "AuftragNr" => "",

            "VerkaufbelegArt" => 4,

            "Entwurf" => false,
            "EtikettTag" => 0,

            "Datum" => $datum,
            "Lieferdatum" => $datum,

            "Referenz" => "",
            "Projekt_ID" => "",

            "RechnAnschrift" => "",
            "LieferAnschrift" => "",

            "LaVerwenden" => false,

            "Betreff" => "Your new VPN",
            "Anrede" => "Dear Michel",

            "KopfText" => "",
            "FussText" => "",
            "Grussformel" => "",

            "Notizen" => $notizen,

            "Bearbeiter" => "",
            "BearbeiterAngeben" => true,

            "Steuergebiet" => 3,
            "BerechnungArt" => 0,

            "VKPreisliste_ID" => "",

            "Waehrung" => $waehrung,

            "Zahlungsbedingungen" => "",
            "SepaMandatReferenz" => "",
            "SepaBankeinzug" => "",

            "Lieferart" => "",
            "Versandnummer" => "",
            "VersandURL" => "",

            "Rabatt" => "0,00",

            "Lagerbuchung" => false
        ]
    ]
];

$json = json_encode(
    $data,
    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);


// Anfrage senden

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => $login . ":" . $passwort,
    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
    CURLOPT_POSTFIELDS => $json,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "mbl-ident: {$firmaId}"
    ]
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL Fehler: " . curl_error($ch) . PHP_EOL;
} else {
    echo "MonKey Antwort:" . PHP_EOL;
    echo $response . PHP_EOL;
}

curl_close($ch);

$cubesql->disconnect();

?>