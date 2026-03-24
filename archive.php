<?php
require_once 'private/initialize.php';

$host_auth_required = defined('HOST_PASSWORD') && HOST_PASSWORD !== '';
$host_authenticated = !$host_auth_required || !empty($_SESSION['host_authenticated']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Archive</title>
    <?php require_once 'components/innerHead.html'; ?>
</head>

<body id="hostpage" class="custom">
<div class="gametext">
    <h1>Archive</h1>

    <?php
        if (!$host_authenticated) {
            ?>
            <p>Please <a href="/host">log in as host</a> first.</p>
            <?php
        } else {
            ensure_response_archive_table_exists();

            $batches = query("
                SELECT archive_batch_id, archived_at, COUNT(*) AS response_count
                FROM tblResponseArchive
                GROUP BY archive_batch_id, archived_at
                ORDER BY archived_at DESC, archive_batch_id DESC
            ");

            if ($batches && $batches->num_rows > 0) {
                while ($batch = $batches->fetch_assoc()) {
                    $archiveBatchId = $batch['archive_batch_id'];
                    $batchResponses = prepare_and_execute(
                        "SELECT * FROM tblResponseArchive WHERE archive_batch_id = ? ORDER BY name ASC",
                        "s",
                        [$archiveBatchId]
                    );
                    ?>
                    <h2>
                        Archived <?php echo e($batch['archived_at']); ?>
                        (<?php echo (int)$batch['response_count']; ?> response<?php echo (int)$batch['response_count'] === 1 ? '' : 's'; ?>)
                    </h2>
                    <?php

                    if ($batchResponses) {
                        while ($row = $batchResponses->fetch_assoc()) {
                            ?>
                            <h3><?php echo e($row['name']); ?></h3>
                            <?php if ($row['partner'] === null) { ?>
                                <p><em>Was not submitted yet.</em></p>
                            <?php } else { ?>
                                <p>Writing for: <?php echo e($row['partner']); ?></p>
                                <?php if ($row['submitted_at']) { ?>
                                    <p style="opacity:0.5">Submitted: <?php echo e($row['submitted_at']); ?></p>
                                <?php } ?>
                                <table>
                                <?php for ($i = 1; $i <= 7; $i++) { ?>
                                    <tr>
                                        <th><?php echo e($row["prompt$i"]); ?></th>
                                        <td><?php echo e($row["response$i"]); ?></td>
                                    </tr>
                                <?php } ?>
                                    <tr>
                                        <th>Signing off line:</th>
                                        <td><?php echo e($row['response8']); ?></td>
                                    </tr>
                                </table>
                            <?php } ?>
                            <hr>
                            <?php
                        }
                    }
                }
            } else {
                ?>
                <p>No archived responses yet.</p>
                <?php
            }
            ?>
            <a href="/host"><input type="button" value="Back to Host"></a>
            <?php
        }
    ?>
</div>
</body>
</html>
