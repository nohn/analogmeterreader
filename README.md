# PHP Analog Meter Reader

Reads analog meters with PHP.

| 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 |
|---|---|---|---|---|---|---|---|---|---|
| ![0](tests/resources/images/0/nohn1.png) | ![1](tests/resources/images/1/nohn1.png) | ![2](tests/resources/images/2/nohn1.png)| ![3](tests/resources/images/3/nohn1.png)| ![4](tests/resources/images/4/nohn1.png)| ![5](tests/resources/images/5/nohn1.png)| ![6](tests/resources/images/6/nohn1.png)| ![7](tests/resources/images/7/nohn1.png)| ![8](tests/resources/images/8/nohn1.png)| ![9](tests/resources/images/9/nohn1.png)

![CI](https://github.com/nohn/analogmeterreader/workflows/CI/badge.svg) [![Latest Stable Version](https://poser.pugx.org/nohn/analogmeterreader/v)](//packagist.org/packages/nohn/analogmeterreader) [![Total Downloads](https://poser.pugx.org/nohn/analogmeterreader/downloads)](//packagist.org/packages/nohn/analogmeterreader) [![Latest Unstable Version](https://poser.pugx.org/nohn/analogmeterreader/v/unstable)](//packagist.org/packages/nohn/analogmeterreader) [![License](https://poser.pugx.org/nohn/analogmeterreader/license)](//packagist.org/packages/nohn/analogmeterreader)

## Installation

    $ composer require nohn/analogmeterreader

## Usage

### Passing an Imagick object

```php
use nohn\AnalogMeterReader\AnalogMeter;

$amr = new AnalogMeter($imagick_object, 'r');
echo $amr->getValue();
```

### Passing a file path

```php
use nohn\AnalogMeterReader\AnalogMeter;

$amr = new AnalogMeter($path_to_image_file, 'r');
echo $amr->getValue();
```

### Practical use example

See [nohn/watermeter](https://github.com/nohn/watermeter) for a real world use case.

## How to contribute

You can contribute to this project by:

* Opening an [Issue](https://github.com/nohn/analogmeterreader/issues) if you found a bug or wish to propose a new feature
* Placing a [Pull Request](https://github.com/nohn/analogmeterreader/pulls) with [test Images](tests/resources/images/), bugfixes, new features etc.

## License

analogmeterreader is released under the [GNU Affero General Public License](LICENSE).