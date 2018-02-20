<?php
class TiendaDB
{
  public $conn;
  private $servername = getenv('SERVERNAME');
  private $username = getenv('DB_USER');
  private $password = getenv('DB_PASS');
  private $dbname =  getenv('DB_NAME');


  public function getConnection(){
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
