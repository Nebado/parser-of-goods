<?php

namespace App;

use Clue\React\Buzz\Browser;
use Symfony\Component\DomCrawler\Crawler;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PHPZip\Zip\File\Zip as Zip;
use \zipArchive;

class ParserGod implements ParserGodInterface
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

	public function __construct(Browser $client, $loop)
	{
		$this->client = $client;
		$this->loop = $loop;
	}

	/**
	 * Run application
	 *
	 * @param integer
	 * @param string
	 */
	public function run($start, $catUrl)
	{
		if ($start == true && $catUrl != null) {

			// Get a hostname
			self::$host = self::getHost($catUrl);

			// Check protocol
			self::$protocol = self::checkProtocol($catUrl);

			// Registration request data and session
			$this->saveRequest();
			$this->saveSession();

			// Get category urls if exists pagination, otherwise,
			// put category url
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

			// Write all urls in tmp file
			$this->writeUrlsCategory($categoryUrls);
			$this->writeUrlsProducts($urlProducts);

			global $arrGoods;

			$this->parseProducts($urlProducts);
			$arrGoods = $this->parsedProduct;

			$uploadPath = "src/upload";
			if(!is_dir($uploadPath)) {
				mkdir($uploadPath, 0777, true);
			}

			$this->generateExcel($arrGoods);

			$this->downloadImages($arrGoods);

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
		if (isset($_POST["image"]) && $_POST["image"] == "1") {
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
				$arrFullPhotos[] = $fullPhotoPathName;

				if (!file_exists($fullPhotoPathName)) {
					file_put_contents($fullPhotoPathName, file_get_contents($photoUrl));
				} else {
					unlink($fullPhotoPathName);
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
		$fp = fsockopen('ssl://'. self::$host, ParserGod::SSL_PORT, $errno, $errstr, 30);
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
