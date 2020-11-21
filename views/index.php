<h1>Parser of Goods</h1>
<form action="" method="post">
    <input type="text" name="url" placeholder="Input your url" style="width: 500px" /><br><br>
    <button type="submit" name="start" value="1">Start</button>
</form>

<?php

if (isset($arrGoods) && !empty($arrGoods)) {
    echo '<table border="1">';
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

    echo '<hr/><p style="color: green">Done<br>Time - '.$time.'</p><br/>';
}

?>
