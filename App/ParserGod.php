<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\ParserInterface;

namespace App;

class ParserGod implements \ParserInterface
{
    public $start;
    public $stop;
    
    public function __construct()
    {
        $this->start = false;
    }

    public function process($start, $catUrl)
    {
        $urlArr = explode('/', $catUrl);
        $url_domain = $urlArr[2];

        if ($start == true && $catUrl != null) {
            $htmlCat = \Parser::getPage([
                "url" => "$catUrl"
            ]);

            if (!empty($htmlCat["data"])) {
                $domCat = \phpQuery::newDocument($htmlCat["data"]["content"]);

                // Pagination section
                $pagCount = pq('ul.pagination > li > a')->text();
                $paginationCount = str_replace('>', '', $pagCount);
                $countPages = substr(trim($paginationCount), -1);

                if ($countPages == '') {
                    $categoryHref[] = $catUrl;
                }

                for ($i = 1; $i <= $countPages; $i++) {
                    $pagHref = str_replace('./', '', pq('ul.pagination > li > a')->attr('href'));
                    $paginationHref = preg_replace("/page=(\d+)/", "page=$i", $pagHref);
                    if ($i === 1) {
                        $categoryHref[] = $catUrl;
                    } else {
                        $categoryHref[] = $catUrl . $paginationHref;
                    }
                }
                \phpQuery::unloadDocuments();
            }

            // Use Multi Curl for categories
            $ref = new \cURmultiStable;
            $htmlCategories = $ref->runmulticurl($categoryHref);

            $cardGood = isset($_POST["card_good"]) ? $_POST["card_good"] : "";

            // Parsing all categories
            for ($k = 0; $k < count($htmlCategories); ++$k) {
                if (!empty($htmlCategories[$k])) {
                    $contentCategories[$k] = $htmlCategories[$k];
                    \phpQuery::newDocument($contentCategories[$k]);

                    $links = pq($cardGood);

                    foreach ($links as $link) {
                        $pqLink = pq($link);
		                $urlGoods[] = "https://".$url_domain.$pqLink->attr('href');
                    }
                    \phpQuery::unloadDocuments();
                }
            }

            // Use Multi Curl
            $ref = new \cURmultiStable;
            $htmlGoods = $ref->runmulticurl($urlGoods);

            $name = isset($_POST['name']) ? $_POST['name'] : '';     
            $code = isset($_POST['code']) ? $_POST['code'] : '';
            $price = isset($_POST['price']) ? $_POST['price'] : '';
            $photo = isset($_POST['photo']) ? $_POST['photo'] : '';
            $desc = isset($_POST['description']) ? $_POST['description'] : '';
            
            $_SESSION["name"] = isset($_POST["name"]) ? $_POST["name"] : "";
            $_SESSION["code"] = isset($_POST["code"]) ? $_POST["code"] : "";
            $_SESSION["price"] = isset($_POST["price"]) ? $_POST["price"] : "";
            $_SESSION["photo"] = isset($_POST["photo"]) ? $_POST["photo"] : "";
            $_SESSION["description"] = isset($_POST["description"]) ? $_POST["description"] : "";
            $_SESSION["card_good"] = isset($_POST["card_good"]) ? $_POST["card_good"] : "";
            $_SESSION["url"] = isset($_POST["url"]) ? $_POST["url"] : "";

            // Generate goods
            global $arrGoods;
            for ($i = 0; $i < count($htmlGoods); ++$i) {
                if(!empty($htmlGoods[$i])) {
                    $contentGoods[$i] = $htmlGoods[$i];
                    \phpQuery::newDocument($contentGoods[$i]);

                    // Section main parsing fields
                    $arrGoods[$i]['name'] = trim(pq($name)->text());
                    $arrGoods[$i]['code'] = pq($code)->text();
                    $arrGoods[$i]['price'] = pq($price)->text();
                    $arrGoods[$i]['description'] = trim(pq($desc)->text());
                    /* more params... */

                    $arrGoods[$i]['photo'] = pq($photo)->attr('href');
                    if ($arrGoods[$i]['photo'] == '') {
                        $arrGoods[$i]['photo'] = pq($photo)->attr('src');
                    }
                    
                    \phpQuery::unloadDocuments();
                }                    
            }

            // Save in Excel

            if (isset($_POST["excel"]) && $_POST["excel"] == "1") {
                $phpExcel = new \Spreadsheet();

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

            // Download images

            if (isset($_POST["image"]) && $_POST["image"] == "1") {
                $catalogOutPath = "images";
                if(!is_dir($catalogOutPath)) {
                    mkdir($catalogOutPath, 0777, true);
                }            
                
                $k = 0;
                for($k = 0; $k < count($arrGoods); $k++) {
                    $photoName = substr($arrGoods[$k]['photo'], (strrpos($arrGoods[$k]['photo'], "/") + 1));
                    $photoUrl = $arrGoods[$k]['photo'];

                    $fullPhotoPathName = $catalogOutPath . '/'. $photoName;
                    $arrFullPhotos[] = $fullPhotoPathName;

                    if (!file_exists($fullPhotoPathName)) {                    
                        file_put_contents($fullPhotoPathName, file_get_contents($photoUrl));
                    }
                }

                // Zip archive images

                $zipPath = "zip";
                if(!is_dir($zipPath)) {
                    mkdir($zipPath, 0777, true);
                }
                
                $zip = new ZipArchive();
                $filenameZip = $zipPath . DIRECTORY_SEPARATOR ."images" . ".zip";
                $zip->open($filenameZip, ZIPARCHIVE::CREATE);
                $files = scandir('images');
                foreach ($files as $file) {
                    if ($file == '.' || $file == '..') {continue;}
                    $f = 'images'.DIRECTORY_SEPARATOR.$file;
                    $zip->addFile($f);
                }
                $zip->close();
            }

            unset($_POST['start']);
            unset($_POST['url']);
        } else {
            return;
        }
    }
}