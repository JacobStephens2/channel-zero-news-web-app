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

function ensure_response_archive_table_exists() {
    $sql = "
        CREATE TABLE IF NOT EXISTS tblResponseArchive (
            id INT PRIMARY KEY AUTO_INCREMENT,
            archive_batch_id VARCHAR(64) NOT NULL,
            archived_at DATETIME NOT NULL,
            original_response_id INT NULL,
            name VARCHAR(255),
            prompts_id INT NULL,
            partner VARCHAR(255),
            response1 TEXT, response2 TEXT, response3 TEXT, response4 TEXT,
            response5 TEXT, response6 TEXT, response7 TEXT, response8 TEXT,
            submitted_at DATETIME NULL,
            prompt1 TEXT, prompt2 TEXT, prompt3 TEXT, prompt4 TEXT,
            prompt5 TEXT, prompt6 TEXT, prompt7 TEXT,
            INDEX idx_archive_batch (archive_batch_id),
            INDEX idx_archived_at (archived_at)
        )
    ";

    return query($sql) === true;
}

function archive_current_responses() {
    global $database;

    if (!ensure_response_archive_table_exists()) {
        return false;
    }

    $countResult = query("SELECT COUNT(*) AS count FROM tblResponses");
    if (!$countResult) {
        return false;
    }

    $countRow = $countResult->fetch_assoc();
    $rowCount = (int)($countRow['count'] ?? 0);
    if ($rowCount === 0) {
        return 0;
    }

    $archiveBatchId = bin2hex(random_bytes(16));

    $database->begin_transaction();

    try {
        $insertSql = "
            INSERT INTO tblResponseArchive (
                archive_batch_id,
                archived_at,
                original_response_id,
                name,
                prompts_id,
                partner,
                response1,
                response2,
                response3,
                response4,
                response5,
                response6,
                response7,
                response8,
                submitted_at,
                prompt1,
                prompt2,
                prompt3,
                prompt4,
                prompt5,
                prompt6,
                prompt7
            )
            SELECT
                ?,
                NOW(),
                r.id,
                r.name,
                r.prompts_id,
                r.partner,
                r.response1,
                r.response2,
                r.response3,
                r.response4,
                r.response5,
                r.response6,
                r.response7,
                r.response8,
                r.submitted_at,
                p.prompt1,
                p.prompt2,
                p.prompt3,
                p.prompt4,
                p.prompt5,
                p.prompt6,
                p.prompt7
            FROM tblResponses r
            LEFT JOIN tblPrompts p ON r.prompts_id = p.id
        ";

        $inserted = prepare_and_execute($insertSql, "s", [$archiveBatchId]);
        if ($inserted === false) {
            throw new Exception('Failed to archive responses.');
        }

        $deleted = query("DELETE FROM tblResponses");
        if ($deleted !== true) {
            throw new Exception('Failed to clear live responses after archiving.');
        }

        $database->commit();
        return $rowCount;
    } catch (Throwable $e) {
        $database->rollback();
        return false;
    }
}

function ensure_prompt_archiving_support_exists() {
    $columnCheck = prepare_and_execute(
        "SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ?
           AND TABLE_NAME = 'tblPrompts'
           AND COLUMN_NAME = 'archived_at'
         LIMIT 1",
        "s",
        [DB_NAME]
    );

    if ($columnCheck === false) {
        return false;
    }

    if ($columnCheck->num_rows > 0) {
        return true;
    }

    return query("ALTER TABLE tblPrompts ADD COLUMN archived_at DATETIME NULL") === true;
}

function generate_prompt_assignment_ids($playerCount) {
    $playerCount = (int)$playerCount;
    if ($playerCount <= 0) {
        return [];
    }

    if (!ensure_prompt_archiving_support_exists()) {
        return false;
    }

    $promptResult = query("SELECT id FROM tblPrompts WHERE archived_at IS NULL ORDER BY id ASC");
    if (!$promptResult || $promptResult->num_rows === 0) {
        return false;
    }

    $promptIds = [];
    while ($row = $promptResult->fetch_assoc()) {
        $promptIds[] = (int)$row['id'];
    }

    shuffle($promptIds);

    $assignedPromptIds = [];
    for ($i = 0; $i < $playerCount; $i++) {
        $assignedPromptIds[] = $promptIds[$i % count($promptIds)];
    }
    shuffle($assignedPromptIds);

    return $assignedPromptIds;
}

function reshuffle_current_player_prompts() {
    global $database;

    $playersResult = query("SELECT id FROM tblResponses ORDER BY id ASC");
    if (!$playersResult) {
        return false;
    }

    $playerIds = [];
    while ($row = $playersResult->fetch_assoc()) {
        $playerIds[] = (int)$row['id'];
    }

    if (count($playerIds) === 0) {
        return 0;
    }

    $assignedPromptIds = generate_prompt_assignment_ids(count($playerIds));
    if ($assignedPromptIds === false) {
        return false;
    }

    $database->begin_transaction();

    try {
        foreach ($playerIds as $index => $playerId) {
            $updated = prepare_and_execute(
                "UPDATE tblResponses
                 SET prompts_id = ?,
                     partner = NULL,
                     response1 = NULL,
                     response2 = NULL,
                     response3 = NULL,
                     response4 = NULL,
                     response5 = NULL,
                     response6 = NULL,
                     response7 = NULL,
                     response8 = NULL,
                     submitted_at = NULL
                 WHERE id = ?",
                "ii",
                [$assignedPromptIds[$index], $playerId]
            );

            if ($updated === false) {
                throw new Exception('Failed to reshuffle prompts.');
            }
        }

        $database->commit();
        return count($playerIds);
    } catch (Throwable $e) {
        $database->rollback();
        return false;
    }
}
        
?>
