<?php
 
 class ConnectionDB {
    const DB_SERVER = "127.0.0.1";
    const DB_USER = "root";
    const DB_PASSWORD = "";
    const DB = "yomanejo";
    private $db = NULL;
    private $mysqli = NULL;
    
    /*
     *  Connect to Database
    */
    public function getConnection(){
       $this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
       return $this->mysqli;
    }
}
?>