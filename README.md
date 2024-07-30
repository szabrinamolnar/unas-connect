# unas-connect

## API client

### Config options

| Option | Type | Default | Description |
| -- | -- | -- | -- |
| apiKey | string | - | UNAS api key |
| curlTimeout | int | 120 | Override curl timeout |
| curlConnectTimeout | int | 120 | Override curl connect timeout |

### Simple api request

```php
<?php

require 'vendor/autoload.php';

use UnasOnline\UnasConnect\Api\Client;

$apiClient = new Client([
    'apiKey' => 'REPLACE_WITH_API_KEY',
]);

$response = $apiClient->apiCall('getOrderStatus');
```

### ClientCacheInterface

Implement `UnasOnline\UnasConnect\Api\ClientCacheInterface` to cache API configuration:

```php
<?php

require 'vendor/autoload.php';

use UnasOnline\UnasConnect\Api\ClientCacheInterface;

class ClientCacheExample
{
    public function __construct(string $shopId)
    {
        $this->shopId = $shopId;
    }

    public function cacheUnasApiLogin(array $data): void
    {
        // save login data for shop
        // ...
    }

    public function restoreUnasApiLogin(): ?array
    {
        // restore login data
        // ...
    }
}
```

### API request with cache

```php
<?php

require 'vendor/autoload.php';

use UnasOnline\UnasConnect\Api\Client;

$cache = new ClientCacheExample('123');
$apiClient = new Client([
    'apiKey' => 'REPLACE_WITH_API_KEY',
], $cache);

$response = $apiClient->apiCall('getOrderStatus');
```

## App integration

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use UnasOnline\UnasConnect\AppClient;

$appClient = new AppClient('app-id', 'app-url', 'app-secret');

// verify unas app request
$requestHmac = $appClient->generateHmac($_POST('shop_id'), $_POST('time'), $_POST('token'));
if ($requestHmac == $_POST['hmac']) {
    // verified
}

// request api key for shop
$apiKey = $appClient->requestApiKey('shop_id', 'time', 'token');

```
