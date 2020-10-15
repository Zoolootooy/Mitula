<?php


namespace app\core;

use DiDom\Document;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use app\models\DataCache;
use app\models\Data;
use http\Exception;
use function GuzzleHttp\json_decode;

/**
 * Class Content.
 * @package app\core
 */
class Content
{
    private $proxy;
    private $dataCache;

    public function __construct()
    {
        $this->proxy = new Proxy();
        $this->dataCache = new DataCache();
    }

    /**
     * @param $link
     * @param Client $client
     * @param Data $data
     * @return Document|false|int|null
     */
    public function getContent($link, Client $client, Data $data)
    {
        if ($link != null) {
            $proxy = $this->proxy->getProxy();
            try {
//            $content = $client->get($link)->getBody()->getContents();
                $content = $client->get($link, ['proxy' => $proxy])->getBody()->getContents();
                $this->proxy->pushProxy($proxy);
                error_log("[" . date("j F Y G:i:s") . "] Successfully parse: " . $link . " with " . $proxy . "\n", 3,
                    __DIR__ . "/../../logs/logfile.log");
                if ($content != '') {
                    return new Document($content);
                } else {
                    return false;
                }
            } catch (RequestException $e) {
                if ($e->getResponse() != null) {
                    if ($e->getResponse()->getStatusCode() == '404') {
                        error_log("[" . date("j F Y G:i:s") . "] Failed to parse: " . $link . " Error " . $e->getResponse()->getStatusCode() . "\n", 3,
                            __DIR__ . "/../../logs/logfile.log");
                        $data->setParsedLink($link, '404');
                        return 404;
                    } else {
                        error_log("[" . date("j F Y G:i:s") . "] Failed to parse: " . $link . " with " . $proxy . " status code = " .
                            $e->getResponse()->getStatusCode() . "\n", 3, __DIR__ . "/../../logs/logfile.log");
                        error_log("[" . date("j F Y G:i:s") . "] Returned: " . $link . "\n", 3, __DIR__ . "/../../logs/logfile.log");
                        $this->dataCache->cacheLink($link);
                        return false;
                    }
                } else {
                    error_log("[" . date("j F Y G:i:s") . "] Failed to parse: " . $link . " content = null \n", 3, __DIR__ . "/../../logs/logfile.log");
                    error_log("[" . date("j F Y G:i:s") . "] Returned: " . $link . "\n", 3, __DIR__ . "/../../logs/logfile.log");
                    $this->dataCache->cacheLink($link);
                    return null;
                }
            }
        }
    }

    /**
     * @param $link
     * @param Client $client
     * @return false|string\
     */
    public static function getProxyContent($link, Client $client)
    {
        try {
            $content = $client->get($link)->getBody()->getContents();
            error_log("[" . date("j F Y G:i:s") . "] Proxy list received successfully \n", 3,
                __DIR__ . "/../../logs/logfile.log");
            return $content;
        } catch (RequestException $e) {
            error_log("[" . date("j F Y G:i:s") . "] Failed to get proxy list \n", 3,
                __DIR__ . "/../../logs/logfile.log");
            sleep(20);
            return false;
        }
    }
}