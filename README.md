# Parser of Goods

Parser of goods on php with generate Excel file, also downloading images.

WARNING! The parser is in an active development state. If you want to use parser just download source code version 0.1 and use.

* [Version 0.1](https://github.com/Nebado/ParserOfGoods/releases/tag/0.1)

![screenshot](./assets/images/parser_v0.1_3.png)

## Dependencies

- php ^5.2
- php-xml
- php-curl
- php-zip
- php-gb
- phpQuery
- PhpSpreadsheet

## Installation

* Composer

```
$ composer init
$ composer require electrolinux/phpquery
$ composer require phpzip/phpzip
$ composer require phpoffice/phpspreadsheet

Uncomment /* require_once("./vendor/autoload.php"); */ in index.php
and comment require_once("./libs/autoload.php");

```
* Without composer

Uncomment /* require_once("./libs/autoload.php"); */ in index.php
and comment require_once("./vendor/autoload.php");

## Quick start

#### Linux

```
$ php -S localhost:5757

```

#### Windows

Use WAMP, XAMP, MAMP or OpenServer

## Usage

In order to start parsing, you need to get fields, and for this you need to know tags, classes or id of the elements (name, price, etc).
## References

[PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)
