<?php

require_once 'constants.php';
class CacheManager
{
    public function __construct()
    {

    }

    public function saveApiKey(string $apiKey)
    {
        Configuration::updateValue('CARFORAGEST_WEBSERVICE_KEY', $apiKey);
        Cache::store(CACHE_KEY, $apiKey);
    }

    public function deleteApiKey()
    {
        Configuration::updateValue('CARFORAGEST_WEBSERVICE_KEY', '');
        Cache::store(CACHE_KEY, '');
    }

    public function retrieveApiKey(): string
    {
        $apiKey = Cache::retrieve(CACHE_KEY);
        if (!isset($apiKey)) {
            $apiKey = Configuration::get('CARFORAGEST_WEBSERVICE_KEY');
        }

        return $apiKey;
    }
}