<?php
session_start();
// Inclure l'autoloader généré par Composer
require __DIR__ . '/../vendor/autoload.php';

// Utilisation de notre class Router
use App\Router\Router;
use App\App\App;

App::DB_Connect();

Router::parseRoute();