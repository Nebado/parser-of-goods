<?php

namespace App\Controllers;

use React\EventLoop\Factory;
use Clue\React\Buzz\Browser;
use Symfony\Component\DomCrawler\Crawler;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PHPZip\Zip\File\Zip as Zip;
use \zipArchive;

class ParserGodController
{
    const SSL_PORT = 443;

    public static $host;
    public static $protocol;

    private $client;
    private $loop;
    private $links = [];
    private $parsedProduct = [];
    private $request = [];
    private $session = [];
    private $arrGoods = [];

    /**
     * Run application
     *
     * @param integer
     * @param string
     */
    public function run()
    {
        $this->loop = Factory::create();
        $this->client = new Browser($this->loop);

        // TODO
        $_REQUEST = (array)json_decode(file_get_contents("php://input"));
        $catUrl = $_REQUEST['url'];

        if ($catUrl != null) {

            self::$host = self::getHost($catUrl);
            self::$protocol = self::checkProtocol($catUrl);

            $this->saveRequest();
            $this->saveSession();

            if (!empty($this->request['pagination_url']) &&
                intval($this->request['quantity_pages']) > 0)
            {
                $categoryUrls = $this->getUrlsOfPagination(
                    $this->request['pagination_url'],
                    $this->request['quantity_pages']);
            } else {
                $categoryUrls[] = $catUrl;
            }

            $urlProducts = $this->getUrlsProducts($categoryUrls);

            $this->writeUrlsCategory($categoryUrls);
            $this->writeUrlsProducts($urlProducts);

            $this->parseProducts($urlProducts);

            $uploadPath = "src/upload";
            if(!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $this->generateExcel($this->parsedProduct);
            $this->downloadImages($this->parsedProduct);

            echo json_encode($this->parsedProduct);

        } else {
            return false;
        }
    }

    /**
     * Get all url pages of pagination
     *
     * @param string $paginationUrl
     * @param integer $quantiyPages
     * @return array
     */
    private function getUrlsOfPagination($paginationUrl, $quantityPages) : array
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
     * @param array $htmlCategories
     * @return array
     */
    private function getUrlsProducts(array $urls) : array
    {
        $urlProducts = [];

        if (!empty($urls)) {
            foreach ($urls as $url) {
                $this->client->get($url)->then(
                    function (\Psr\Http\Message\ResponseInterface $response) {
                        $crawler = new Crawler((string) $response->getBody());
                        $this->links[] = $crawler->filter((string) $this->request['product_card_name'])->extract(['href']);
                });
            }
            $this->loop->run();

            foreach ($this->links as $key => $value) {
                foreach ($value as $href) {
                    $hrefs[] = $href;
                }
            }

            $hrefs = array_unique($hrefs);

            foreach ($hrefs as $href) {
                if (substr($href, 0, 4) === 'http') {
                    $urlProducts[] = $href;
                } else {
                    $urlProducts[] = self::$protocol.self::$host.$href;
                }
            }
        }

        return $urlProducts;
    }

    /**
     * Parse all products
     *
     * @param array
     */
    private function parseProducts(array $urls) : void
    {
        if (!empty($urls)) {
            foreach ($urls as $url) {
                $this->client->get($url)->then(
                    function (\Psr\Http\Message\ResponseInterface $response) {
                        $this->parsedProduct[] = $this->scrapFromHtml((string) $response->getBody());
                });
            }
            $this->loop->run();
        }
    }

    /**
     * Extract data from html
     *
     * @param string
     * @return array
     */
    private function scrapFromHtml(string $html) : array
    {
        $fields = [];
        $crawler = new Crawler($html);

        if (!empty($this->request['name'])) {
            $name = $crawler->filter(trim($this->request['name']))->text();
        } else {
            $name = '';
        }

        if (!empty($this->request['code'])) {
            $code = $crawler->filter(trim($this->request['code']))->text();
        } else {
            $code = '';
        }

        if (!empty($this->request['price'])) {
            $price = $crawler->filter(trim($this->request['price']))->text();
        } else {
            $price = '';
        }

        if (!empty($this->request['photo'])) {
            $link = $crawler->filter(trim($this->request['photo']));
            $photo = $link->filter('img')->attr('src');
        } else {
            $photo = '';
        }

        if (!empty($this->request['description'])) {
            $description = $crawler->filter(trim($this->request['description']))->text();
        } else {
            $description = '';
        }

        if (!empty($this->request['field'])) {
            foreach ($this->request['field'] as $field) {
                $fields[] = $crawler->filter(trim($field))->text();
            }
        }

        return [
            'name'        => $name,
            'code'        => $code,
            'price'       => $price,
            'description' => $description,
            'photo'       => $photo,
            'fields'      => $fields
        ];
    }

    /**
     * Generate Excel file
     *
     * @param array
     */
    private function generateExcel($arrGoods) : void 
    {
        if (isset($this->request["excel"]) && $this->request["excel"] == "1") {
            $phpExcel = new Spreadsheet();

            $ceils = array(
                'A', 'B', 'C', 'D', 'E',
                'F', 'G', 'H', 'I', 'K',
                'L', 'M', 'N', 'O', 'P',
                'Q', 'R', 'S', 'T', 'U',
                'V', 'W', 'X', 'Y', 'Z');
            $names = array_keys($arrGoods[0]);
            $fields = $arrGoods[0]['fields'];

            $titles = array();

            for ($i = 0; $i < count($names); ++$i) {
                if ($names[$i] == 'fields') {
                    $j = 1;
                    foreach ($fields as $field) {
                        $titles[] = array(
                            'name' => "field $j",
                            'ceil' => $ceils[$i]
                        );
                        ++$i;
                        ++$j;
                    }
                } else {
                    $titles[] = array(
                        'name' => $names[$i],
                        'ceil' => $ceils[$i]
                    );
                }
            }

            for ($i = 0; $i < count($titles); $i++) {
                $string = $titles[$i]['name'];
                $ceilLetter = $titles[$i]['ceil'] . 1;
                $phpExcel->getActiveSheet()->setCellValueExplicit($ceilLetter, $string, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }

            $k = 2;
            for ($i = 0; $i < count($arrGoods); ++$i) {
                $phpExcel->getActiveSheet()->setCellValueExplicit("A".$k, $arrGoods[$i]['name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $phpExcel->getActiveSheet()->setCellValue("B".$k, $arrGoods[$i]['code']);
                $phpExcel->getActiveSheet()->setCellValue("C".$k, $arrGoods[$i]['price']);
                $phpExcel->getActiveSheet()->setCellValueExplicit("D".$k, $arrGoods[$i]['description'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $phpExcel->getActiveSheet()->setCellValue("E".$k, $arrGoods[$i]['photo'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $j = 5;
                foreach ($arrGoods[$i]['fields'] as $field) {
                    $phpExcel->getActiveSheet()->setCellValue($ceils[$j].$k, $field, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    ++$j;
                }
                ++$k;
            }

            $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(28);
            $phpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(28);
            $phpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(28);
            $phpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(28);
            $phpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(28);

            $page = $phpExcel->setActiveSheetIndex(0);
            $page->setTitle('goods');
            $objWriter = new Xlsx($phpExcel);
            $filename = "src/upload/goods.xlsx";

            if (file_exists($filename)) {
                unlink($filename);
            }

            $objWriter->save($filename);
        }
    }

    /**
     * Download images
     *
     * @param array
     */
    private function downloadImages($arrGoods) : void
    {
        if (isset($this->request["image"]) && $this->request["image"] == "1") {
            $catalogOutPath = "src/upload/images";
            if(!is_dir($catalogOutPath)) {
                mkdir($catalogOutPath, 0777, true);
            }

            $k = 0;
            for($k = 0; $k < count($arrGoods); $k++) {
                $photoName = substr($arrGoods[$k]['photo'], (strrpos($arrGoods[$k]['photo'], "/") + 1));

                if (substr($arrGoods[$k]['photo'], 0, 4) === 'http') {
                    $photoUrl = $arrGoods[$k]['photo'];
                } else {
                    $photoUrl = self::$protocol.self::$host.$arrGoods[$k]['photo'];
                }

                $fullPhotoPathName = $catalogOutPath . DIRECTORY_SEPARATOR . $photoName;

                if (file_exists($fullPhotoPathName) and filesize($fullPhotoPathName) > 0) {
                    continue;
                } else {
                    file_put_contents($fullPhotoPathName, file_get_contents($photoUrl));
                }
            }

            $this->zipUp();
        }
    }

    /**
     * Create zip file for images
     */
    private function zipUp() : void
    {
        $zipPath = "src/upload/zip";
        $imagesPath = "src/upload/images";

        if(!is_dir($zipPath)) {
            mkdir($zipPath, 0777, true);
        }

        $zip = new ZipArchive();
        $filenameZip = $zipPath . DIRECTORY_SEPARATOR ."images" . ".zip";
        /* $zip = new Zip();
         * $zip->saveZipFile($filenameZip); */
        $res = $zip->open($filenameZip, ZipArchive::CREATE);
        $files = scandir($imagesPath);

        if ($res === TRUE) {
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {continue;}
                $f = $imagesPath.DIRECTORY_SEPARATOR.$file;
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
    public static function getHost(string $url) : string
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
    public static function checkProtocol(string $url) : string
    {
        $fp = fsockopen('ssl://'. self::$host, ParserGodController::SSL_PORT, $errno, $errstr, 30);
        $result = (!empty($fp)) ? "https://" : "http://";

        return $result;
    }

    /**
     * Registration request data
     */
    private function saveRequest() : void
    {
        if (!empty($_REQUEST)) {
            foreach ($_REQUEST as $key => $value) {
                $this->request[$key] = $value;
            }
        }
    }

    /**
     * Registration session data
     */
    private function saveSession() : void
    {
        if (!empty($this->request)) {
            foreach ($this->request as $key => $value) {
                $_SESSION[$key] = $value;
            }
        }
    }

    /**
     * Write all category urls in file
     *
     * @param array
     */
    private function writeUrlsCategory(array $urls) : void
    {
        $tmpPath = "src/tmp/";
        $filename = $tmpPath.'category.txt';

        if(!is_dir($tmpPath)) {
            mkdir($tmpPath, 0777, true);
        }

        $data = json_encode($urls);
        file_put_contents($filename, $data);
    }

    /**
     * Write all products urls in file
     *
     * @param array
     */
    private function writeUrlsProducts(array $urls) : void
    {
        $tmpPath = "src/tmp/";
        $filename = $tmpPath.'products.txt';

        $data = json_encode($urls);
        file_put_contents($filename, $data);
    }
}
