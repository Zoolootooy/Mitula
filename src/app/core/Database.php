<?php

namespace app\core;

use PDO;

/**
 * Class Database.
 * Works with Mysql database.
 * @package app\core
 */
class Database
{
    private static $pdo;
    private $sQuery;
    private $bConnected = false;
    private $parameters;

    /**
     * Database constructor.
     */
    public function __construct()
    {
        $config = require __DIR__ . '/../config/database_config.php';

        $this->pdo = new PDO(
            'mysql:dbname=' . $config['name'] . ';host=' . $config['host'] . ';port=' . $config['port'] . ';charset=utf8',
            $config['user'],
            $config['password'],
            array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            )
        );
    }

    /**
     * Parse and execution call the query
     *
     * @param string $query Role of query text from model
     * @param array $params Role of query parameter from model
     * @param int $fetchmMode
     *
     * @return array|int|null
     */
    public function query($query, $params = null, $fetchmMode = PDO::FETCH_ASSOC)
    {
        $query = trim($query);
        $rawStatement = explode(" ", $query);
        $this->init($query, $params);
        $statement = strtolower($rawStatement[0]);

        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmMode);
        } else {
            if ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
                return $this->sQuery->rowCount();
            } else {
                return null;
            }
        }
    }

    /**
     * Execute the query
     *
     * @param string $query Role of query text
     * @param array $parameters Role of query parameter
     */
    private function init($query, $parameters)
    {
        if (!$this->bConnected) {
            $this->__construct();
        }

        $this->parameters = $parameters;
        $this->sQuery = $this->pdo->prepare($this->buildParams($query, $this->parameters));

        if (!empty($this->parameters)) {
            if (array_key_exists(0, $parameters)) {
                $parametersType = true;
                array_unshift($this->parameters, "");
                unset($this->parameters[0]);
            } else {
                $parametersType = false;
            }
            foreach (array_keys($this->parameters) as $column) {
                $this->sQuery->bindParam($parametersType ? intval($column) : ":" . $column, $this->parameters[$column]);
            }
        }

        $this->sQuery->execute();
        $this->parameters = array();
    }


    /**
     * Combines query text with parameters
     *
     * @param string $query Role of query text
     * @param array $params Role of query parameter
     *
     * @return string
     */
    private function buildParams($query, $params = array())
    {
        if (!empty($params)) {
            $arrayParameterFound = false;

            foreach ($params as $parameterKey => $parameter) {
                if (is_array($parameter)) {
                    $arrayParameterFound = true;
                    $in = "";
                    foreach ($parameter as $key => $value) {
                        $namePlaceholder = $parameterKey . "_" . $key;
                        // concatenates params as named placeholders
                        $in .= ":" . $namePlaceholder . ", ";
                        // adds each single parameter to $params
                        $params[$namePlaceholder] = $value;
                    }
                    $in = rtrim($in, ", ");
                    $query = preg_replace("/:" . $parameterKey . "/", $in, $query);
                    // removes array form $params
                    unset($params[$parameterKey]);
                }
            }
            // updates $this->params if $params and $query have changed
            if ($arrayParameterFound) {
                $this->parameters = $params;
            }
        }

        return $query;
    }

    /**
     * Get the id of the last inserted row
     *
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}