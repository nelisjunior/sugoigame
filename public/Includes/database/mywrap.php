<?php
declare(strict_types=1);

// Modern PHP 8.x error reporting - show all errors including deprecated
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once(__DIR__ . '/../../Constantes/requires.php');
require_once('mywrap_result.php');
require_once('mywrap_connection.php');