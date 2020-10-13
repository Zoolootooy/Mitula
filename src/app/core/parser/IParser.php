<?php


namespace app\core\parser;


use app\core\Content;
use app\models\Data;
use GuzzleHttp\Client;

interface IParser
{
    public function parse($uri, Client $client);
}