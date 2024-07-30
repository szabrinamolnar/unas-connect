<?php

namespace UnasOnline\UnasConnect\Api;

interface ClientCacheInterface
{
    public function cacheUnasApiLogin(array $data): void;
    public function restoreUnasApiLogin(): ?array;
}
