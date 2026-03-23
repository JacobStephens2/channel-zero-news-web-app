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
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            if (isset($_POST['_method']) && $_POST['_method']=='delete'){     
                
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

            } else {
                $validNames = array_values(array_filter($_POST, function($v) { return $v !== ''; }));
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
                            <h2>Go to channelzeronews.stewardgoods.com to submit your answers!</h2>
                        <?php
                    } else {
                        ?>
                            <p>Great googly moogly, it's all gone to shit</p>
                            <p>Exit out and try again.</p>
                        <?php
                    }
                } else {
                    ?>
                    <h2>Go to channelzeronews.stewardgoods.com to submit your answers!</h2>
                    <?php
                }

                $numberOfNamesResult = query("SELECT * FROM tblResponses;");
            
                ?>
                    <p>
                        <span id="numberofsubmissions">0</span>/<span id="numberOfNames"><?php echo $numberOfNamesResult->num_rows; ?></span> players have submitted their answers so far
                    </p>
                    <p id="begingame" style="display:none">
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
            ?>
            <form method='post' id='users'>
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
        <input type='submit' value='Check Submissions'>
    </form>
    
    <form method='post'>
        <input type="hidden" name="_method" value="delete" />
        <input type='submit' value='Delete All Responses And Names'>
    </form>
</div>
</body>
</html>