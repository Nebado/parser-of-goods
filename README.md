# Parser Of Goods

The parser can scrape products from websites on specific URL addresses.
Also, the parser has such features are generating Excel, CSV,
downloading images, and creating zip archives for downloaded images.

![screenshot](src/assets/images/parser_v0.1_6.png)

## Requirements

- php ^7.2
- docker
- web client (any browser what do you prefer)

## Installation

Use the [docker compose](https://docs.docker.com/compose/install/) for defining and running multi-container Docker applications.
Use the package manager [composer](https://getcomposer.org/) for installing application locally.

```
$ docker-compose up -d --build
$ docker exec -it php-parser bash
$ composer install
```

## Usage

- Open your web client in localhost
- Enter your specific URL of the website with the category of products
- To start parsing, you need to fill fields, and for this, you need to
know tags,classes, or id of the elements (name, price, etc).

## Releases

* [ver. 0.1](https://github.com/Nebado/parser-of-goods/releases/tag/0.1)
* [ver. 0.2](https://github.com/Nebado/parser-of-goods/releases/tag/0.2)

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](LICENSE)
