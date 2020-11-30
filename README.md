# Parser of Goods

Parser of goods on php with generate Excel file, also downloading images.

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
$ composer require electrolinux/phpquery

```
* Without composer

Uncomment /* require_once("./libs/autoload.php"); */ in index.php
and comment require_once("./vendor/autoload.php");

![screenshot](./assets/wocomposer.png)

## Quick start

#### Linux

```
$ php -S localhost:5757

```

#### Windows

Use WAMP, XAMP, MAMP or OpenServer

## Usage

In order to start parsing, you need to maintain fields, and for this you need to know the classes or id of the elements (name, price, etc).

## References

[PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)
