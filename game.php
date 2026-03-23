<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once 'components/innerHead.html'; ?>
    <link rel="stylesheet" href="/style/carousel.css">
    <title>Game</title>
</head>
<body id="game" class="custom">
    <?php

    $debug = false;

    require_once 'private/initialize.php';
    $sql = "SELECT * from tblResponses;";
    $result = query($sql);

    if ($debug === true) {
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }

    $names = [];
    foreach ($result as $row) {
        if ($debug === true) {
            echo "<pre>";
            print_r($row);
            echo "</pre>";
        }
    }

    ?>
    <div id="carouselExample" class="carousel slide">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="gametext">
                <h1>The Channel 0 News!</h1>
                <p>The rules:</p>        
                <p>When it's your turn to read, DO.&nbsp;NOT.&nbsp;LAUGH.</p>        
                <p>When your script is being read, use the right arrow key to move to the next slide.</p>        
                <p>When you're an audience member, laugh as much as you want!</p>        
            </div>       
        </div>
        <?php
            $i=0;
            foreach($result as $row){
                $promptsquery = prepare_and_execute("SELECT * FROM tblPrompts WHERE id = ?", "i", [$row['prompts_id']]);
                $prompts = [];
                foreach($promptsquery as $prompt){
                    foreach($prompt as $subprompt){
                        $prompts[] = $subprompt;
                    }
                }

                ?>
                <div class="carousel-item">
                    <div class="gametext">
                        <p><?php 
                        if($i==0){
                            echo "To start us off, ". e($row['partner']) . " is going to be our first presenter, with ".e($row['name'])." working the teleprompter.";
                            $i++;
                        } else {
                            echo "Next up, we have ".e($row['partner']) . " reading, with " . e($row['name']) . " working the teleprompter.";
                        }
                        ?></p>
                        <br>
                        <p>Ready? <?php echo e($row['name']); ?>, hit the right-side arrow to begin!</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="scripttext">
                        <p class="script">Good evening, everyone!  I'm <?php echo e($row['partner']); ?>, and this is today's Channel 0 News.</p>       
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="scripttext">
                        <p class="script"><?php echo e($prompts[1]); ?><?php echo e($row['response1']); ?></p>
                    </div>        
                </div>
                <div class="carousel-item">
                    <div class="scripttext">
                        <p class="script"><?php echo e($prompts[2]); ?><?php echo e($row['response2']); ?></p>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="scripttext">
                        <p class="script"><?php echo e($prompts[3]); ?><?php echo e($row['response3']); ?></p>
                    </div>        
                </div>
                <div class="carousel-item">
                    <div class="scripttext">
                        <p class="script"><?php echo e($prompts[4]); ?><?php echo e($row['response4']); ?></p>
                    </div>        
                </div>
                <div class="carousel-item">
                    <div class="scripttext">
                        <p class="script"><?php echo e($prompts[5]); ?><?php echo e($row['response5']); ?></p>
                    </div>        
                </div>
                <div class="carousel-item">
                    <div class="scripttext">
                        <p class="script"><?php echo e($prompts[6]); ?><?php echo e($row['response6']); ?></p>
                    </div>        
                </div>
                <div class="carousel-item">
                    <div class="scripttext">
                        <p class="script"><?php echo e($prompts[7]); ?><?php echo e($row['response7']); ?></p>
                    </div>        
                </div>
                <div class="carousel-item">
                    <div class="scripttext">
                        <p class="script">That's all the Channel 0 News for today, folks.  This is <?php echo e($row['partner']); ?> signing off, and remember: <?php echo e($row['response8']); ?></p>
                    </div>        
                </div>
                <?php
            }

        ?>
        <div class="carousel-item">
            <div class = "gametext">
                <center> 
                <h1>Thanks for playing!</h1>
                <hr>
                <p>Development: Jacob & Eric</p>
                <br>        
                <p>Graphic Design: Jacob</p>
                <br>
                <p>Game Design: Eric</p>
                <br>
                <p>Web Development: Jacob</p>
                <br>
                <p>Writing: Eric</p>
                
                <br>
                </center>
            </div>
        </div>
        <div class="carousel-item">
            <p></p>        
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
</body>
</html>