<!DOCTYPE html>
<html>
    <head>
        <title>Parser Of Goods</title>
        <meta charset="utf-8" />
        <style>
         html, body {
             padding: 0;
             margin: 0;
         }
         body {
             color: #ffffff;
             padding: 10px;
             background-color: #181818;
         }
         table, tr, td, th {
             width: auto;
             background-color: #282828;
             border: 1px solid #eeeeee;
             margin: 10px 0px;
         }
         th {
             background-color: #282744;
         }
         h1.title {
             text-align: center;
         }
         .input-url {
             width: 500px;
             height: 30px;
         }
         .btn {
             color: #ffffff;
             border: none;
             font-weight: bold;
             height: 30px;
             width: 70px;
         }
         .btn-start {
             background-color: #22ec22;
         }
         .btn-stop {
             background-color: #ff1e1e;
         }
         .total, .time {
             color: #22ec22;
         }
        </style>
    </head>
    <body>
        <h1 class="title">Parser of Goods</h1>
        <form action="" method="post" class="simple-form">
            <input class="input-url" type="text" name="url" placeholder="Input your url" style="width: 500px" /><br><br>
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
                $photosTitle[$i] = mb_substr($arrGoods[$i]['photo'], (mb_strpos($arrGoods[$i]['photo'], "source-img/") + 37));
                echo '<td>'.$photosTitle[$i].'</td>';
                echo '</tr>';
            }
            echo '</tr></table>';

            echo '<hr/><p class="total">Done. Total: ' . count($arrGoods) . ' products</p><p class="time">Time - '.$time.'</p><br/>';
        }

        ?>
    </body>
</html>
