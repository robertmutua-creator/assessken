<?php

use Dotenv\Dotenv;
use Src\Models\Database;

require_once __DIR__ . '/../vendor/autoload.php';


$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $db = Database::connect();

    echo "Database connection successful.\n";
} catch (Throwable $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
