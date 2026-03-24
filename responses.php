<?php require_once 'private/initialize.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Responses</title>
    <?php require_once 'components/innerHead.html'; ?>
</head>

<body id="hostpage" class="custom">
<div class="gametext">
    <h1>Responses</h1>

    <?php
        $host_auth_required = defined('HOST_PASSWORD') && HOST_PASSWORD !== '';
        $host_authenticated = !$host_auth_required || !empty($_SESSION['host_authenticated']);

        if (!$host_authenticated) {
            ?>
            <p>Please <a href="/host">log in as host</a> first.</p>
            <?php
        } else {
            $result = query("SELECT r.name, r.partner, r.response1, r.response2, r.response3, r.response4, r.response5, r.response6, r.response7, r.response8, r.submitted_at, p.prompt1, p.prompt2, p.prompt3, p.prompt4, p.prompt5, p.prompt6, p.prompt7 FROM tblResponses r LEFT JOIN tblPrompts p ON r.prompts_id = p.id");

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <h2><?php echo e($row['name']); ?></h2>
                    <?php if ($row['partner'] === null) { ?>
                        <p><em>Has not submitted yet.</em></p>
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
            } else {
                ?>
                <p>No players set up yet.</p>
                <?php
            }
            ?>
            <a href="/host"><input type="button" value="Back to Host"></a>
            <a href="/archive"><input type="button" value="View Response Archive"></a>
            <?php
        }
    ?>
</div>
</body>
</html>
