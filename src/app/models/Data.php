<?php

namespace app\models;

use app\core\Database;

/**
 * Class Data.
 * Works with database, check and save data.
 * @package app\models
 */
class Data
{
    private $conn;

    /**
     * Data constructor.
     */
    public function __construct()
    {
        //Connection to DB
        $this->conn = new Database();
    }

    public function getParsedLinks()
    {
        return $this->conn->query("SELECT link FROM parsered_links");
    }

    public function setParsedLink($link)
    {
        $this->conn->query("INSERT INTO parsered_links (link) VALUES (?)", [$link]);
    }

    public function checkAd($ad)
    {
        return $executeQuery = $this->conn->query("SELECT id FROM ad WHERE idAnuncio = ?", [$ad])[0];
    }

    public function saveAd($ad)
    {
        $id = self::checkAd($ad);

        if ($id == null) {
            $executeQuery = $this->conn->query("INSERT INTO ad (idAnuncio) VALUES (?)", [$ad]);
            if ($executeQuery) {
                return $this->conn->lastInsertId();
            }
            return false;
        } else {
            return $id;
        }
    }


    /**
     * Checking the existence of a question.
     * @param string $question
     * @return int|null
     */
    public function checkQueE($question)
    {
        return $executeQuery = $this->conn->query("SELECT id FROM question WHERE text = ?", [$question])[0]['id'];
    }

    /**
     * Saving the question; return inserted ID or false.
     * @param string $question
     * @return false|int
     */
    public function saveQue($question)
    {
        $id = self::checkQueE($question);

        if ($id == null) {
            $executeQuery = $this->conn->query("INSERT INTO question (text) VALUES (?)", [$question]);
            if ($executeQuery) {
                return $this->conn->lastInsertId();
            }
            return false;
        } else {
            return $id;
        }
    }

    /**
     * Checking the existence of an answer.
     * @param string $answer
     * @return int|null
     */
    public function checkAnsE($answer)
    {
        return $executeQuery = $this->conn->query("SELECT id FROM answer WHERE text = ?", [$answer])[0]['id'];
    }

    /**
     * Saving the answer; return inserted ID or false.
     * @param string $answer
     * @param int $length
     * @return bool|int
     */
    public function saveAns($answer, $length)
    {
        $id = self::checkAnsE($answer);

        if ($id == null) {
            $executeQuery = $this->conn->query("INSERT INTO answer (text, length) VALUES (?,?)", [$answer, $length]);
            if ($executeQuery) {
                return $this->conn->lastInsertId();
            }
            return false;
        } else {
            return $id;
        }
    }

    /**
     * Checking the existence of an answer to a question.
     * @param int $idQue
     * @param int $idAns
     * @return int|null
     */
    public function checkAnsQueE($idQue, $idAns)
    {
        return $executeQuery = $this->conn->query("SELECT id FROM answerQuestion WHERE idQuestion=? and idAnswer=?",
            [$idQue, $idAns])[0]['id'];
    }

    /**
     * Saving answer on the question.
     * @param int $idQue
     * @param int $idAns
     */
    public function saveAnsQue($idQue, $idAns)
    {
        if (self::checkAnsQueE($idQue, $idAns) == null) {
            $this->conn->query("INSERT INTO answerQuestion (idQuestion, idAnswer) VALUES (?,?)", [$idQue, $idAns]);
        }
    }
}