<?php
require_once 'private/initialize.php';

$host_auth_required = defined('HOST_PASSWORD') && HOST_PASSWORD !== '';
$host_authenticated = !$host_auth_required || !empty($_SESSION['host_authenticated']);

// Handle form submissions before any HTML output so redirects work
if ($host_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf_token()) {
    ensure_prompt_archiving_support_exists();

    if (isset($_POST['_method']) && $_POST['_method'] === 'delete_prompt' && isset($_POST['prompt_id'])) {
        prepare_and_execute("DELETE FROM tblPrompts WHERE id = ?", "i", [(int)$_POST['prompt_id']]);
        header("Location: /prompts");
        exit;

    } elseif (isset($_POST['_method']) && $_POST['_method'] === 'archive_prompt' && isset($_POST['prompt_id'])) {
        prepare_and_execute(
            "UPDATE tblPrompts SET archived_at = NOW() WHERE id = ?",
            "i",
            [(int)$_POST['prompt_id']]
        );
        header("Location: /prompts?archived=1");
        exit;

    } elseif (isset($_POST['_method']) && $_POST['_method'] === 'unarchive_prompt' && isset($_POST['prompt_id'])) {
        prepare_and_execute(
            "UPDATE tblPrompts SET archived_at = NULL WHERE id = ?",
            "i",
            [(int)$_POST['prompt_id']]
        );
        header("Location: /prompts?unarchived=1#archived-prompts");
        exit;

    } elseif (isset($_POST['_method']) && $_POST['_method'] === 'edit_prompt') {
        $id = (int)$_POST['prompt_id'];
        prepare_and_execute(
            "UPDATE tblPrompts SET prompt1=?, prompt2=?, prompt3=?, prompt4=?, prompt5=?, prompt6=?, prompt7=? WHERE id=?",
            "sssssssi",
            [$_POST['prompt1'], $_POST['prompt2'], $_POST['prompt3'], $_POST['prompt4'], $_POST['prompt5'], $_POST['prompt6'], $_POST['prompt7'], $id]
        );
        header("Location: /prompts");
        exit;

    } elseif (isset($_POST['_method']) && $_POST['_method'] === 'add_prompt') {
        prepare_and_execute(
            "INSERT INTO tblPrompts (prompt1, prompt2, prompt3, prompt4, prompt5, prompt6, prompt7) VALUES (?, ?, ?, ?, ?, ?, ?)",
            "sssssss",
            [$_POST['prompt1'], $_POST['prompt2'], $_POST['prompt3'], $_POST['prompt4'], $_POST['prompt5'], $_POST['prompt6'], $_POST['prompt7']]
        );
        header("Location: /prompts");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Manage Prompts</title>
    <?php require_once 'components/innerHead.html'; ?>
</head>

<body id="hostpage" class="custom">
<div class="gametext">
    <h1>Manage Prompts</h1>

    <?php
        if (!$host_authenticated) {
            ?>
            <p>Please <a href="/host">log in as host</a> first.</p>
            <?php
        } else {
            ensure_prompt_archiving_support_exists();

            if (isset($_GET['archived']) && $_GET['archived'] === '1') {
                ?>
                <p>Prompt set archived.</p>
                <?php
            }
            if (isset($_GET['unarchived']) && $_GET['unarchived'] === '1') {
                ?>
                <p>Prompt set restored.</p>
                <?php
            }

            $result = query("SELECT * FROM tblPrompts WHERE archived_at IS NULL ORDER BY id");
            $archivedResult = query("SELECT * FROM tblPrompts WHERE archived_at IS NOT NULL ORDER BY archived_at DESC, id DESC");

            if ($result && $result->num_rows > 0) {
                $promptNum = 0;
                while ($row = $result->fetch_assoc()) {
                    $promptNum++;
                    ?>
                    <h2>Prompt Set #<?php echo $promptNum; ?></h2>
                    <form method="post">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="_method" value="edit_prompt">
                        <input type="hidden" name="prompt_id" value="<?php echo (int)$row['id']; ?>">
                        <?php for ($i = 1; $i <= 7; $i++) { ?>
                            <label>Prompt <?php echo $i; ?>:</label>
                            <textarea name="prompt<?php echo $i; ?>" rows="3" cols="50"><?php echo e($row["prompt$i"]); ?></textarea>
                        <?php } ?>
                        <input type="submit" value="Save Changes">
                    </form>
                    <form method="post" style="display:inline">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="_method" value="archive_prompt">
                        <input type="hidden" name="prompt_id" value="<?php echo (int)$row['id']; ?>">
                        <input type="submit" value="Archive This Prompt Set">
                    </form>
                    <form method="post" style="display:inline">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="_method" value="delete_prompt">
                        <input type="hidden" name="prompt_id" value="<?php echo (int)$row['id']; ?>">
                        <input type="submit" value="Delete This Prompt Set" onclick="return confirm('Are you sure?')">
                    </form>
                    <hr>
                    <?php
                }
            } else {
                ?>
                <p>No prompt sets yet.</p>
                <?php
            }
            ?>

            <h2 id="archived-prompts">Archived Prompt Sets</h2>
            <?php if ($archivedResult && $archivedResult->num_rows > 0) { ?>
                <?php while ($row = $archivedResult->fetch_assoc()) { ?>
                    <h3>Archived <?php echo e($row['archived_at']); ?></h3>
                    <?php for ($i = 1; $i <= 7; $i++) { ?>
                        <label>Prompt <?php echo $i; ?>:</label>
                        <textarea rows="3" cols="50" readonly><?php echo e($row["prompt$i"]); ?></textarea>
                    <?php } ?>
                    <form method="post" style="display:inline">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="_method" value="unarchive_prompt">
                        <input type="hidden" name="prompt_id" value="<?php echo (int)$row['id']; ?>">
                        <input type="submit" value="Unarchive This Prompt Set">
                    </form>
                    <hr>
                <?php } ?>
            <?php } else { ?>
                <p>No archived prompt sets.</p>
            <?php } ?>

            <h2>Add New Prompt Set</h2>
            <form method="post">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="_method" value="add_prompt">
                <?php for ($i = 1; $i <= 7; $i++) { ?>
                    <label>Prompt <?php echo $i; ?>:</label>
                    <textarea name="prompt<?php echo $i; ?>" rows="3" cols="50"></textarea>
                <?php } ?>
                <input type="submit" value="Add Prompt Set">
            </form>

            <hr>
            <a href="/host"><input type="button" value="Back to Host"></a>
            <?php
        }
    ?>
</div>
</body>
</html>
