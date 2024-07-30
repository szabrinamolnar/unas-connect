<?php

namespace UnasOnline\UnasConnect;

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
