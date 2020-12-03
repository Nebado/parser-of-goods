<!DOCTYPE html>
<html>
    <head>
        <title>Parser Of Goods</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="../assets/styles/main.css">
        <link rel="stylesheet" href="../assets/styles/normalize.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Source+Serif+Pro:wght@300;600;700&display=swap" rel="stylesheet">
    </head>
    <body>
        <video id="myVideo" src="https://css-tricks-post-videos.s3.us-east-1.amazonaws.com/Island%20-%204141.mp4" autoplay loop playsinline muted></video>
        
        <main id="main" class="main--wrapper">
            <div class="left_block_main simple-form">
                <div class="slider">
                    <div class="item">
                        <button id="btn" class="btn btn-show">Show Table</button>
                        <form action="" method="post">
                            <h1 class="title">Parser of Goods</h1>
                            <fieldset class="form-input">
                                <input class="input-url" type="text" name="url" placeholder="Input your url category" /><br><br>
                                <input class="input-checkbox" type="checkbox" name="image" /> Download images<br><br>
                                <input class="input-checkbox" type="checkbox" name="excel" /> Excel <br><br>
                                <a class="next btn" onclick="nextSlide()">Next</a>
                            </fieldset>
                            <br>
                    </div>
                    <div class="item">
                        <h1 class="title">Choose Fields</h1>
                        <fieldset class="form-input">
                            <input class="input-url" type="text" name="field1" placeholder="Input field" /><br><br>
                            <button class="btn btn-start" type="submit" name="start" value="1">Start</button>
                            <button class="btn btn-stop" type="button" onclick="window.stop()">Stop</button>
                            <a class="previous btn" onclick="previousSlide()">Previous</a>
                        </form>
                        <br>
                    </div>
                    
                </div>
            </div>
            <div id="modalTable" class="modal-content output-table">
                <span class="close">&times;</span>
                <?php
                if (isset($arrGoods) && !empty($arrGoods)) {
                    echo '<table id="table">';
                    echo '<tr><th>Name</th><th>Code</th><th>Price</th><th>Description</th><th>Photos</th></tr>';
                    for ($i = 0; $i < count($arrGoods); ++$i) {
                        echo '<tr>';
                        echo '<td>'.$arrGoods[$i]['name'].'</td>';
                        echo '<td>'.$arrGoods[$i]['code'].'</td>';
                        echo '<td>'.$arrGoods[$i]['price'].'</td>';
                        echo '<td>'.$arrGoods[$i]['description'].'</td>';
                        $photosTitle[$i] = substr($arrGoods[$i]['photo'], (strrpos($arrGoods[$i]['photo'], "/") + 1));
                        echo '<td>'.$photosTitle[$i].'</td>';
                        echo '</tr>';
                    }
                    echo '</tr></table>';

                    echo '<hr/><p class="total">Done. Total: ' . count($arrGoods) . ' products</p><p class="time">Time - '.$time.'</p><br/>';
                }
                ?>
            </div>
        </main>
        <footer id="footer" class="footer_wrapper">
            <div class="main_footer_block">
                <ul class="our_git">
                    <li><a href="https://github.com/Nebado">Ruslan</a></li>
                    <li><a href="">Sergei</a></li>
                    <li><a href="https://github.com/Calm13">Pavel</a></li>
                </ul>
            </div>
        </footer>
    </body>
    <script src="../assets/js/main.js"></script>
</html>
