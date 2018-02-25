<?php
class TiendaDB
{
  public $conn;
  private $servername;
  private $username;
  private $password;
  private $dbname;

  function __construct() {
    $servername = getenv("SERVERNAME");
    $username = getenv("DB_USER");
    $password = getenv("DB_PASS");
    $dbname =  getenv("DB_NAME");
  }

  public function getConnection() {
    $this->conn = null;
    try {
      $this->conn = new PDO("mysql:host=" . $this->servername . ";dbname=" . $this->dbname, $this->username, $this->password);
      $this->conn->exec("set names utf8");
    } catch(PDOException $exception){
      echo "Connection error: " . $exception->getMessage();
    }
    return $this->conn;
  }
}
?>
