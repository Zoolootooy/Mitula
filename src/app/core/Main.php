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
        if (!file_exists('output/None')) {
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

        $prices = [
            0,
            100000,
            200000,
            300000,
            400000,
            500000,
            600000,
            600000,
            800000,
            900000,
            1000000,
            2000000,
            3000000,
            4000000,
            5000000,
            6000000,
            7000000,
            8000000,
            9000000,
            10000000,
            12500000,
            20000000,
            27500000,
            35000000,
            42500000,
            50000000,
            75000000,
            100000000,
            500000000
        ];
        $localities = file(__DIR__ . '/../localites.txt');

        for ($i = 0; $i < count($localities); $i++) {
            $localities[$i] = str_replace("\n", "", $localities[$i]);
        }


        foreach ($this->data->getParsedLinks() as $a) {
            array_push($this->parsed_links, $a['link']);
        }

//        for ($i = 0; $i < count($localities); $i++) {
//            $nivel2 = str_replace(' ', '+', $localities[$i]);
//            $q = str_replace(' ', '-', $localities[$i]);
//
//            for ($j = 1; $j <= 2; $j++) {
//                for ($k = 0; $k < count($tips); $k++) {
//                    $url_base = "https://casas.mitula.mx/searchRE/orden-0/op-" . $j . "/tipo-" . $tips[$k] . "/precio_min-0/precio_max-10000000/q-" . $q . "/pag-1";
//                    if (! in_array($url_base, $this->parsed_links)) {
//                        $this->dataCache->cacheLink($url_base);
//                    } else {
//                        error_log("[" . date("j F Y G:i:s") . "] Is already parsed: ". $url_base . "\n", 3,
//                            __DIR__ . "/../../logs/logfile.log");
//                    }
//                }
//            }
//        }
        for ($i = 0; $i < count($localities); $i++) {
            $nivel2 = str_replace(' ', '+', $localities[$i]);
            $q = str_replace(' ', '-', $localities[$i]);

            for ($j = 1; $j <= 2; $j++) {
                for ($k = 0; $k < count($tips); $k++) {
                    for ($l = 0; $l < count($prices) - 1; $l++) {
                        $priceMin = $prices[$l];
                        $priceMax = $prices[$l + 1] + 1;
                        $url_base = "https://casas.mitula.mx/searchRE/orden-0/nivel2-" . $nivel2 . "/op-" . $j . "/tipo-" . $tips[$k] . "/precio_min-" . $priceMin . "/precio_max-" . $priceMax . "/q-" . $q . "/pag-1";
                        if (!in_array($url_base, $this->parsed_links)) {
                            $this->dataCache->cacheLink($url_base);
                        } else {
                            error_log("[" . date("j F Y G:i:s") . "] Is already parsed: " . $url_base . "\n", 3,
                                __DIR__ . "/../../logs/logfile.log");
                        }
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
                        $p = $PageParser->parse($linkData, $this->client);
//                        if (($p == false) || ($p == null)) {
//                            $this->dataCache->cacheLink($linkData);
//                            error_log("[" . date("j F Y G:i:s") . "] Returned " . $linkData . "\n", 3,
//                                __DIR__ . "/../../logs/logfile.log");
//
//                        }
                        exit();

                    default:
                        $this->childsPid[] = $pid;
                        break;
                }
            }
        }
    }
}