<?php require_once 'private/initialize.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Submissions</title>
    <?php require_once 'components/innerHead.html'; ?>
</head>

<body id="hostpage" class="custom">
<div class="gametext">
    <h1>The Channel 0 News!</h1>
    <h2>Go to zero.stephens.page to submit your answers!</h2>

    <?php $numberOfNamesResult = query("SELECT * FROM tblResponses"); ?>
    <p style="width:auto;">
        <span id="numberofsubmissions">0</span>/<span id="numberOfNames"><?php echo $numberOfNamesResult ? $numberOfNamesResult->num_rows : 0; ?></span> players have submitted their answers so far
    </p>
    <p id="begingame">
        <a href="/game">
            <input type="button" value="Start the game!">
        </a>
    </p>
    <div id="setintervalid" style="display:none"></div>
    <a href="/host"><input type="button" value="Back to Host"></a>

    <script>
        (function() {
            function getnumberofsubmissions() {
                return fetch("endpoints/getNumberOfPlayerSubmissions.php")
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        document.querySelector("#numberofsubmissions").innerText = data.numberOfSubmissions;
                        document.querySelector("#numberOfNames").innerText = data.numberOfNames;

                        if (data.numberOfSubmissions >= data.numberOfNames) {
                            document.querySelector("#begingame").style.display = "block";
                            clearInterval(document.querySelector("#setintervalid").innerText);
                        }
                    });
            }

            document.querySelector("#begingame").style.display = "none";
            getnumberofsubmissions();

            var refreshIntervalId = setInterval(getnumberofsubmissions, 1000);
            document.querySelector("#setintervalid").innerText = refreshIntervalId;
        })();
    </script>
</div>
</body>
</html>
