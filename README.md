# Parser of Goods

Parser of goods on php with generate Excel file, also downloading images.

WARNING! The parser is in an active development state. If you want to use parser just download source code version 0.1 and use.

* [ver. 0.1](https://github.com/Nebado/ParserOfGoods/releases/tag/0.1)
* [ver. 0.2](https://github.com/Nebado/ParserOfGoods/releases/tag/0.2)

![screenshot](src/assets/images/parser_v0.1_6.png)

## Requirements

- php ^7.2
- php-xml
- php-curl
- php-zip
- php-gb
- clue/buzz-react
- symfony/dom-crawler
- symfony/css-selector

## Quick start

```
$ docker-compose run up -d --build
$ composer install
```

## Usage

In order to start parsing, you need to fill fields, and for this you need to know tags, classes or id of the elements (name, price, etc).

## References

[PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)
