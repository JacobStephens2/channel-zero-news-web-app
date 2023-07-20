<?php

function query($query) {
			
    // This function uses PHP's mysqli API for interfacing with MySQL

    // MySQL update, delete, and insert queries via mysqli
    // return bool true if successful, and bool false otherwise
    // Select queries return a mysqli result object.
    global $database;

    return $database->query($query);
    
}

function sanitize($string) {

    global $database;

    return $database->real_escape_string($string);
}
        
?>