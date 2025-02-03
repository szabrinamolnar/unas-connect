<?php
$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_POST, true);

$request = '<?xml version="1.0" encoding="UTF-8" ?>
            <Params>
                <ApiKey>d04fb8476e379e59fbcc1e3e4cd4b77b7f8912e6</ApiKey>
                <WebshopInfo>true</WebshopInfo>
            </Params>';

curl_setopt($curl, CURLOPT_URL, "https://api.unas.eu/shop/login");
curl_setopt($curl, CURLOPT_POSTFIELDS, $request);


$response = curl_exec($curl);

if(curl_errno($curl)) {
    echo 'cURL hiba: ' . curl_error($curl);
} else {
    $xml = simplexml_load_string($response);

    if ($xml && isset($xml->Token)) {
        $token = (string)$xml->Token;
        echo "Sikeres login. Token: $token\n";
    } else {
        echo "Login hiba: " . $response; 
    }
}

curl_close($curl);
?>
