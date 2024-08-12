<?php

namespace UnasOnline\UnasConnect\Api;

use DateTime;
use DateTimeZone;
use Spatie\ArrayToXml\ArrayToXml;
use UnasOnline\UnasConnect\Exception\InvalidApiConfigException;
use UnasOnline\UnasConnect\Exception\LoginException;
use UnasOnline\UnasConnect\Exception\MethodNotAllowedException;
use UnasOnline\UnasConnect\Utils\Arrays;

class Client
{
    private ?string $apiKey;
    private ?string $apiToken;
    private string $apiUrl = '';
    protected ClientCacheInterface $cache;
    protected array $permissions = [];
    protected string $subscription;
    protected array $allowedIps = [];
    protected int $curlTimeout;
    protected int $curlConnectTimeout;

    /**
     * @param array<string,mixed> {
     *     apiKey:         string,
     *     apiUrl:         string (default "https://api.unas.eu/shop/")
     *     timeout:        int    (default 120)
     *     connectTimeout: int    (default 120)
     * } $config
     * @param ClientCacheInterface $cache
     *
     * @throws InvalidApiConfigException
     */
    public function __construct(array $config, ClientCacheInterface $cache = null)
    {
        $error = $this->validateConfig($config);
        if (!is_null($error)) {
            throw new InvalidApiConfigException($error);
        }
        
        $this->apiKey = Arrays::get($config, 'apiKey');
        $this->curlTimeout = Arrays::get($config, 'timeout', 120);
        $this->curlConnectTimeout = Arrays::get($config, 'connectTimeout', 120);
        $this->apiUrl = Arrays::get($config, 'apiUrl', 'https://api.unas.eu/shop/');

        if (!is_null($cache)) {
            $this->cache = $cache;

            $response = $this->cache->restoreUnasApiLogin();
            
            if (!is_null($response)) {
                $tz = new DateTimeZone('Europe/Budapest');
                $format = "Y.m.d H:i:s";
                $expireTime = DateTime::createFromFormat($format, $response['Expire'], $tz);
                $expired = $expireTime <= new DateTime('now', $tz);

                if (!$expired) {
                    $this->apiToken = $response['Token'];
                    $this->permissions = $response['Permissions']['Permission'];
                    $this->subscription = $response['Subscription'];
                }
            }
        }
    }

    /**
     * Validate configuration
     *
     * @param array $config config array to validate
     * @return ?string error message, null if config is valid
     */
    protected function validateConfig(array $config): ?string
    {
        if (!array_key_exists('apiKey', $config)) {
            return 'apiKey is required';
        }
        
        return null;
    }

    /**
     * Make API call
     *
     * @param string $method       api method
     * @param array  $xml          array to send as XML
     * @param string $rootElement  root element of the output XML
     * @param bool   $withoutToken make api call without bearer token
     *
     * @return Response
     */
    public function apiCall(string $method, array $xml, string $rootElement = '', bool $withoutToken = false): Response
    {
        if (!$withoutToken && empty($this->apiToken)) {
            $this->login();
        }

        if ($method != 'login' && !in_array($method, $this->getPermissions())) {
            throw new MethodNotAllowedException("method not allowed: $method");
        }
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'UnasConnect',
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => $this->curlConnectTimeout,
            CURLOPT_TIMEOUT => $this->curlTimeout,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_POST => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        $ch = curl_init($this->apiUrl . $method);
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ArrayToXml::convert($xml, $rootElement ?? 'Params'));

        if (!$withoutToken) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->apiToken,
            ]);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        return new Response($statusCode, $content, $error);
    }

    /**
     * Get allowed methods for the instance
     *
     * @return array methods names allowed in api settings
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Get full subscription package name
     *
     * @return string
     */
    public function getSubscription(): string
    {
        return $this->subscription;
    }

    /**
     * Check if subscription has a premium package
     *
     * @return bool true if subscription is premium
     */
    public function isPremium(): bool
    {
        return str_starts_with($this->getSubscription(), 'premium');
    }

    /**
     * Check if subscription has a vip package
     *
     * @return bool true if subscription is vip
     */
    public function isVip(): bool
    {
        return str_starts_with($this->getSubscription(), 'vip');
    }

    /**
     * Perform login request, save token
     *
     * @return array
     *
     * @throws LoginException
     */
    public function login(): array
    {
        $req = [
            'ApiKey' => $this->apiKey,
            'WebshopInfo' => false
        ];

        $response = $this->apiCall('login', $req, 'Params', true)->getResponse();
        if (array_key_exists('error', $response)) {
            throw new LoginException($response['error']);
        }

        $this->apiToken = $response['Token'];
        $this->permissions = $response['Permissions']['Permission'];
        $this->subscription = $response['Subscription'];
        
        if (!empty($this->cache)) {
            $this->cache->cacheUnasApiLogin($response);
        }

        return $response;
    }
}
