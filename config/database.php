<?php
// config/database.php

class Database {
    private static $instance = null;
    private $connection;
    
    private const HOST = 'localhost';
    private const DB_NAME = 'y923494j_32';
    private const DB_USER = 'y923494j_32';
    private const DB_PASS = 'y923494j_32123';
    private const CHARSET = 'utf8mb4';
    
    private function __construct() {
        $dsn = "mysql:host=" . self::HOST . ";dbname=" . self::DB_NAME . ";charset=" . self::CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        try {
            $this->connection = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            if (getenv('APP_ENV') !== 'production') {
                die("Ошибка подключения к базе: " . htmlspecialchars($e->getMessage()));
            }
            die("Ошибка подключения к базе данных");
        }
    }
    
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
    
    public function __clone() {}
    public function __wakeup() {}
}