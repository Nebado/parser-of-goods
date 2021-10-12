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
    private const SSL_PORT = 443;

    public static $host;
    public static $protocol;

    private $client;
    private $loop;
    private $links = [];
    private $errors = [];
    private $parsedProducts = [];
    private $request = [];
    private $session = [];
    private $products = [];

    public function __construct()
    {
        header('Content-type: application/json');

        $_REQUEST = (array)json_decode(file_get_contents("php://input"));
        
        $this->saveRequest();
    }

    public function run()
    {
        $this->loop = Factory::create();
        $this->client = new Browser($this->loop);

        if ($this->request['url'] === null) {
            return false;
        }

        self::$host = self::getHost($this->request['url']);
        self::$protocol = self::checkProtocol($this->request['url']);
        
        if (empty($this->request['pagination_url'])) {
            $categoryUrls[] = $this->request['url'];
        } else {
            $categoryUrls = $this->getUrlsOfPagination(
                $this->request['pagination_url'],
                $this->request['quantity_pages']);
        }

        $urlProducts = $this->getProductUrls($categoryUrls);

        if (empty($urlProducts)) {
            $this->errors['errors'][] = 'List of product urls is empty';            
        }

        $this->writeToFile($categoryUrls, 'category');
        $this->writeToFile($urlProducts, 'product');
        $this->parseProducts($urlProducts);
        
        if (empty($this->parsedProducts)) {
            $this->errors['errors'][] = 'List of parsed products is empty';
        }
        
        $this->saveSession($this->errors);
        $this->download();

        return $this->getJson([
            'products'   => $this->parsedProducts,
            'zipFile'    => $this->session['has_zip_file'],
            'excelFile'  => $this->session['has_excel_file']
        ]);
    }

    /**
     * @param string $paginationUrl
     * @param string $quantiy
     *
     * @return array
     */
    private function getPaginationUrls(string $paginationUrl, string $quantity) : array
    {
        $iterator = 1;
        $categoryHref = [];
        $quantity = intval($quantity);

        if ($quantity > 0) {
            if ($paginationUrl[strlen($paginationUrl)-1] == '/') {
                $paginationUrl = substr($paginationUrl, 0, -1);
            }

            while (is_numeric(substr($paginationUrl, -$iterator)) != false) {
                $iterator++;
            }

            $numberOfDigit = $iterator - 1;

            for ($i = 1; $i <= $quantity; ++$i) {
                $paginationHref = substr_replace($paginationUrl, $i, -$numberOfDigit);
                $categoryHref[] = $paginationHref;
            }
        }

        return $categoryHref;
    }

    /**
     * @param array $urls
     *
     * @return array|null
     */
    private function getProductUrls(array $urls) : array
    {
        $productUrls = [];
        $hrefs = [];
        
        if (empty($urls)) {
            return null;
        }

        foreach ($urls as $url) {
            $this->client->get($url)->then(
                function (\Psr\Http\Message\ResponseInterface $response) {
                    $crawler = new Crawler((string) $response->getBody());
                    $this->links[] = $crawler->filter((string) $this->request['product_card_name'])->extract(['href']);
            });
        }
        $this->loop->run();

        if (count($this->links) <= 0) {
            $this->errors['errors'][] = "Product urls list is empty";
        }

        foreach ($this->links as $key => $value) {
            foreach ($value as $href) {
                $hrefs[] = $href;
            }
        }

        $hrefs = array_unique($hrefs);

        foreach ($hrefs as $href) {
            if (substr($href, 0, 4) === 'http') {
                $productUrls[] = $href;
            } else {
                $productUrls[] = self::$protocol.self::$host.$href;
            }
        }

        return $productUrls;
    }

    /**
     * @param array $urls
     *
     * @return void
     */
    private function parseProducts(array $urls) : void
    {
        if (empty($urls)) {
            return;
        }
        
        foreach ($urls as $url) {
            $this->client->get($url)->then(
                function (\Psr\Http\Message\ResponseInterface $response) {
                    $this->parsedProducts[] = $this->scrapFromHtml((string) $response->getBody());
            });
        }
        $this->loop->run();
    }

    /**
     * @param string $html
     *
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

        if (!empty($this->request['image'])) {
            $link = $crawler->filter(trim($this->request['image']));
            $image = $link->filter('img')->attr('src');
        } else {
            $image = '';
        }

        if (!empty($this->request['description'])) {
            $description = $crawler->filter(trim($this->request['description']))->text();
        } else {
            $description = '';
        }

        if (!empty($this->request['field["custom"]'])) {
            if (is_array($this->request['field["custom"]'])) {
                foreach ($this->request['field["custom"]'] as $field) {
                    $fields[] = $crawler->filter(trim($field))->text();
                }
            } else {
                $fields[] = $crawler->filter(trim($this->request['field["custom"]']))->text();
            }
        }

        return [
            'name'        => $name,
            'code'        => $code,
            'price'       => $price,
            'description' => $description,
            'image'       => $image,
            'fields'      => $fields
        ];
    }

    /**
     *
     * @return void
     */
    private function download() : void
    {
        $this->saveSession([
            'has_zip_file' => false,
            'has_excel_file' => false
        ]);
        
        $uploadPath = "src/upload";
        
        if(!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if (isset($this->request["download_excel"])) {
            $this->generateExcel($this->parsedProducts);
        }

        if (isset($this->request["download_image"])) {
            $this->downloadImages($this->parsedProducts);
        }
    }

    /**
     * @param array $products
     *
     * @return void
     */
    private function generateExcel(array $products) : void
    {
        $phpExcel = new Spreadsheet();
        $ceils = array(
            'A', 'B', 'C', 'D', 'E',
            'F', 'G', 'H', 'I', 'K',
            'L', 'M', 'N', 'O', 'P',
            'Q', 'R', 'S', 'T', 'U',
            'V', 'W', 'X', 'Y', 'Z');
        $names = array_keys($products[0]);
        $fields = $products[0]['fields'];
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
        for ($i = 0; $i < count($products); ++$i) {
            $phpExcel->getActiveSheet()->setCellValueExplicit("A".$k, $products[$i]['name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $phpExcel->getActiveSheet()->setCellValue("B".$k, $products[$i]['code']);
            $phpExcel->getActiveSheet()->setCellValue("C".$k, $products[$i]['price']);
            $phpExcel->getActiveSheet()->setCellValueExplicit("D".$k, $products[$i]['description'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $phpExcel->getActiveSheet()->setCellValue("E".$k, $products[$i]['image'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $j = 5;
            foreach ($products[$i]['fields'] as $field) {
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
        
        $this->saveSession(['has_excel_file' => true]);
    }

    /**
     * @param array $products
     * 
     * @return void
     */
    private function downloadImages(array $products) : void
    {
        $catalogImagePath = "src/upload/images";
        if(!is_dir($catalogImagePath)) {
            mkdir($catalogImagePath, 0777, true);
        }

        for($k = 0; $k < count($products); $k++) {
            $imageName = substr($products[$k]['image'], (strrpos($products[$k]['image'], "/") + 1));
            
            if (substr($products[$k]['image'], 0, 4) === 'http') {
                $imageUrl = $products[$k]['image'];
            } else {
                $imageUrl = self::$protocol.self::$host.$products[$k]['image'];
            }

            $imagePath = $catalogImagePath . DIRECTORY_SEPARATOR . $imageName;

            if (file_exists($imagePath) and filesize($imagePath) > 0) {
                continue;
            } else {
                file_put_contents($imagePath, file_get_contents($imageUrl));
            }
        }

        $this->zipUp();
    }

    /**
     *
     * @return void
     */
    private function zipUp() : void
    {        
        $zipPath = "src/upload/zip";
        $catalogImagePath = "src/upload/images";

        if(!is_dir($zipPath)) {
            mkdir($zipPath, 0777, true);
        }

        $zip = new ZipArchive();
        $filenameZip = $zipPath . DIRECTORY_SEPARATOR ."images" . ".zip";
        /* $zip = new Zip();
         * $zip->saveZipFile($filenameZip); */
        $result = $zip->open($filenameZip, ZipArchive::CREATE);
        $files = scandir($catalogImagePath);

        if ($result === true) {
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {continue;}
                $filePath = $catalogImagePath . DIRECTORY_SEPARATOR . $file;
                $zip->addFile($filePath);
            }
            $zip->close();

            $this->saveSession(['has_zip_file' => true]);
        }
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private static function getHost(string $url) : string
    {
        $urlArr = explode('/', $url);
        $host = $urlArr[2];

        return $host;
    }

    /**
     * @param string
     * 
     * @return string
     */
    private static function checkProtocol(string $url) : string
    {
        $fp = fsockopen('ssl://'. self::$host, ParserGodController::SSL_PORT, $errno, $errstr, 30);
        $result = (!empty($fp)) ? "https://" : "http://";

        return $result;
    }

    /**
     * Save request data to session
     *
     * @return void
     */
    private function saveRequest() : void
    {
        
        if (!empty($_REQUEST)) {
            foreach ($_REQUEST as $key => $value) {
                $this->request[$key] = $value;
            }
            $this->saveSession($this->request);
        }
    }

    /**
     * @param array $data
     * 
     * @return void
     */
    private function saveSession(array $data) : void
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $_SESSION[$key] = $value;
                $this->session[$key] = $value;
            }
        }
    }

    /**
     * @param array $urls
     * @param string $name
     *
     * @return void
     */
    private function writeToFile(array $urls, string $name) : void
    {
        $tmpPath = "src/tmp/";
        $extension = ".txt";
        $filename = $tmpPath . $name . $extension;

        if(!is_dir($tmpPath)) {
            mkdir($tmpPath, 0777, true);
        }

        $data = json_encode($urls);
        
        file_put_contents($filename, $data);
    }

    /**
     * @param array $urls
     *
     * @return void
     */
    private function getJson(array $data) : void
    {
        echo json_encode($data);
    }    
}
