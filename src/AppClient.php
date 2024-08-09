<?php

namespace UnasOnline\UnasConnect;

use UnasOnline\UnasConnect\Utils\Arrays;

class AppClient
{
    private string $unasAppId;
    private string $unasAppUrl;
    private string $unasAppSecret;
    
    public function __construct(string $unasAppId, string $unasAppUrl, string $unasAppSecret)
    {
        $this->unasAppId = $unasAppId;
        $this->unasAppUrl = $unasAppUrl;
        $this->unasAppSecret = $unasAppSecret;
    }

    /**
     * Verify unas app request data
     *
     * @param string $shop_id
     * @param string $time
     * @param string $token
     * @param string $hmac
     * @return true|string true if request is verified, error message on fail
     */
    public function verifyRequest(string $shop_id, string $time, string $token, string $hmac): true|string
    {
        if (empty(trim($hmac))) {
            return 'empty hmac';
        }

        if ($this->generateHmac($shop_id, $time, $token) !== $hmac) {
            return 'invalid hmac';
        }

        if (!$this->verifyTime($time)) {
            return 'timeout';
        }

        if (!self::verifyHeaders()) {
            return 'invalid referer';
        }

        return true;
    }

    /**
     * Generate HMAC to verify UNAS request
     *
     * @param string $shop_id
     * @param string $time
     * @param string $token
     * @return string
     */
    public function generateHmac($shop_id, $time, $token): string
    {
        $query = http_build_query(compact('shop_id', 'time', 'token'));
        return hash_hmac('sha256', $query, $this->unasAppSecret);
    }

    /**
     * Verify timestamp
     *
     * @param string|int $time a valid unix timestamp
     * @return bool true if $time is newer than 120 seconds
     */
    public static function verifyTime(string|int $time): bool
    {
        $timestamp = (int)$time;
        $currentTime = time();
        $difference = $currentTime - $timestamp;

        return $difference < 120;
    }

    /**
     * Verify HTTP headers
     */
    public static function verifyHeaders(): bool
    {
        $headers = self::getHttpHeaders();
        if ($headers === false) {
            return false;
        }

        $accept = [
            'https://shop.unas.hu/',
            'https://shop.unas.eu/'
        ];

        $referer = Arrays::get($headers, 'Referer', '');

        return in_array($referer, $accept) || empty($referer);
    }

    /**
     * Get HTTP headers
     */
    protected static function getHttpHeaders(): false|array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        } elseif (function_exists('apache_request_headers')) {
            return apache_request_headers();
        } else {
            return false;
        }
    }

    /**
     * Request API key from UNAS
     *
     * @param string $shop_id
     * @param string $time
     * @param string $token
     *
     * @return array
     */
    public function requestApiKey($shop_id, $time, $token): array
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'UnasConnect',
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_POST => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        $request = compact('shop_id', 'time', 'token');
        $request['hmac'] = $this->generateHmac($shop_id, $time, $token);
        $options[CURLOPT_POSTFIELDS] = http_build_query($request);

        $ch = curl_init($this->unasAppUrl . '/requestApiKey');
        curl_setopt_array($ch, $options);

        $content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if (!empty($error)) {
            throw new \Exception($error);
        }

        return (array)json_decode($content, true);
    }
}
