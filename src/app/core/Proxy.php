<?php


namespace app\core;

use app\models\DataCache;
use GuzzleHttp\Client;

/**
 * Class Proxy.
 * Works with proxy-addresses, proxy-lists.
 * @package app\core
 */
class Proxy
{
    private $client;
    private $dataCache;

    /**
     * Proxy constructor.
     */
    public function __construct()
    {
        $this->client = new Client(OPTION);
        $this->dataCache = new DataCache();
    }

    /**
     * Creates new proxy-list.
     */
    public function newProxyList()
    {
        $config = require __DIR__.'/../config/proxy_config.php';
        $proxyList = false;
        while ($proxyList == false) {
            $proxyList = Content::getProxyContent($config['proxyLink'], $this->client);
        }

        $proxyList = json_decode($proxyList, true);
        foreach ($proxyList as $proxy) {
            $this->dataCache->cacheProxy($proxy['ip'].':'.$proxy['port']);
        }
    }

    /**
     * Checking proxy for availability.
     * @param string $ip
     * @param string $port
     * @return bool
     */
    public function checkProxy($ip, $port)
    {
        $errorNumber = '';
        $errorMessage = '';
        $timeout = 10;
        if (@fsockopen($ip, $port, $errorNumber, $errorMessage, $timeout)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get proxy-address from proxy-list.
     * @return string
     */
    public function getProxy()
    {
        $proxiesExit = $this->dataCache->getProxy();
        if ($proxiesExit == false) {
            self::newProxyList();
            $proxiesExit = $this->dataCache->getProxy();
        }
        $proxy = explode(':', $proxiesExit);
        while (self::checkProxy($proxy['ip'], $proxy['port'])) {
            if ($proxiesExit == false) {
                self::newProxyList();
                $proxiesExit = $this->dataCache->getProxy();
            }
            $proxy = explode(':', $proxiesExit);
        }
        return implode(':', $proxy);
    }

    /**
     * @param string $proxy
     */
    public function pushProxy($proxy)
    {
        $this->dataCache->cacheProxy($proxy);
    }
}