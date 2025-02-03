<?php

namespace UnasOnline\UnasConnect\Api;

use UnasOnline\UnasConnect\Api\Client;
use UnasOnline\UnasConnect\Exception\LoginException;

class UnasApi
{
    private $unasClient;

    public function __construct($apiKey, $cache)
    {


        if (!$apiKey) {
            throw new \Exception('UNAS_API_KEY is not set in .env file');
        }

        $config = [
            'apiKey' => $apiKey,
            'apiUrl' => 'https://api.unas.eu/shop/',
        ];
        
        $this->unasClient = new Client($config, $cache);
    }

    public function login()
    {
        try {
            $this->unasClient->login();
        } catch (LoginException $e) {
            echo 'Login failed: ' . $e->getMessage();
        }
    }

    public function uploadProductData($xmlData)
    {
        $method = 'setProduct';
        
        $response = $this->unasClient->apiCall($method, $xmlData, 'Products');

        if ($response->getStatusCode() === 200) {
            echo "Product data uploaded successfully!\n";
        } else {
            echo "Error uploading product data: " . $response->getError() . "\n";
            var_dump($response); 

            $responseContent = $response->getResponseContent();  
            $jsonResponse = json_decode($responseContent, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                echo "API Response (JSON):\n";
                print_r($jsonResponse);
            } else {
                echo "Non-JSON response: " . $responseContent;
            }
        }
    }


}
