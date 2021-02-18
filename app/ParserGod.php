<?php

namespace App;

use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PHPZip\Zip\File\Zip as Zip;
use \zipArchive;

class ParserGod implements ParserGodInterface
{
    const SSL_PORT = 443;
    public $start;
    public $stop;
    public static $host;
    public static $protocol;

    public function __construct()
    {
        $this->start = false;
    }

    public function process($start, $catUrl)
    {
        // Get a hostname
        self::$host = self::getHost($catUrl);

        // Check protocol
        self::$protocol = self::checkProtocol($catUrl);

        if ($start == true && $catUrl != null) {

            // TODO: Make method for registration session data and request data

            // Get data from request post if exists post data
            $productCard                = isset($_POST["product_card"]) ? $_POST["product_card"] : "";
            $productCardName            = isset($_POST["product_card_name"]) ? $_POST["product_card_name"] : "";
            $paginationUrl              = isset($_POST["pagination_url"]) ? $_POST["pagination_url"] : "";
            $quantityPages              = isset($_POST["quantity_pages"]) ? $_POST["quantity_pages"] : "0";
            $name                       = isset($_POST['name']) ? $_POST['name'] : '';
            $code                       = isset($_POST['code']) ? $_POST['code'] : '';
            $price                      = isset($_POST['price']) ? $_POST['price'] : '';
            $photo                      = isset($_POST['photo']) ? $_POST['photo'] : '';
            $desc                       = isset($_POST['description']) ? $_POST['description'] : '';

            // Put data in session from request post if exists post data
            $_SESSION["name"]              = isset($_POST["name"]) ? $_POST["name"] : "";
            $_SESSION["code"]              = isset($_POST["code"]) ? $_POST["code"] : "";
            $_SESSION["price"]             = isset($_POST["price"]) ? $_POST["price"] : "";
            $_SESSION["photo"]             = isset($_POST["photo"]) ? $_POST["photo"] : "";
            $_SESSION["description"]       = isset($_POST["description"]) ? $_POST["description"] : "";
            $_SESSION["product_card"]      = isset($_POST["product_card"]) ? $_POST["product_card"] : "";
            $_SESSION["product_card_name"] = isset($_POST["product_card_name"]) ? $_POST["product_card_name"] : "";
            $_SESSION["pagination_url"]    = isset($_POST["pagination_url"]) ? $_POST["pagination_url"] : "";
            $_SESSION["quantity_pages"]    = isset($_POST["quantity_pages"]) ? $_POST["quantity_pages"] : "";
            $_SESSION["url"]               = isset($_POST["url"]) ? $_POST["url"] : "";

            $htmlCat = \Parser::getPage([
                "url" => "$catUrl"
            ]);

            // Get category urls if exists pagination, otherwise,
            // put category url
            if (!empty($paginationUrl) && intval($quantityPages) > 0) {
                $categoryUrls = $this->getUrlsOfPagination($paginationUrl, $quantityPages);
            } else {
                $categoryUrls[] = $catUrl;
            }

            // Use Multi Curl for categories
            $ref = new \cURmultiStable;
            $htmlCategories = $ref->runmulticurl($categoryUrls);
			
            $urlGoods = $this->getUrlsOfProducts($htmlCategories, $productCard, $productCardName);

            // Use Multi Curl
            $ref = new \cURmultiStable;
            $htmlGoods = $ref->runmulticurl($urlGoods);

            global $arrGoods;
            $arrGoods = $this->parseProducts($htmlGoods);

            $this->generateExcel($arrGoods);

            $this->downloadImages($arrGoods);

            // Unset session data
            unset($_SESSION["pagination_url"]);
            unset($_SESSION["quantity_pages"]);
        } else {
            return;
        }
    }

    /**
     * Get all url pages of pagination
     *
     * @param string $paginationUrl
     * @param integer $quantiyPages
     * @return array
     */
    public function getUrlsOfPagination($paginationUrl, $quantityPages)
    {
        $iterator = 1;
        $categoryHref = [];
        $quantityPages = intval($quantityPages);

        if ($quantityPages > 0) {
            if ($paginationUrl[strlen($paginationUrl)-1] == '/') {
                $paginationUrl = substr($paginationUrl, 0, -1);
            }

            while (is_numeric(substr($paginationUrl, -$iterator)) != false) {
                $iterator++;
            }

            $numberOfDigit = $iterator - 1;

            for ($i = 1; $i <= $quantityPages; ++$i) {
                $paginationHref = substr_replace($paginationUrl, $i, -$numberOfDigit);
                $categoryHref[] = $paginationHref;
            }
        }

        return $categoryHref;
    }

    /**
     * Parse url products from product card
     *
     * @param string $htmlCategories
     * @return array
     */

    public function getUrlsOfProducts($htmlCategories, $productCard, $productCardName)
    {
        // TODO: Optimize process of parsing products card
        // 1. Unload memory
        // 2. Use other library for parsing

        $urlGoods = array();
        $domCategory = array();
		$len = count($htmlCategories);
		$cardRegex = '<div[^>]+?class\s*?=\s*?(["\'])'.str_replace('.', '', $productCard).'(.*)\1[^>]*?>(.+?)</div>';

        for ($k = 0; $k < $len; ++$k) {
            $domCategory[] = $htmlCategories[$k];

			preg_match_all($regexp, $domCategory[$k], $matches); 
			$hrefs = $matches[1];
        }

        $hrefs = array_unique($hrefs);

        foreach ($hrefs as $href) {
            if (substr($href, 0, 4) === 'http') {
                $urlGoods[] = $href;
            } else {
                $urlGoods[] = self::$protocol.self::$host.$href;
            }
        }

        return $urlGoods;
    }

