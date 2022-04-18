# dither-php
A small library for converting full-color image to dither image.

## Installation
Require the mion/dither package in your composer.json and update your dependencies:
```shell
composer require mion/dither
```

## Usage
```php
use Dither\Dither;

$image = imagecreatefrompng('demo.png');

$dither = new Dither;
$dither->setPalette(['#000000', '#FFFFFF', '#00FF00', '#0000FF', '#FF0000', '#FFFF00', '#FF8000']);
$output = $dither->process($image, 800, 600);
```

## About Mion

Mion is a software solutions startup, specialized in integrated enterprise solutions for SMEs established in Guangzhou, China since September 2016.


## License

This software is released under [The MIT License (MIT)](LICENSE).