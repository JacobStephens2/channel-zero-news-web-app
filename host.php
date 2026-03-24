<?php
require_once 'private/initialize.php';

$host_auth_required = defined('HOST_PASSWORD') && HOST_PASSWORD !== '';
$host_authenticated = !$host_auth_required || !empty($_SESSION['host_authenticated']);

// Handle host login
if ($host_auth_required && !$host_authenticated && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['host_password'])) {
    if (validate_csrf_token() && hash_equals(HOST_PASSWORD, $_POST['host_password'])) {
        $_SESSION['host_authenticated'] = true;
        $host_authenticated = true;
    }
}

// Handle redirecting actions before any HTML output
if ($host_authenticated && $_SERVER['REQUEST_METHOD']==='POST' && validate_csrf_token()) {
    if (isset($_POST['_method']) && $_POST['_method']==='remove_player' && isset($_POST['player_name'])) {
        prepare_and_execute("DELETE FROM tblResponses WHERE name = ?", "s", [$_POST['player_name']]);
        header("Location: /host");
        exit;
    } elseif (isset($_POST['_method']) && $_POST['_method']==='delete') {
        $result = query("DELETE FROM tblResponses WHERE true");
        if ($result) {
            header("Location: /host?deleted=1");
        } else {
            header("Location: /host?deleted=error");
        }
        exit;
    } elseif (isset($_POST['_method']) && $_POST['_method']==='archive_responses') {
        $archivedCount = archive_current_responses();
        if ($archivedCount === false) {
            header("Location: /host?archive=error");
        } else {
            header("Location: /host?archive=" . (int)$archivedCount);
        }
        exit;
    } elseif (isset($_POST['_method']) && $_POST['_method']==='shuffle_prompts') {
        $shuffledCount = reshuffle_current_player_prompts();
        if ($shuffledCount === false) {
            header("Location: /host?shuffle=error");
        } else {
            header("Location: /host?shuffle=" . (int)$shuffledCount);
        }
        exit;
    } elseif (isset($_POST['_method']) && $_POST['_method']==='add_player' && isset($_POST['new_player_name']) && $_POST['new_player_name'] !== '') {
        $promptAssignments = generate_prompt_assignment_ids(1);
        if ($promptAssignments !== false && !empty($promptAssignments)) {
            prepare_and_execute(
                "INSERT INTO tblResponses (name, prompts_id) VALUES (?, ?)",
                "si",
                [$_POST['new_player_name'], $promptAssignments[0]]
            );
        }
        header("Location: /host");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Host</title>
    <?php require_once 'components/innerHead.html'; ?>
</head>

<body id="hostpage" class="custom">
<div class="gametext">
    <h1>The Channel 0 News!</h1>

    <?php
        if (!$host_authenticated) {
    ?>
            <h2>Host Login</h2>
            <form method="post">
                <?php echo csrf_input(); ?>
                <input type="password" name="host_password" placeholder="Host password">
                <input type="submit" value="Log In">
            </form>
            <?php if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['host_password'])) { ?>
                <p>Incorrect password.</p>
            <?php } ?>
    <?php
        } else {
            if (isset($_GET['archive'])) {
                if ($_GET['archive'] === 'error') {
                    ?>
                    <p>Archiving failed. Please try again.</p>
                    <?php
                } else {
                    $archivedCount = (int)$_GET['archive'];
                    ?>
                    <p><?php echo $archivedCount; ?> response<?php echo $archivedCount === 1 ? '' : 's'; ?> archived.</p>
                    <?php
                }
            }
            if (isset($_GET['shuffle'])) {
                if ($_GET['shuffle'] === 'error') {
                    ?>
                    <p>Prompt shuffle failed. Make sure prompt sets exist, then try again.</p>
                    <?php
                } else {
                    $shuffledCount = (int)$_GET['shuffle'];
                    ?>
                    <p>Prompt sets reshuffled for <?php echo $shuffledCount; ?> player<?php echo $shuffledCount === 1 ? '' : 's'; ?>. Existing submissions were cleared.</p>
                    <?php
                }
            }
            if (isset($_GET['deleted']) && $_GET['deleted'] === 'error') {
                ?>
                <p>Deleting the slate failed. Please try again.</p>
                <?php
            } elseif (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
                ?>
                <p>The slate has been wiped clean.</p>
                <?php
            }

            $currentPlayers = query("SELECT name, partner FROM tblResponses");
            ?>
            <h2>Current Players</h2>
            <?php if ($currentPlayers && $currentPlayers->num_rows > 0) { ?>
                <ul>
                <?php while ($row = $currentPlayers->fetch_assoc()) { ?>
                    <li>
                        <?php echo e($row['name']); ?>
                        <?php if ($row['partner'] !== null) { ?>
                            <span style="opacity:0.5">(submitted)</span>
                        <?php } ?>
                        <form method="post" style="display:inline">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="_method" value="remove_player">
                            <input type="hidden" name="player_name" value="<?php echo e($row['name']); ?>">
                            <button type="submit" style="margin-left:0.5em">✕</button>
                        </form>
                    </li>
                <?php } ?>
                </ul>
            <?php } else { ?>
                <p>No players added yet.</p>
            <?php } ?>
            <form method="post">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="_method" value="add_player">
                <input type="text" name="new_player_name" placeholder="New player name">
                <input type="submit" value="Add Player">
            </form>
    <?php } ?>

    <hr>

    <a href="/submissions"><input type='button' value='Check Submissions'></a>

    <a href="/"><input type='button' value='Player Page'></a>

    <a href="/responses"><input type='button' value='View Responses'></a>

    <a href="/archive"><input type='button' value='View Response Archive'></a>

    <a href="/prompts"><input type='button' value='Manage Prompts'></a>

    <a href="/game"><input type='button' value='Start the Game!'></a>

    <form method='post'>
        <?php echo csrf_input(); ?>
        <input type="hidden" name="_method" value="shuffle_prompts" />
        <input type='submit' value='Shuffle Prompts'>
    </form>

    <form method='post'>
        <?php echo csrf_input(); ?>
        <input type="hidden" name="_method" value="archive_responses" />
        <input type='submit' value='Archive Responses'>
    </form>

    <form method='post'>
        <?php echo csrf_input(); ?>
        <input type="hidden" name="_method" value="delete" />
        <input type='submit' value='Delete All Responses And Names'>
    </form>
</div>
</body>
</html>
