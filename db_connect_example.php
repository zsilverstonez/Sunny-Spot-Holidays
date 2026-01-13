<?php
require __DIR__ . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->load();

$connect = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

if ($connect->connect_error) {
    error_log($connect->connect_error);
    die("Service temporarily unavailable. Please try again later.");
}
