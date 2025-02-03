<?php

require __DIR__ . '/../../vendor/autoload.php';


use UnasOnline\UnasConnect\Api\SobFeed;
use UnasOnline\UnasConnect\Api\UnasApi;
use UnasOnline\UnasConnect\Utils\Xml; 

// $dotenv = Dotenv::createImmutable('/var/www/unas-connect');
// $dotenv->load();

// $unasApiKey = getenv('UNAS_API_KEY') ?: die('UNAS_API_KEY is missing in .env file');

$unasApiKey = 'd04fb8476e379e59fbcc1e3e4cd4b77b7f8912e6';

try {
    $sobFeed = new SobFeed();
    $brands = $sobFeed->readBrands();
    
    $allProducts = [];
    foreach ($brands as $brand) {
        $brandId = $brand['id'];
        $products = $sobFeed->readProductData($brandId);
    }
    
    echo "Termékek sikeresen lekérve az SoB-ról!\n";
    
    $unasApi = new UnasApi($unasApiKey, null);
    
    foreach ($products as $product) {
        $productArray = Xml::convertArrayStructure($product);
        
        $unasApi->uploadProductData($productArray);
    }
    
    echo "Termékek sikeresen feltöltve az Unas-ba!";

} catch (Exception $e) {
    echo "Hiba: " . $e->getMessage();
}
