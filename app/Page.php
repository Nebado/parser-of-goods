<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function run($start, $catUrl) {
    $urlArr = explode('/', $catUrl);
    $url_domain = $urlArr[2];

    if ($start == 1 && $catUrl != null) {
        $htmlCat = Parser::getPage([
            "url" => "$catUrl"
        ]);

        if (!empty($htmlCat["data"])) {
            $domCat = phpQuery::newDocument($htmlCat["data"]["content"]);

            // Pagination section
            $pagCount = pq('ul.pagination > li > a')->text();
            $paginationCount = str_replace('>', '', $pagCount);
            $countPages = substr(trim($paginationCount), -1);

            for ($i = 1; $i <= $countPages; $i++) {
                $pagHref = str_replace('./', '', pq('ul.pagination > li > a')->attr('href'));
                $paginationHref = preg_replace("/page=(\d+)/", "page=$i", $pagHref);
                if ($i === 1) {
                    $categoryHref[] = $catUrl;
                } else {
                    $categoryHref[] = $catUrl . $paginationHref;
                }
            }
            phpQuery::unloadDocuments();
        }

        // Use Multi Curl for categories
        $ref = new cURmultiStable;
        $htmlCategories = $ref->runmulticurl($categoryHref);

        //Parsing all categories
        for ($k = 0; $k < count($htmlCategories); ++$k) {
            if (!empty($htmlCategories[$k])) {
                $contentCategories[$k] = $htmlCategories[$k];
                phpQuery::newDocument($contentCategories[$k]);

                $links = pq('.block-goods-list')->find('.goods-name a');

                foreach ($links as $link) {
                    $pqLink = pq($link);
		            $urlGoods[] = "https://".$url_domain.$pqLink->attr('href');
                }
                phpQuery::unloadDocuments();
            }
        }

        // Use Multi Curl
        $ref = new cURmultiStable;
        $htmlGoods = $ref->runmulticurl($urlGoods);

        // Generate goods
        global $arrGoods;
        for ($i = 0; $i < count($htmlGoods); ++$i) {
            if(!empty($htmlGoods[$i])) {
                $contentGoods[$i] = $htmlGoods[$i];
                phpQuery::newDocument($contentGoods[$i]);

                // Section main parsing fields
                $arrGoods[$i]['name'] = trim(pq("._goods-title")->text());
                $arrGoods[$i]['code'] = pq("._goods-id")->text();
                $arrGoods[$i]['price'] = pq("span[itemprop='price']")->text();
                $arrGoods[$i]['description'] = trim(pq("._goods-description-text")->text());
                /* more params... */

                $arrGoods[$i]['photo'] = pq('#goods-top-photo')->attr('href');
                if ($arrGoods[$i]['photo'] == '#') {
                    $arrGoods[$i]['photo'] = pq('#goods_photos a')->attr('href');
                }
                
                phpQuery::unloadDocuments();
            }                    
        }        

        // Save in Excel
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

        // Download images
        $catalog_out_path = "images";
        if(!is_dir($catalog_out_path)) {
            mkdir($catalog_out_path, 0777, true);
        }

        $k = 0;
        for($k = 0; $k < count($arrGoods); $k++) {
            $foto_name = substr($arrGoods[$k]['photo'], (strrpos($arrGoods[$k]['photo'], "/") + 1));
            $photoUrl = 'https:'.$arrGoods[$k]['photo'];

            unlink($catalog_out_path . '/'. $foto_name);
            if (!file_exists($catalog_out_path . "/" . $foto_name)) {
                file_put_contents($catalog_out_path . "/" . $foto_name, file_get_contents($photoUrl));
            }
        }

        unset($_POST['start']);
        unset($_POST['url']);
    } else {
        return;
    }
}

if (isset($_POST['start'])) {
    run($_POST['start'], $_POST['url']);
}
