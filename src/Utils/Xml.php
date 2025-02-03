<?php

namespace UnasOnline\UnasConnect\Utils;

use SimpleXMLElement;

class Xml
{
    /**
     * Recursively convert xml to php array
     *
     * @return array
     */
    public static function simpleXmlToArray($xml)
    {
        $array = (array)$xml;

        if (count($array) === 0) {
            return [json_encode($xml)];
        }

        foreach ($array as $key => $value) {
            if ((!is_object($value) || strpos(get_class($value), 'SimpleXML') === false) && !is_array($value)) {
                continue;
            }
            $array[$key] = self::simpleXmlToArray($value);
        }

        return (array)$array;
    }

    /**
     * Recursively convert array to XML
     *
     * @return string
     */
    public static function arrayToXml($data, $rootElement = 'Products', $xml = null)
    {
        if ($xml === null) {
            $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" ?><$rootElement></$rootElement>");
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                self::arrayToXml($value, $key, $xml->addChild($key));
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }

        return $xml->asXML();
    }

    public static function convertArrayStructure($product) 
    {
            $xmlArray['Product'][] = [
                'Action' => 'add',
                'Statuses' => [
                    'Status' => [
                        'Type' => 'base',
                        'Value' => '2'
                    ]
                ],
                'Sku' => $product['product_id'],
                'Name' => $product['name'],
                'Unit' => 'db',
                'Categories' => [
                    'Category' => [
                        'Id' => '1',
                        'Name' => 'Világítástechnika',
                        'Type' => 'base'
                    ]
                ],
                'Prices' => [
                    'Price' => [
                        'Type' => 'normal',
                        'Net' => $product['price'],
                        'Gross' => $product['msrp']
                    ]
                ],
                'Images' => [
                    'Image' => array_map(fn($url) => ['Url' => $url], $product['images'])
                ],
                'Properties' => array_reduce($product['properties'], function ($carry, $item) {
                    $carry[$item['name']] = $item['value'];
                    return $carry;
                }, [])
            ];


        return $xmlArray;
    }
    
}

