<?php

ob_start();

echo '<table border="1">';
echo '<tr><th>Name</th><th>Code</th><th>Price</th><th>Description</th><th>Photos</th></tr>';
for ($i = 0; $i < count($names); ++$i) {
    echo '<tr>';
    echo '<td>'.$names[$i].'</td>';
    echo '<td>'.$codes[$i].'</td>';
    echo '<td>'.$prices[$i].'</td>';
    echo '<td>'.$description[$i].'</td>';
    $photosTitle[$i] = mb_substr($photos[$i], (mb_strpos($photos[$i], "source-img/") + 21));
    $photosTitle[$i] = 'data/'.$photosTitle[$i];
    echo '<td>'.$photosTitle[$i].'</td>';
    echo '</tr>';
}
echo '</tr></table>';

$contents = ob_get_contents();
ob_end_clean();

echo $contents;
