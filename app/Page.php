<?php

ini_set('max_execution_time', '300');

function run($start, $catUrl) {
    $urlArr = explode('/', $catUrl);
    $url_domain = $urlArr[2];

    if ($start == 1 && $catUrl != null) {
        $c = 0;
        $categoryUrl = $catUrl;
        while ($c < 1) {
            $c++;
            $html = Parser::getPage([
                "url" => "$categoryUrl"
            ]);            
            
            if (!empty($html["data"])) {
                $content = $html["data"]["content"];
                phpQuery::newDocument($content);

                // Pagination section
                $pagination = pq('.pagination')->find('a')->attr('href');
                $lengthStrPagination = strlen($pagination);
                $pagination = substr($pagination, 2, $lengthStrPagination);
                $categoryUrl = $catUrl . $pagination;

                // Parsing categories
                $categories = pq(".block-goods-list")->find(".goods-photo");
                $goods = [];
                foreach ($categories as $key => $category){
                    $category = pq($category);
                    $goods[$key] = [
                        "url"  => trim($category->attr("href"))
                    ];
                }

                // Parsing goods
                foreach($goods as $good => $goodsUrl) {
                    foreach ($goodsUrl as $uri) {
                        $urls[] = "".$uri;
                    }
                }

                foreach ($urls as $url) {
                    $htmlGoods[] = Parser::getPage([
                        "url" => "https://".$url_domain.$url
                    ]);
                }

                global $arrGoods;
                for ($i = 0; $i < count($htmlGoods); ++$i) {
                    if(!empty($htmlGoods[$i]["data"])) {
                        $contentGoods[$i] = $htmlGoods[$i]["data"]["content"];
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
                    }                    
                }
                
                
                
                phpQuery::unloadDocuments();
            }
        }

        // Save in Excel
        $phpExcel = new PHPExcel();

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
            $phpExcel->getActiveSheet()->setCellValueExplicit($ceilLetter, $string, PHPExcel_Cell_DataType::TYPE_STRING);
        }

        $i = 2;

        foreach($arrGoods as $row) {
            $phpExcel->getActiveSheet()->setCellValueExplicit("A$i", $row['name'], PHPExcel_Cell_DataType::TYPE_STRING);
            //$string = mb_convert_encoding($string, 'UTF-8', 'Windows-1251');
            $phpExcel->getActiveSheet()->setCellValue("B$i", $row['code']);
            $phpExcel->getActiveSheet()->setCellValue("C$i", $row['price']);
            $description = $row['description'];
            //$string = mb_convert_encoding($string, 'UTF-8', 'Windows-1251');
            $phpExcel->getActiveSheet()->setCellValueExplicit("D$i", $description, PHPExcel_Cell_DataType::TYPE_STRING);
            $phpExcel->getActiveSheet()->setCellValue("E$i", $row['photo'], PHPExcel_Cell_DataType::TYPE_STRING);
            $i++;
        }

        $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(96);
        $phpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
        $phpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $phpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(96);
        $phpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(96);

        $page = $phpExcel->setActiveSheetIndex();
        $page->setTitle('goods');
        $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $filename = "goods.xlsx";

        if (file_exists($filename)) {
            unlink($filename);
        }

        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
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
