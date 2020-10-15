<?php

namespace app\models;

/**
 * Class DataCache.
 * Works with redis, cache and returns proxy, links.
 * @package app\models
 */
class DataCache
{
    private $redis;
    private $config;

    /**
     * DataCache constructor.
     */
    public function __construct()
    {
        $this->redis = new \Redis();
        $this->config = require __DIR__ . '/../config/redis_config.php';
    }

    public function checkList($key)
    {
        $this->redis->connect($this->config['host'], $this->config['port']);
        return $this->redis->lLen('link');
    }

    /**
     * Cache proxy.
     * @param $proxy
     */
    public function cacheProxy($proxy)
    {
        $this->redis->connect($this->config['host'], $this->config['port']);
        $this->redis->rPush('proxy', $proxy);
    }

    /**
     * Return proxy-address
     * @return mixed
     */
    public function getProxy()
    {
        $this->redis->connect($this->config['host'], $this->config['port']);
        return $this->redis->lPop('proxy');
    }

    /**
     * Cache link.
     * @param $type
     * @param $link
     */
    public function cacheLink($link)
    {
        $this->redis->connect($this->config['host'], $this->config['port']);
        $this->redis->rPush('link', $link);
    }

    /**
     * Return link.
     * @return mixed
     */
    public function getLink()
    {
        $this->redis->connect($this->config['host'], $this->config['port']);
        return $this->redis->lPop('link');
    }

    /**
     * Flush all
     */
    public function flushAll()
    {
        $this->redis->connect($this->config['host'], $this->config['port']);
        $this->redis->flushAll();
    }


}