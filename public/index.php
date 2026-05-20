<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Remove ALL limits before anything loads
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '0');
ini_set('max_input_time', '-1');
ini_set('post_max_size', '0');
ini_set('upload_max_filesize', '0');
ini_set('max_file_uploads', '10000');
ini_set('enable_post_data_reading', '0');

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Handle the request
$app->handleRequest(Request::capture());
