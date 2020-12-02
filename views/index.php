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
        <h1 class="title">Parser of Goods</h1>
        <form action="" method="post" class="simple-form">
            <input class="input-url" type="text" name="url" placeholder="Input your url" /><br><br>
            <button class="btn btn-start" type="submit" name="start" value="1">Start</button>
            <button class="btn btn-stop" type="button" onclick="window.stop()">Stop</button>
        </form>
        <br>

        <?php

        if (isset($arrGoods) && !empty($arrGoods)) {
            echo '<table>';
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
    
    </body>
</html>
