<?php

namespace UnasOnline\UnasConnect\Api;

class SobFeed
{
    
    private static $endpointFeedMap     = '/synklink/feed/map/{synklink_key}/json?p360=1&lang=hu';
    private static $endpointCategories  = '/synklink/category/list/{synklink_key}/json?p360=1&lang=hu';
    private static $endpointBrands      = '/synklink/brand/list/{synklink_key}/json?p360=1&lang=hu';
    private static $endpointProductData = '/synklink/product/data/{synklink_key}/{brand_id}/json?lang=hu&p360=1"';
    private static $endpointBrandProps = '/synklink/brand/properties/{synklink_key}/{brand_id}/json?lang=hu&p360=1';
    
    public static $hostUrl = 'https://sob.hp.localnet';
    public static $synklinkKey = 'arBW5ucRGs2qtecJd28cyJW1p18kVBXi';
    
    public function __construct()
    {
        echo self::$hostUrl;
        echo self::$synklinkKey;
    }
    /**
     * 
     * @return static
     */
    public static function get()
    {
        return new static();
    }
    
    /**
     * 
     * @return array
     */
    public function readMap()
    {
        return $this->read(self::$endpointFeedMap);
    }
    
    /**
     * 
     * @return array
     */
    public function readCategories()
    {
        return $this->read(self::$endpointCategories);
    }
    
    /**
     * 
     * @return array
     */
    public function readBrands()
    {
        return $this->read(self::$endpointBrands);
    }

    /**
     *  
     * @param int $brandId
     * @return array
     */
    public function readProductData($brandId)
    {
        $endpoint = str_replace('{brand_id}', $brandId, self::$endpointProductData);
        return $this->read($endpoint);
    }
    
    /**
     *  
     * @param int $brandId
     * @return array
     */
    public function readBrandProperties($brandId)
    {
        $endpoint = str_replace('{brand_id}', $brandId, self::$endpointBrandProps);
        return $this->read($endpoint);
    }
    
    /**
     * 
     * @param string $endpoint
     * @return object|array
     * @throws \Exception
     */
    public function read($endpoint)
    {    
        $responseBody = $this->readRemote($endpoint);

        $responseJson = json_decode($responseBody, true);
        $jsonError = json_last_error();
        
        if (!empty($jsonError)) {  
            throw new \Exception('JSON error ('.$jsonError.'): '.json_last_error_msg());  
        }

        return $responseJson;
    }

    
    /**
     * 
     * @param string $endpoint
     * @throws \Exception
     * @return string
     */
    private function readRemote($endpoint)
    {
        $url = self::$hostUrl . str_replace('{synklink_key}', self::$synklinkKey, $endpoint);

        echo "URL: " . $url . "\n";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        $response = curl_exec($curl);
        if( $response === false)
        {
            throw new \Exception('ERROR CURL: #'.(string)curl_errno($curl).curl_error($curl));
        }

        $info = curl_getinfo($curl);
        curl_close($curl);

        $statusCode = $info['http_code'];
        if( $statusCode != 200 )
        {
            throw new \Exception('HTTP '.$info['http_code']);
        }
        
        return substr($response, $info['header_size']);
    }
}