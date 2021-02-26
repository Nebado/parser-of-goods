<!DOCTYPE html>
<html>
    <head>
        <title>Parser Of Goods</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="./src/assets/styles/main.css">
        <link rel="stylesheet" href="./src/assets/styles/normalize.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Source+Serif+Pro:wght@300;600;700&display=swap" rel="stylesheet">
    </head>
    <body>
        <main id="main" class="main--wrapper">
            <div class="left_block_main simple-form">
                <div class="slider">
                    <form action="/" method="post">
                        <div class="item">
                            <h1 class="title">Parser of Goods</h1>
                            <fieldset class="form-input">
                                <input class="input input-url" type="text" name="url" value="<?= $_SESSION["url"]?>" placeholder="Enter url of category" required />
                                <div class="tooltip">?<span class="tooltip-content">E.g. https://site.com/category/</span></div>
                                <input class="input input-card-name" type="text" name="product_card_name" value="<?= $_SESSION["product_card_name"]?>" placeholder="Enter selector of product card name" required />
                                <div class="tooltip">?<span class="tooltip-content">E.g. .product-name a</span></div><br>
                                <input id="pagination-checkbox" class="input-checkbox" onclick="pagination()" type="checkbox" <?= (!empty($_SESSION['pagination_url'])) ? "checked" : "" ?> />
                                <label for="pagination">Pagination</label><br>
                                <div id="pagination" style="display: <?= (!empty($_SESSION['pagination_url'])) ? "block" : "none" ?>">
                                    <input id="pagination-url" class="input input-pagination" type="text" name="pagination_url" value="<?= isset($_SESSION["pagination_url"]) ? $_SESSION["pagination_url"] : ""?>" placeholder="Enter url page with pagination" />
                                    <div class="tooltip">?<span class="tooltip-content">E.g. https://site.com/category?page=3</span></div>
                                    <div>
                                        <p>Enter the number of pages in pagination</p>
                                        <input type="text" name="quantity_pages" value="<?= isset($_SESSION["quantity_pages"]) ? $_SESSION["quantity_pages"] : "0"?>" class="quantity-pages" id="quantiy-pages" />
                                    </div><br>
                                </div>
                                <div>
                                    <input id="image" class="input-checkbox" type="checkbox" checked="checked" name="image" value="1" />
                                    <label for="image">Download images</label>
                                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/src/upload/zip/images.zip')): ?>
                                        <a href="/src/upload/zip/images.zip" class="download">Download</a>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <input id="excel" class="input-checkbox" type="checkbox" checked="checked" name="excel" value="1" />
                                    <label for="excel">Excel/CSV</label>
                                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/src/upload/goods.xlsx')): ?>
                                        <a href="/src/upload/goods.xlsx" class="download">Download</a>
                                    <?php endif; ?>
                                </div>
                                <a class="next btn" onclick="nextSlide()">Next</a>
                                <a id="btn" href="#modalTable" class="btn btn-show">Show Table</a>
                            </fieldset>
                        </div>
                        <div class="item" style="display: none;">
                            <h1 class="title">Choose Fields</h1>
                            <fieldset class="form-input">
                                <div id="form-fields">
                                    <input class="input input-name" type="text" name="name" value="<?= $_SESSION["name"]?>" placeholder="Enter selector of name" />
                                    <div class="tooltip">?<span class="tooltip-content">E.g. h1</span></div>
                                    <input class="input input-code" type="text" name="code" value="<?= $_SESSION["code"]?>" placeholder="Enter selector of code" />
                                    <div class="tooltip">?<span class="tooltip-content">E.g. .product-article</span></div>
                                    <input class="input input-price" type="text" name="price" value="<?= $_SESSION["price"]?>" placeholder="Enter selector of price" />
                                    <div class="tooltip">?<span class="tooltip-content">E.g. .product-price</span></div>
                                    <input class="input input-photo" type="text" name="photo" value="<?= $_SESSION["photo"]?>" placeholder="Enter selector of photo" />
                                    <div class="tooltip">?<span class="tooltip-content">E.g. div.product-image</span></div>
                                    <input class="input input-description" type="text" value="<?= $_SESSION["description"]?>" name="description" placeholder="Enter selector of description" />
                                    <div class="tooltip">?<span class="tooltip-content">E.g. .product-desc</span></div>
                                    <div class="btn btn-add" onclick="addField()">Add</div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-start" type="submit" name="start" value="1">Start</button>
                                    <button class="btn btn-stop" type="button" onclick="window.stop()">Stop</button>
                                    <a class="previous btn" onclick="previousSlide()">Previous</a>
                                </div>
                            </fieldset>
                        </div>
                    </form>
                </div>
            </div>
            <div id="modalTable" class="modal-content output-table">
                <span class="close">&times;</span>
                <?php
                if (isset($arrGoods) && !empty($arrGoods)) {
                    $names = array_keys($arrGoods[0]);
                    $fields = $arrGoods[0]['fields'];
                    
                    echo '<table id="table">';
                    
                    // Head table
                    echo "<tr>";
                    foreach ($names as $name) {
                        if ($name == 'fields') {
                            $i = 1;
                            foreach ($fields as $field) {
                                echo "<th>Field $i</th>";
                                $i++;
                            }
                        } else {
                            echo "<th>$name</th>";
                        }
                    }
                    echo "</tr>";

                    // Body table
                    for ($i = 0; $i < count($arrGoods); ++$i) {
                        $photosTitle[$i] = substr($arrGoods[$i]['photo'], (strrpos($arrGoods[$i]['photo'], "/") + 1));
                        
                        echo '<tr>';
                        
                        echo '<td>'.$arrGoods[$i]['name'].'</td>';
                        echo '<td>'.$arrGoods[$i]['code'].'</td>';
                        echo '<td>'.$arrGoods[$i]['price'].'</td>';
                        echo '<td>'.$arrGoods[$i]['description'].'</td>';
                        echo '<td>'.$photosTitle[$i].'</td>';
                        
                        foreach ($arrGoods[$i]['fields'] as $field) {
                            echo '<td>'.$field.'</td>';
                        }
                        
                        echo '</tr>';
                    }
                    echo '</tr></table>';

                    echo '<hr/><p class="total">Done. Total: ' . count($arrGoods) . ' products</p><p class="time">Time - '.$time.'</p><br/>';
                }
                ?>
            </div>
        </main>
        <footer id="footer" class="footer_wrapper"></footer>
    </body>
    <script src="./src/assets/js/main.js" type="text/javascript"></script>
</html>
