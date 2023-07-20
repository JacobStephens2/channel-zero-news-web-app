<?php

    require_once '../private/initialize.php';

    header('Content-Type: application/json');

    $response = new stdClass;

    $numberOfSubmissionsResult = query("SELECT * FROM tblResponses WHERE partner IS NOT NULL;");
    
    $response->numberOfSubmissions = $numberOfSubmissionsResult->num_rows;
    
    $numberOfNamesResult = query("SELECT * FROM tblResponses;");
    
    $response->numberOfNames = $numberOfNamesResult->num_rows;

    foreach ($numberOfNamesResult as $name) {
        $response->names[] = $name['name'];
    } 

    echo json_encode($response);

?>