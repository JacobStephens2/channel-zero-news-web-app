<?php require_once 'private/initialize.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>The Channel 0 News!</title>
    <?php require_once 'components/innerHead.html'; ?>
</head>

<body id="index" class="custom">
    <div class="gametext">
        <h1>The Channel 0 News!</h1>

    <?php // Output HTML based on request method
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Save form submission
            if (!validate_csrf_token()) {
                ?><p>Invalid request. Please go back and try again.</p><?php
            } else {
            $result = prepare_and_execute(
                "UPDATE tblResponses SET partner=?, response1=?, response2=?, response3=?, response4=?, response5=?, response6=?, response7=?, response8=? WHERE name=?",
                "ssssssssss",
                [$_POST['partner'], $_POST['response1'], $_POST['response2'], $_POST['response3'], $_POST['response4'], $_POST['response5'], $_POST['response6'], $_POST['response7'], $_POST['response8'], $_POST['name']]
            );
            if($result){
                ?>
                    <p>Great!  Now all you have to do is wait for the game to&nbsp;start.</p>
                    <p>You can close out this page - the host will take it from&nbsp;here.<p>
                <?php
            } else {
                ?>
                    <p>Great googly moogly, it's all gone to shit</p>
                    <p>Exit out and try again.</p>
                <?php
            }
            }
        } else { // Display entry form
            $sql = "SELECT name, prompts_id FROM tblResponses;";
            $result = query($sql);
            $names = [];
            $prompts_id = [];
            $namePromptPairs = [];
            $i = 0;
            foreach($result as $row){
                $names[] = $row['name'];
                $prompts_id[] = $row['prompts_id'];
                $namePromptPairs[$i]['name'] = $row['name'];
                $namePromptPairs[$i]['prompt_id'] = $row['prompts_id'];
                $i++;
            }
            $promptIds = array_values(array_unique(array_column($namePromptPairs, 'prompt_id')));
            if (!empty($promptIds)) {
                $placeholders = implode(',', array_fill(0, count($promptIds), '?'));
                $types = str_repeat('i', count($promptIds));
                $promptset = prepare_and_execute(
                    "SELECT * FROM tblPrompts WHERE id IN ($placeholders)",
                    $types,
                    $promptIds
                );
            } else {
                $promptset = [];
            }


            function getnamefrompromptid($id, $array1, $array2){
                return $array2[array_search($id, $array1)];
            }

            function getpromptidfromname($name, $namesarray, $promptidarray){
                return $promptidarray[array_search($name, $namesarray)];
            }
            function getpromptfrompromptid($id, $promptrow){
                return $promptrow['prompt'.$id];
            }

            ?>

            <h2>Select your name:</h2> 
                <select id='name'>
                    <option value='' id='placeholder'></option>
                    <?php
                        foreach($names as $name){
                            echo "<option value='" . e($name) . "'>" . e($name) . "</option>";
                        }
                    ?>
                </select>
                <?php
                foreach($names as $name){
                    ?><form method="post" id="<?php echo e(str_replace(' ', '', $name));?>" style="display:none">
                        <p id='partner'>&nbsp;</p>
                        <hr>
                        <?php
                            foreach($promptset as $promptrow){
                                if(getpromptidfromname($name, $names, $prompts_id)==$promptrow['id']){
                                    $i=0;
                                    foreach($promptrow as $prompt){
                                        if($i!=0){
                                            ?>
                                            <p><?php echo e($prompt)?></p>
                                            <textarea type='text' name='response<?php echo $i;?>' id='response<?php echo $i+1;?>' rows="5" cols="34" maxlength=200></textarea>
                                            <?php
                                        }
                                        $i++;
                                    }
                                }
                            }
                        ?>
                        <input type="hidden" name="name" id="nameinput">
                        <input type="hidden" name="partner" id="partnerinput">
                        <p>That's all the Channel 0 News for today, folks.  This is your anchor signing off, and remember:</p>
                        <textarea type='text' name='response8' id='response8' rows="5" cols="34" maxlength=200></textarea>
                        <?php echo csrf_input(); ?>
                        <input type='submit' value='Submit' id='submitbutton'>
                        </form>
                    <?php
                }
            ?>

            <script>
                (function() {
                    var nameselectedyet = false;
                    var nameslist = <?php echo json_encode(array_values($names)); ?>;
                    var nameselect = document.querySelector("#name");

                    nameselect.addEventListener("change", function () {
                        if (nameselectedyet === false) {
                            nameselectedyet = true;
                            document.querySelector("#submitbutton").style.display = "block";
                            document.querySelector("#placeholder").remove();
                        }

                        var nameinputs = document.querySelectorAll("#nameinput");
                        for (var i = 0; i < nameinputs.length; i++) {
                            nameinputs[i].value = nameselect.value;
                        }
                        var partnerinputs = document.querySelectorAll("#partnerinput");
                        var partnerfields = document.querySelectorAll("#partner");
                        for (var i = 0; i < partnerinputs.length; i++) {
                            var nameindex = nameslist.indexOf(nameselect.value);
                            var partnerindex = (nameindex + 1) % nameslist.length;
                            var partnervalue = nameslist[partnerindex];
                            partnerfields[i].innerText = "You're writing for " + partnervalue;
                            partnerinputs[i].value = partnervalue;
                        }
                        var forms = document.querySelectorAll("form");
                        for (var i = 0; i < forms.length; i++) {
                            forms[i].style = "display:none";
                        }
                        document.querySelector("#" + nameselect.value.replace(/\s+/g, '')).style = "display:block";
                    });

                    var buttons = document.querySelectorAll("form");
                    for (var i = 0; i < buttons.length; i++) {
                        buttons[i].addEventListener("submit", function(event) {
                            event.preventDefault();
                            this.submit();
                        });
                    }
                })();
            </script>
            <?php
        }
    ?>
    </div>
</body>

</html>