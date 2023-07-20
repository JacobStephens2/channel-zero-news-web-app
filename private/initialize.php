<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'environmentVariables.php';

function confirm_db_connect($connection) {
    if($connection->connect_errno) {
        $msg = "Database connection failed: ";
        $msg .= $connection->connect_error;
        $msg .= " (" . $connection->connect_no . ")";
        exit($msg);
    }
}

function db_connect() {
    $connection = new mysqli(DB_HOST, USERNAME, DB_PASSWORD, DB_NAME);
    confirm_db_connect($connection);
    return $connection;
}

$database = db_connect();

require_once 'functions.php';

?>