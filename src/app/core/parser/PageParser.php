<?php


namespace app\core\parser;

use app\core\Content;
use app\models\Data;
use app\models\DataCache;
use GuzzleHttp\Client;

/**
 * Class LetterPageParser.
 * Works with letters-pages, "seite"-pages; caches included links.
 * @package app\core\parser
 */
class PageParser implements IParser
{
    private $content;
    private $dataCache;
    private $model;

    /**
     * LetterPageParser constructor.
     */
    public function __construct()
    {
        $this->content = new Content();
        $this->dataCache = new DataCache();
        $this->model = new Data();
    }

    /**
     * @param string $uri
     * @param Client $client
     * @return bool
     */
    public function parse($uri, Client $client)
    {
        if ((!$uri) || ($uri == '') || ($uri == null)) {
            return true;
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
        $document = false;
        $document = $this->content->getContent($uri, $client, $this->model);
        if ($document == '') {
            return '*';
        }
        if ($document == 404) {
            return 404;
        }
        if ($document == null) {
            error_log("[" . date("j F Y G:i:s") . "] Content null: " . $uri . "\n", 3,
                __DIR__ . "/../../../logs/logfile.log");
            return null;
        }
        if ($document == false) {
            error_log("[" . date("j F Y G:i:s") . "] Content false: " . $uri . "\n", 3,
                __DIR__ . "/../../../logs/logfile.log");
            return false;
        }
        if ($document != false) {
            $data = $document->find('.lis_ting_Ad');
            if ((!$data) || ($data == null)) {
                error_log("[" . date("j F Y G:i:s") . "] Successfully parse: " . $uri . " : no ads there\n", 3,
                    __DIR__ . "/../../../logs/logfile.log");
                return true;
            }

            foreach ($data as $a) {
                $idAnuncio = $a->attr('data-idanuncio');


                //url
                $href = str_replace("abrirAnuncio(event, this, \"", "", $a->xpath('//a')[0]->attr('onmousedown'));
                $href = str_replace('")', '', $href);
                if (stripos($href, 'http') === false) {
                    $href = 'http://casas.mitula.mx' . $href;
                }
                $ad = ['url' => $href];

                //site
                $site = $a->attr('data-nombreagencia');
                $ad += ['source' => $site];

                //name
                $name = $a->xpath("//div[contains(@class, 'title')]")[0]->text();
                $ad += ['name' => $name];

                //price
                $price = $a->find("div.adPrice div")[0]->text();
                $ad += ['price' => $price];

                //location
                $lat = $a->find('div.adFooter div.display--flex div.goToMap')[0];
                if ($lat) {
                    $lat = $lat->attr('data-lat');
                } else {
                    $lat = '';
                }
                $lon = $a->find('div.adFooter div.display--flex div.goToMap')[0];
                if ($lon) {
                    $lon = $lon->attr('data-lon');
                } else {
                    $lon = '';
                }
                $ad += ['location' => ['latitude' => $lat, 'longitude' => $lon]];

                $a->find('div.adDescription h5 span')[1]->text() === "Venta" ? $op = 'sale' : $op = 'rent';
                $ad += ['operationType' => $op];

                $subtypology = '';
                preg_match("|tipo\-[a-zA-Z]{1,}\/|", $uri, $subtypology);
                $subtypology = str_replace("tipo-", "", $subtypology[0]);
                $subtypology = str_replace("/", "", $subtypology);
                $typology = $tipos[$subtypology];
                $ad += ['typology' => $typology];
                $ad += ['subtypology' => $subtypology];

                $state = $a->xpath("//meta[contains(@itemprop, 'addressRegion')]")[0];
                if ($state) {
                    $state = $state->attr('content');
                } else {
                    $state = trim(explode(",", strip_tags($a->find('div.adDescription h5 span')[0]->text()))[1]);
                }
                $ad += ['state' => $state];

                $locality = $a->xpath("//meta[contains(@itemprop, 'addressLocality')]")[0];
                if ($locality) {
                    $locality = $locality->attr('content');
                } else {
                    $locality = explode(",", strip_tags($a->find('div.adDescription h5 span')[0]->text()))[0];
                }
                $ad += ['locality' => $locality];

                $rooms = $a->xpath("//meta[contains(@itemprop, 'numberOfRooms')]")[0];
                if ($rooms) {
                    $rooms = $rooms->attr('content');
                } else {
                    $rooms = '';
                }
                $ad += ['rooms' => $rooms];

                preg_match("|'bathrooms':[0-9]{1,}|", $a->attr('onmouseenter'), $bathrooms);
                if ($bathrooms) {
                    $bathrooms = str_replace("'bathrooms':", '', $bathrooms[0]);
                } else {
                    $bathrooms = '';
                }
                $ad += ['bathrooms' => $bathrooms];

                $sqm = $a->xpath("//meta[contains(@itemprop, 'floorSize')]")[0];
                if ($sqm) {
                    $sqm = $sqm->attr('content');
                }
                $ad += ['sqm' => $sqm];

                $description = $a->xpath("//div[contains(@class, 'adTeaser ellipsis')]")[0]->text();
                $ad += ['description' => $description];

                if (preg_match("|alberca|", $description) || preg_match("|piscina|", $description)) {
                    $swimmingPool = 'true';
                } else {
                    $swimmingPool = 'false';
                }
                $ad += ['swimmingPool' => $swimmingPool];

                if (preg_match("|jardin|", $description) || preg_match("|jardín|", $description)) {
                    $garden = 'true';
                } else {
                    $garden = 'false';
                }
                $ad += ['garden' => $garden];

                if (preg_match("|cochera|", $description) || preg_match("|garaje|", $description)) {
                    $garage = 'true';
                } else {
                    $garage = 'false';
                }
                $ad += ['garage' => $garage];

                if ($this->model->checkAd($idAnuncio) == null) {
                    $this->model->saveAd($idAnuncio);
                    if (!file_exists('output/' . $locality)) {
                        mkdir('output/' . $locality, 0777);
                    }
                    if ($locality) {
                        $fp = fopen('output/' . $locality . '/' . $idAnuncio . '.json', 'w+');
                    } else {
                        $fp = fopen('output/None/' . $idAnuncio . '.json', 'w+');
                    }
                    fwrite($fp, json_encode($ad, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    fclose($fp);
                }
            }

            if ($document->xpath("//li[contains(@class, 'activated')]/following::li")[0]) {
                if ($document->xpath("//li[contains(@class, 'activated')]/following::li")[0]->attr('value') != '') {
                    $Pag = '';
                    preg_match("|[0-9]{1,}$|", $uri, $Pag);
                    $Pag = intval($Pag[0]) + 1;
                    $new_uri = preg_replace("|[0-9]{1,}$|", $Pag, $uri);
                    $this->dataCache->cacheLink($new_uri);
                    error_log("[" . date("j F Y G:i:s") . "] Added to queue: " . $new_uri . "\n", 3,
                        __DIR__ . "/../../../logs/logfile.log");
                }
            }


            error_log("[" . date("j F Y G:i:s") . "] Successfully parse: " . $uri . " with " . count($data) . " ads\n", 3,
                __DIR__ . "/../../../logs/logfile.log");
            $this->model->setParsedLink($uri, '200');
            return true;
        } else {
            return false;
        }


    }
}
