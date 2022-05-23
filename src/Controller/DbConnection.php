<?php

namespace App\Controller;

use PDO;
use PDOException;

class DbConnection extends PDO
{
    protected static $instance;

    private static $dsn = 'mysql:host=localhost;dbname=sarigato_task_1';
    private static $username = 'root';
    private static $password = '';

    private function __construct() 
    {
        try {
            self::$instance = new PDO(self::$dsn, self::$username, self::$password);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            new DbConnection();
        }

        return self::$instance;
    }
}
