<?php

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_POST, true);

$token = '62db1069030c0ef65cec11cda19aee8659aa4f0d'; 


$headers = array();
$headers[] = "Authorization: Bearer " . $token;
$headers[] = "Content-Type: application/xml";


$request = '<?xml version="1.0" encoding="UTF-8" ?>
<Products>
    <Product>
        <Action>add</Action> 
        <Sku>400578</Sku>
        <Name>Connector for 10 mm LED strip, 2-PIN, Strip-To-Strip</Name>
        <Unit>7138</Unit>
        <Categories>
            <Category>
                <Id>43041</Id>
                <Name>Világítástechnika|LED szalag</Name>
                <Type>base</Type>
            </Category>
        </Categories>
        <Prices>
            <Price>
                <Type>normal</Type>
                <Net>29.22</Net>
                <Gross>71.86</Gross>
            </Price>
        </Prices>
    </Product>
</Products>';

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_URL, "https://api.unas.eu/shop/setProduct");
curl_setopt($curl, CURLOPT_POSTFIELDS, $request);

$response = curl_exec($curl);

if(curl_errno($curl)) {
    echo 'cURL hiba: ' . curl_error($curl);
} else {
    echo "API válasz: " . $response;
}

curl_close($curl);
?>
