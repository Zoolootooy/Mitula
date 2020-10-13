<?php


namespace app\core;

use DiDom\Document;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use app\models\DataCache;
use http\Exception;
use function GuzzleHttp\json_decode;

/**
 * Class Content.
 * @package app\core
 */
class Content
{
    private $proxy;

    public function __construct()
    {
        $this->proxy = new Proxy();
    }

    /**
     * @param $link
     * @param Client $client
     * @return Document|false
     */
    public function getContent($link, Client $client)
    {
        $proxy = $this->proxy->getProxy();
        try {
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
            if ($e->getResponse() !== null) {
                if ($e->getResponse()->getStatusCode() == '404') {
                    error_log("[" . date("j F Y G:i:s") . "] Failed to parse: " . $link . " Error 404\n", 3,
                        __DIR__ . "/../../logs/logfile.log");
                    return 404;
                } else {
                    error_log("[" . date("j F Y G:i:s") . "] Failed to parse: " . $link . " with " . $proxy . "\n", 3,
                        __DIR__ . "/../../logs/logfile.log");
                    return false;
                }
            } else {
                error_log("[" . date("j F Y G:i:s") . "] Failed to parse: " . $link . " with  |".$e->getResponse()."|\n", 3,
                    __DIR__ . "/../../logs/logfile.log");
                return false;
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