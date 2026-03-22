<?php
// config.php
session_start();

$host = 'localhost';
$dbname = 'crm_db';
$user = 'root';
$pass = ''; // Por padrão, XAMPP/WAMP usam senha em branco
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Caso o banco não exista ainda, tenta conectar sem dbname
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        $dsn_no_db = "mysql:host=$host;charset=$charset";
        try {
            $pdo = new PDO($dsn_no_db, $user, $pass, $options);
        } catch (\PDOException $e2) {
             throw new \PDOException($e2->getMessage(), (int)$e2->getCode());
        }
    } else {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}
?>
