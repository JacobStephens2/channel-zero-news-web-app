<?php require_once 'private/initialize.php'; ?>

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
        $host_auth_required = defined('HOST_PASSWORD') && HOST_PASSWORD !== '';
        $host_authenticated = !$host_auth_required || !empty($_SESSION['host_authenticated']);

        if ($host_auth_required && !$host_authenticated && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['host_password'])) {
            if (validate_csrf_token() && hash_equals(HOST_PASSWORD, $_POST['host_password'])) {
                $_SESSION['host_authenticated'] = true;
                $host_authenticated = true;
            } else {
                ?><p>Incorrect password.</p><?php
            }
        }

        if (!$host_authenticated) {
    ?>
            <h2>Host Login</h2>
            <form method="post">
                <?php echo csrf_input(); ?>
                <input type="password" name="host_password" placeholder="Host password">
                <input type="submit" value="Log In">
            </form>
    <?php
        } else {

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            if (!validate_csrf_token()) {
                ?><p>Invalid request. Please go back and try again.</p><?php
            } elseif (isset($_POST['_method']) && $_POST['_method']=='delete'){

                $sql = "DELETE from tblResponses where true;";

                $result = query($sql);

                if ($result) {
                    ?>
                        <p>The slate has been wiped clean.</p>
                        <p><a href='/host'></a><p>
                    <?php
                } else {
                    ?>
                        <p>Great googly moogly, it's all gone to shit</p>
                        <p>Exit out and try again.</p>
                        <p><a href='/host'></a><p>
                    <?php
                }

            } elseif (isset($_POST['_method']) && $_POST['_method']=='remove_player' && isset($_POST['player_name'])) {
                $result = prepare_and_execute(
                    "DELETE FROM tblResponses WHERE name = ?",
                    "s",
                    [$_POST['player_name']]
                );
                header("Location: /host");
                exit;

            } elseif (isset($_POST['_method']) && $_POST['_method']=='add_player' && isset($_POST['new_player_name']) && $_POST['new_player_name'] !== '') {
                // Pick a random prompt for the new player
                $promptResult = query("SELECT id FROM tblPrompts");
                $promptIds = [];
                while ($row = $promptResult->fetch_assoc()) {
                    $promptIds[] = $row['id'];
                }
                if (!empty($promptIds)) {
                    $randomPromptId = $promptIds[array_rand($promptIds)];
                    prepare_and_execute(
                        "INSERT INTO tblResponses (name, prompts_id) VALUES (?, ?)",
                        "si",
                        [$_POST['new_player_name'], $randomPromptId]
                    );
                }
                header("Location: /host");
                exit;

            } else {
                $postData = $_POST;
                unset($postData['csrf_token']);
                $validNames = array_values(array_filter($postData, function($v) { return $v !== ''; }));
                shuffle($validNames);

                $sql = "SELECT * FROM tblPrompts";
                $result = query($sql);

                // Randomize the prompts assigned to players.
                // Duplicates appear only if players exceed available prompts.
                $allPromptIDsArray = [];
                for ($i = 1; $i <= $result->num_rows; $i++) {
                    $allPromptIDsArray[] = $i;
                }
                shuffle($allPromptIDsArray);

                $promptIDsForThisGameArray = [];
                for ($i = 0; $i < count($validNames); $i++) {
                    $promptIDsForThisGameArray[] = $allPromptIDsArray[$i % $result->num_rows];
                }
                shuffle($promptIDsForThisGameArray);

                if (count($validNames) > 0) {
                    $placeholders = implode(', ', array_fill(0, count($validNames), '(?, ?)'));
                    $types = '';
                    $params = [];
                    for ($i = 0; $i < count($validNames); $i++) {
                        $types .= 'si';
                        $params[] = $validNames[$i];
                        $params[] = $promptIDsForThisGameArray[$i];
                    }
                    $result = prepare_and_execute(
                        "INSERT INTO tblResponses (name, prompts_id) VALUES " . $placeholders,
                        $types,
                        $params
                    );

                    if ($result) {
                        ?>
                            <h2>Go to zero.stephens.page to submit your answers!</h2>
                        <?php
                    } else {
                        ?>
                            <p>Great googly moogly, it's all gone to shit</p>
                            <p>Exit out and try again.</p>
                        <?php
                    }
                } else {
                    ?>
                    <h2>Go to zero.stephens.page to submit your answers!</h2>
                    <?php
                }

                $numberOfNamesResult = query("SELECT * FROM tblResponses;");
            
                ?>
                    <p>
                        <span id="numberofsubmissions">0</span>/<span id="numberOfNames"><?php echo $numberOfNamesResult->num_rows; ?></span> players have submitted their answers so far
                    </p>
                    <p id="begingame">
                        <a href="/game">
                            <input type="submit" value="Start the game!">
                        </a>
                    </p>
                    <div id="setintervalid" style="display:none"></div>
                    <script>
                        numberofsubmissions = 0;
                        function getnumberofsubmissions() {
                            return fetch("endpoints/getNumberOfPlayerSubmissions.php")
                            .then((response) => response.json())
                            .then((data) => {

                                document.querySelector("#numberofsubmissions").innerText = data.numberOfSubmissions;
                                document.querySelector("#numberOfNames").innerText = data.numberOfNames;
                                
                                if (document.querySelector("#numberofsubmissions").innerText >= data.numberOfNames) {
                                    document.querySelector("#begingame").style.display = "block";
                                    clearInterval(document.querySelector("#setintervalid").innerText);
                                }
                            });
                        }

                        var refreshIntervalId = setInterval(
                            getnumberofsubmissions,
                            1000
                        );
                        
                        document.querySelector("#setintervalid").innerText = refreshIntervalId;
                    </script>
                <?php
            }
            
        } else {
            $currentPlayers = query("SELECT name, partner FROM tblResponses");
            if ($currentPlayers && $currentPlayers->num_rows > 0) {
                ?>
                <h2>Current Players</h2>
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
                <form method="post">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="_method" value="add_player">
                    <input type="text" name="new_player_name" placeholder="New player name">
                    <input type="submit" value="Add Player">
                </form>
                <hr>
                <?php
            }
            ?>
            <form method='post' id='users'>
                <?php echo csrf_input(); ?>
                <section id="users">
                    <div id="SwSDiv1" class="sweetSpot">
                        <input name="1" type="search" class="user">
                        <button id="RemoveUser1" class="user" style="margin-left: 0">-</button>
                        <ul id="userList1" class="user"></ul>
                    </div>
                    <div id="SwSDiv2" class="sweetSpot">
                        <input name="2" type="search" class="user">
                        <button id="RemoveUser2" class="user" style="margin-left: 0">-</button>
                        <ul id="userList2" class="user"></ul>
                    </div>
                </section>
                <button id="addUser">Add Another</button>
                <input type='submit' value='Submit'>
            </form>
            <script>
                for (let i = 1; i < 3; i++) {
                    let button = document.querySelector("#RemoveUser"+i);
                    button.addEventListener("click", function (event) {
                        event.preventDefault();
                        let element = document.querySelector("#SwSDiv"+i);
                        element.remove();
                    });
                }   
            </script>
            <script src='host.js' defer></script>
            <?php
        }
    ?>

    <hr>

    <form method='get'>
        <input type='submit' value='Enter Names'>
    </form>
    
    <form method='post'>
        <?php echo csrf_input(); ?>
        <input type='submit' value='Check Submissions'>
    </form>
    
    <form method='post'>
        <?php echo csrf_input(); ?>
        <input type="hidden" name="_method" value="delete" />
        <input type='submit' value='Delete All Responses And Names'>
    </form>
    <?php } ?>
</div>
</body>
</html>