<?php

class DATA_BASE_RESULT {
    private $result;
    private $driver;

    public function __construct($result, string $driver) {
        $this->result = $result;
        $this->driver = $driver;
    }

    public function fetch_assoc() {
        if ($this->driver === 'mysqli') {
            return $this->result->fetch_assoc();
        }

        $row = $this->result->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }
}

class DATA_BASE {

    private $host = "localhost";
    private $user = "root";
    private $pass = "123456";
    private $db   = "php_project";
    private static $instance;

    private $conn;
    private $driver;

    private function __construct() {
        if (class_exists('mysqli')) {
            $this->driver = 'mysqli';
            $this->conn = new mysqli(
                $this->host,
                $this->user,
                $this->pass,
                $this->db,
                3307
            );

            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }

            return;
        }

        if (class_exists('PDO')) {
            $this->driver = 'pdo';

            try {
                $this->conn = new PDO(
                    "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
                    $this->user,
                    $this->pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }

            return;
        }

        die("Connection failed: no MySQL driver is enabled in PHP.");
    }
   
    //  GET SINGLE INSTANCE 
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DATA_BASE();
        }
        return self::$instance;
    }

    // ── ADDED: returns the raw connection for prepared statements.
    //    Used only by cart_action.php — no other files need this.
    public function getRawConnection() {
        return $this->conn;
    }

    // INSERT
    public function insert($table, $columns, $values) {
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        $this->runQuery($sql);

        if ($this->driver === 'mysqli') {
            return $this->conn->insert_id;
        }

        return (int)$this->conn->lastInsertId(); 

    }

    // UPDATE
    public function update($table, $set, $condition) {
        $sql = "UPDATE $table SET $set WHERE $condition";
        return $this->runQuery($sql);
    }

    // DELETE
    public function delete($table, $condition) {
        $sql = "DELETE FROM $table WHERE $condition";
        return $this->runQuery($sql);
    }

     // select one
    public function select($table, $condition) {
        $sql = "SELECT * FROM $table WHERE $condition";
        return $this->runSelectQuery($sql);
    }

    // SELECT ALL
    public function selectAll($table,$condition=1) {
        $sql = "SELECT * FROM $table WHERE $condition";
        return $this->runSelectQuery($sql);
    }

    private function runQuery(string $sql) {
        if ($this->driver === 'mysqli') {
            return $this->conn->query($sql);
        }

        return $this->conn->exec($sql);
    }

    private function runSelectQuery(string $sql): DATA_BASE_RESULT {
        if ($this->driver === 'mysqli') {
            return new DATA_BASE_RESULT($this->conn->query($sql), 'mysqli');
        }

        return new DATA_BASE_RESULT($this->conn->query($sql), 'pdo');
    }
}

?>