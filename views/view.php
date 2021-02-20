<!DOCTYPE html>
<html>
    <head>
        <title>Parser Of Goods</title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="assets/styles/main.css">
        <link rel="stylesheet" href="assets/styles/normalize.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Source+Serif+Pro:wght@300;600;700&display=swap" rel="stylesheet">
    </head>
    <body>
        <main id="main" class="main--wrapper">
            <div class="left_block_main simple-form">
                <div class="slider">
                    <div class="item">
                        <a id="btn" href="#modalTable" class="btn btn-show">Show Table</a>
                        <form action="" method="post">
                            <h1 class="title">Parser of Goods</h1>
                            <fieldset class="form-input">
                                <input class="input input-url" type="text" name="url" value="<?= $_SESSION["url"]?>" placeholder="Enter url of category" /><span class="required">*</span>
                                <input class="input input-card" type="text" name="product_card" value="<?= $_SESSION["product_card"]?>" placeholder="Enter class of product card" /><span class="required">*</span>
                                <input class="input input-card-name" type="text" name="product_card_name" value="<?= $_SESSION["product_card_name"]?>" placeholder="Enter class of product card name" /><span class="required">*</span><br>
                                <div>
                                    <input id="pagination-checkbox" class="input-checkbox" onclick="pagination()" type="checkbox" />
                                    <label for="pagination">Pagination</label><br>
                                    <div>
                                        <div id="pagination" style="display: none;">
                                            <input id="pagination-url" class="input input-pagination" type="text" name="pagination_url" value="<?= isset($_SESSION["pagination_url"]) ? $_SESSION["pagination_url"] : ""?>" placeholder="Enter url page with pagination" />
                                            <div>
                                                <p>Enter the number of pages in pagination</p>
                                                <input type="text" name="quantity_pages" value="<?= isset($_SESSION["quantity_pages"]) ? $_SESSION["quantity_pages"] : "0"?>" class="quantity-pages" id="quantiy-pages" />
                                            </div><br>
                                        </div>
                                        <div>
                                            <input id="image" class="input-checkbox" type="checkbox" checked="checked" name="image" value="1" />
                                            <label for="image">Download images</label>
                                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/zip/images.zip')): ?>
                                                <a href="zip/images.zip" class="download">Download</a>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <input id="excel" class="input-checkbox" type="checkbox" checked="checked" name="excel" value="1" />
                                            <label for="excel">Excel/CSV</label>
                                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/goods.xlsx')): ?>
                                                <a href="./goods.xlsx" class="download">Download</a>
                                            <?php endif; ?>
                                        </div>
                                        <a class="next btn" onclick="nextSlide()">Next</a>
                            </fieldset>
                                    </div>
                                    <div class="item">
                                        <h1 class="title">Choose Fields</h1>
                                        <fieldset class="form-input">
                                            <div id="form-fields">
                                                <input class="input input-name" type="text" name="name" value="<?= $_SESSION["name"]?>" placeholder="Input class name" /><span class="required">*</span>
                                                <input class="input input-code" type="text" name="code" value="<?= $_SESSION["code"]?>" placeholder="Input class code" /><span class="required">*</span>
                                                <input class="input input-price" type="text" name="price" value="<?= $_SESSION["price"]?>" placeholder="Input class price" /><span class="required">*</span>
                                                <input class="input input-photo" type="text" name="photo" value="<?= $_SESSION["photo"]?>" placeholder="Input class photo" /><span class="required">*</span>
                                                <input class="input input-description" type="text" value="<?= $_SESSION["description"]?>" name="description" placeholder="Input class description" />
                                            </div>
                                            <div class="btn-group">
                                                <button class="btn btn-start" type="submit" name="start" value="1">Start</button>
                                                <button class="btn btn-stop" type="button" onclick="window.stop()">Stop</button>
                                                <a class="previous btn" onclick="previousSlide()">Previous</a>
                                            </div>
                                        </fieldset>
                        </form>
                        <button class="btn btn-add" onclick="addField()">Add</button>
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
        </footer>
    </body>
    <script src="assets/js/main.js"></script>
</html>
