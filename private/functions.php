<?php

function query($query) {
    global $database;
    return $database->query($query);
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function prepare_and_execute($sql, $types = '', $params = []) {
    global $database;

    $stmt = $database->prepare($sql);
    if (!$stmt) {
        return false;
    }
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        return false;
    }

    $result = $stmt->get_result();
    if ($result instanceof mysqli_result) {
        return $result;
    }
    return true;
}
        
?>