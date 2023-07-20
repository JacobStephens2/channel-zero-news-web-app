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
        if ($_SERVER['REQUEST_METHOD']=='POST') { // Save form submission
            $partner = sanitize($_POST['partner']);
            $response1 = sanitize($_POST['response1']);
            $response2 = sanitize($_POST['response2']);
            $response3 = sanitize($_POST['response3']);
            $response4 = sanitize($_POST['response4']);
            $response5 = sanitize($_POST['response5']);
            $response6 = sanitize($_POST['response6']);
            $response7 = sanitize($_POST['response7']);
            $response8 = sanitize($_POST['response8']);
            $name = sanitize($_POST['name']);
            $sql = "UPDATE tblResponses  set
                    partner = '".$partner."', 
                    response1 = '".$response1."', 
                    response2 = '".$response2."', 
                    response3 = '".$response3."', 
                    response4 = '".$response4."', 
                    response5 = '".$response5."', 
                    response6 = '".$response6."', 
                    response7 = '".$response7."', 
                    response8 = '".$response8."' 
                    WHERE name= '".$name."';
            ";
            $result = query($sql);
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
            echo "<pre>";
            $i = 0;
            $sql = "SELECT * from tblPrompts where id in ('eric was here'";
            foreach($namePromptPairs as $namePromptPair) {

                $sql .= ", '".$namePromptPair['prompt_id']."'";
                $i++;
            }
            $sql .=");";
            $promptset = query($sql);


            function getnamefrompromptid($id, $array1, $array2){
                return $array2[array_search($id, $array1)];
            }

            function getpromptidfromname($name, $namesarray, $promptidarray){
                return $promptidarray[array_search($name, $namesarray)];
            }
            function getpromptfrompromptid($id, $promptrow){
                return $promptrow['prompt'.$id];
            }
            echo "</pre>";

            ?>

            <h2>Select your name:</h2> 
                <select id='name'>
                    <option value='' id='placeholder'></option>
                    <?php
                        foreach($names as $name){
                            echo "<option value='$name'>$name</option>";
                        }
                    ?>
                </select>
                <?php
                foreach($names as $name){
                    ?><form method="post" id="<?php echo str_replace(' ', '', $name);?>" style="display:none">
                        <p id='partner'>&nbsp;</p>
                        <hr>
                        <?php
                            foreach($promptset as $promptrow){
                                if(getpromptidfromname($name, $names, $prompts_id)==$promptrow['id']){
                                    $i=0;
                                    foreach($promptrow as $prompt){
                                        if($i!=0){
                                            ?>
                                            <p><?php echo $prompt?></p>
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
                        <input type='submit' value='Submit' id='submitbutton'>
                        </form>
                    <?php
                }
            ?>

            <script>
                nameselectedyet=false;
                var nameslist = [
                    <?php
                        foreach($names as $name){
                            echo "'".$name."', ";
                        }
                    ?>
                ];
                promptidlist = [
                    <?php
                    foreach ($prompts_id as $prompt_id){
                        echo "'".$prompt_id . "', ";
                    }
                    ?>
                ];
                console.log(nameslist);
                console.log(promptidlist);
                let nameselect = document.querySelector("#name");
                    nameselect.addEventListener("change", function () {
                        if(nameselectedyet == false){
                            nameselectedyet = true;
                            console.log(nameselectedyet);
                            let submitbutton = document.querySelector("#submitbutton");
                            submitbutton.style.display = "block";
                            let placeholder = document.querySelector("#placeholder");
                            placeholder.remove();
                        }
                        
                        var nameinputs = document.querySelectorAll("#nameinput");
                        for(let i=0; i<nameinputs.length; i++){
                            nameinputs[i].value = nameselect.value;
                        }
                        var partnerinputs = document.querySelectorAll("#partnerinput");
                        var partnerfields = document.querySelectorAll("#partner")
                        for(let i=0; i<partnerinputs.length; i++){
                            nameindex = nameslist.indexOf(nameselect.value);
                            partnerindex=(nameindex+1)%nameslist.length;
                            partnervalue = nameslist[partnerindex];
                            partnerfields[i].innerText ="You're writing for "+partnervalue;
                            partnerinputs[i].value = partnervalue;
                        }
                        let partnerfield = document.querySelector("#partner");
                        var forms = document.querySelectorAll("form");
                        for (i=0; i<forms.length; i++){
                            forms[i].style = "display:none";
                        }
                        document.querySelector("#"+nameselect.value.replace(/\s+/g, '')).style = "display:block";
                    });

                    var buttons = document.querySelectorAll("form");
                    for(let i=0; i<buttons.length; i++){
                        buttons[i].addEventListener("submit", function(event){
                            event.preventDefault();
                            if(true){
                                this.submit();
                            }
                        });
                    }
            </script>
            <?php
        }
    ?>
    </div>
</body>

</html>