<?php


namespace app\core;

use app\core\parser\PageParser;
use app\models\Data;
use app\models\DataCache;
use GuzzleHttp\Client;

/**
 * Class Main.
 * @package app\core
 */
class Main
{
    protected $client;
    protected $dataCache;
    protected $data;
    protected $content;
    protected $childsPid = [];
    protected $parsed_links = [];

    /**
     * Main constructor.
     */
    public function __construct()
    {
        $this->client = new Client(OPTION);
        $this->content = new Content();
        $this->dataCache = new DataCache();
        $this->data = new Data();

        $this->dataCache->flushAll();
        if (!file_exists('output/None') ) {
            mkdir('output/None', 0777);
        }
        $tipos = [
            'Ático' => 'flat',
            'Dúplex' => 'flat',
            'Estudio' => 'flat',
            'Penthouse' => 'flat',
            'Flat' => 'flat',
            'Departamento' => 'flat',
            'Bungalow' => 'house',
            'Cabaña' => 'house',
            'Casa' => 'house',
            'Casa En Condominio' => 'house',
            'Finca' => 'house',
            'Hacienda' => 'house',
            'Rancho' => 'house',
            'Villa' => 'house',
            'Quinta' => 'house',
        ];

        $tips = [
            'Ático',
            'Dúplex',
            'Estudio',
            'Penthouse',
            'Flat',
            'Departamento',
            'Bungalow',
            'Cabaña',
            'Casa',
            'Casa En Condominio',
            'Finca',
            'Hacienda',
            'Rancho',
            'Villa',
            'Quinta',
        ];
        $localities = file(__DIR__ . '/../localites.txt');

        for ($i = 0; $i < count($localities); $i++) {
            $localities[$i] = str_replace("\n", "", $localities[$i]);
        }


        foreach ($this->data->getParsedLinks() as $a) {
            array_push($this->parsed_links, $a['link']);
        }

        for ($i = 0; $i < count($localities); $i++) {
            $nivel2 = str_replace(' ', '+', $localities[$i]);
            $q = str_replace(' ', '-', $localities[$i]);

            for ($j = 1; $j <= 2; $j++) {
                for ($k = 0; $k < count($tips); $k++) {
                    $url_base = "https://casas.mitula.mx/searchRE/nivel2-" . $nivel2 . "/orden-0/op-" . $j . "/tipo-" . $tips[$k] . "/precio_min-0/precio_max-10000000/q-" . $q . "/pag-1";
                    if (! in_array($url_base, $this->parsed_links)) {
                        $this->dataCache->cacheLink($url_base);
                    }
                }
            }
        }
    }

    /**
     * Parser's start point.
     */
    public function start()
    {
        while (true) {
            foreach ($this->childsPid as $key => $pid) {
                $result = pcntl_waitpid($pid, $status, WNOHANG);
                if ($result == -1 || $result > 0) {
                    unset ($this->childsPid[$key]);
                }
            }

            if (count($this->childsPid) < PROCESS_LIMIT) {
                $linkData = $this->dataCache->getLink();
                if ($linkData == false && count($this->childsPid) == 0) {
                    exit();
                }

                switch ($pid = pcntl_fork()) {
                    case -1:
                        error_log('Failed to create child process');
                        break;

                    case 0:
                        $PageParser = new PageParser();
                        if ($PageParser->parse($linkData, $this->client) == false){
                            $this->dataCache->cacheLink($linkData);
                            error_log("[" . date("j F Y G:i:s") . "] Returned ". $linkData . "\n", 3,
                                __DIR__ . "/../../logs/logfile.log");
                        }
                        exit();

                    default:
                        $this->childsPid[] = $pid;
                        break;
                }
            }
        }
    }
}