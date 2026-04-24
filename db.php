<?php

class DATA_BASE {

    private $host = "localhost";
    private $user = "root";
    private $pass = "123456";
    private $db   = "php_project";
    private static $instance;

    private $conn;

    private function __construct() {
        $this->conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->db
        );

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
   
    //  GET SINGLE INSTANCE 
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DATA_BASE();
        }
        return self::$instance;
    }
   // INSERT
public function insert($table, $columns, $values) {
    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
    $this->conn->query($sql);
    return $this->conn->insert_id; 

    }

    // UPDATE
    public function update($table, $set, $condition) {
        $sql = "UPDATE $table SET $set WHERE $condition";
        return $this->conn->query($sql);
    }

    // DELETE
    public function delete($table, $condition) {
        $sql = "DELETE FROM $table WHERE $condition";
        return $this->conn->query($sql);
    }

     // select one
    public function select($table, $condition) {
        $sql = "SELECT * FROM $table WHERE $condition";
        return $this->conn->query($sql);
    }

    // SELECT ALL
    public function selectAll($table,$condition=1) {
        $sql = "SELECT * FROM $table WHERE $condition";
        return $this->conn->query($sql);
    }
}

?>