    /**
     * Parse all products
     *
     * @param array
     * @return array
     */
    public function parseProducts($htmlGoods)
    {
        $arrGoods = array();

        for ($i = 0; $i < count($htmlGoods); ++$i) {
            if(!empty($htmlGoods[$i])) {
                $contentGoods[$i] = $htmlGoods[$i];
                \phpQuery::newDocument($contentGoods[$i]);

                // Section main parsing fields

                $arrGoods[$i]['name'] = (!empty($name)) ? ($arrGoods[$i]['name'] = trim(pq($name)->text())) : '';

                $arrGoods[$i]['code'] = (!empty($code)) ? ($arrGoods[$i]['code'] = pq($code)->text()) : '';

                $arrGoods[$i]['price'] = (!empty($price)) ? ($arrGoods[$i]['price'] = pq($price)->text()) : '';

                $arrGoods[$i]['description'] = (!empty($desc)) ? ($arrGoods[$i]['desc'] = pq($desc)->text()) : '';

                /* more params... */

                $arrGoods[$i]['photo'] = (!empty($photo)) ? ($arrGoods[$i]['photo'] = pq($photo)->attr('href')) : '';

                if ($arrGoods[$i]['photo'] == '') {
                    $arrGoods[$i]['photo'] = pq($photo)->attr('src');
                }
            }

            \phpQuery::unloadDocuments();
        }

        return $arrGoods;
    }

    /**
     * Generate Excel file
     */
    public function generateExcel($arrGoods)
    {
        if (isset($_POST["excel"]) && $_POST["excel"] == "1") {
            $phpExcel = new Spreadsheet();

            $titles = array(
                array(
                    'name' => 'Name',
                    'ceil' => 'A'
                ),
                array(
                    'name' => 'Code',
                    'ceil' => 'B'
                ),
                array(
                    'name' => 'Price',
                    'ceil' => 'C'
                ),
                array(
                    'name' => 'Description',
                    'ceil' => 'D'
                ),
                array(
                    'name' => 'Image',
                    'ceil' => 'E'
                )
            );

            for ($i = 0; $i < count($titles); $i++) {
                $string = $titles[$i]['name'];
                //$string = mb_convert_encoding($string, 'UTF-8', 'Windows-1251');
                $ceilLetter = $titles[$i]['ceil'] . 1;
                $phpExcel->getActiveSheet()->setCellValueExplicit($ceilLetter, $string, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }

            $i = 2;

            foreach($arrGoods as $row) {
                $phpExcel->getActiveSheet()->setCellValueExplicit("A$i", $row['name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                //$string = mb_convert_encoding($string, 'UTF-8', 'Windows-1251');
                $phpExcel->getActiveSheet()->setCellValue("B$i", $row['code']);
                $phpExcel->getActiveSheet()->setCellValue("C$i", $row['price']);
                $description = $row['description'];
                //$string = mb_convert_encoding($string, 'UTF-8', 'Windows-1251');
                $phpExcel->getActiveSheet()->setCellValueExplicit("D$i", $description, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $phpExcel->getActiveSheet()->setCellValue("E$i", $row['photo'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $i++;
            }

            $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(96);
            $phpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
            $phpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
            $phpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(96);
            $phpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(96);

            $page = $phpExcel->setActiveSheetIndex(0);
            $page->setTitle('goods');
            $objWriter = new Xlsx($phpExcel);
            $filename = "goods.xlsx";

            if (file_exists($filename)) {
                unlink($filename);
            }

            $objWriter->save($filename);
        }
    }

    /**
     * Download images
     */
    public function downloadImages($arrGoods)
    {
        if (isset($_POST["image"]) && $_POST["image"] == "1") {
            $catalogOutPath = "images";
            if(!is_dir($catalogOutPath)) {
                mkdir($catalogOutPath, 0777, true);
            }

            $k = 0;
            for($k = 0; $k < count($arrGoods); $k++) {
                $photoName = substr($arrGoods[$k]['photo'], (strrpos($arrGoods[$k]['photo'], "/") + 1));
                $photoUrl = self::$protocol.self::$host.$arrGoods[$k]['photo'];

                $fullPhotoPathName = $catalogOutPath . DIRECTORY_SEPARATOR . $photoName;
                $arrFullPhotos[] = $fullPhotoPathName;

                if (!file_exists($fullPhotoPathName)) {
                    file_put_contents($fullPhotoPathName, file_get_contents($photoUrl));
                }
            }

            $this->zipUp();
        }
    }

    /**
     * Create zip file for images
     */
    public function zipUp()
    {
        $zipPath = "zip";

        if(!is_dir($zipPath)) {
            mkdir($zipPath, 0777, true);
        }

        $zip = new ZipArchive();
        $filenameZip = $zipPath . DIRECTORY_SEPARATOR ."images" . ".zip";
        /* $zip = new Zip();
         * $zip->saveZipFile($filenameZip); */
        $res = $zip->open($filenameZip, ZipArchive::CREATE);
        $files = scandir('images');

        if ($res === TRUE) {
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {continue;}
                $f = 'images'.DIRECTORY_SEPARATOR.$file;
                $zip->addFile($f);
            }
            $zip->close();
        }
    }

    /**
     * Get a host
     *
     * @param string
     * @return string
     */
    public static function getHost($url)
    {
        $urlArr = explode('/', $url);
        $host = $urlArr[2];

        return $host;
    }

    /**
     * Check protocol in site
     *
     * @param string
     * @return string
     */
    public static function checkProtocol($url)
    {
        $fp = fsockopen('ssl://'. self::$host, ParserGod::SSL_PORT, $errno, $errstr, 30);
        $result = (!empty($fp)) ? "https://" : "http://";

        return $result;
    }
}
