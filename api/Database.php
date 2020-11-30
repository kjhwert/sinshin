<?php


class Database
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "Ajdajd8689!@";
    private $database = "sinshin";
    private $db = null;
    private static $_instance; //The single instance

    private function __construct(){

        try {

            $dsn = "mysql:host={$this->servername};port=3306;dbname={$this->database};charset=utf8";
            $this->db = new PDO($dsn, $this->username, $this->password);
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {

            echo $e->getMessage();

        };

    }

    /** 싱글톤 방식으로 Database 클래스를 리턴한다. */
    public static function getInstance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /** DB정보를 return한다. */
    public function getDatabase() {
        return $this->db;
    }
}
