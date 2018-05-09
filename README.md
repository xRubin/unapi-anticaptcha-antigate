[![Build Status](https://travis-ci.org/xRubin/unapi-anticaptcha-antigate.svg?branch=master)](https://travis-ci.org/xRubin/unapi-anticaptcha-antigate)
# Unapi Anticaptcha Antigate
Модуль для распознования капчи через сервис [Antigate.com](http://antigate.com)

Являтся частью библиотеки [Unapi](https://github.com/xRubin/unapi)

Реализует **unapi\anticaptcha\common\AnticaptchaInterface**

## Установка
```bash
$ composer require unapi/anticaptcha-antigate
```

## Инициализация сервиса
Ключ выдается при регистрации в сервисе [Antigate.com](http://antigate.com)
```php
<?php
use unapi\anticaptcha\antigate\AntigateService;

$service = new AntigateService([
    'key' => YOUR_ANTIGATE_KEY,
]);
```

## Распознавание графической капчи
```php
<?php
use unapi\anticaptcha\common\task\ImageTask;

echo $service->resolve(
    new ImageTask([
        'body' => file_get_contents(__DIR__ . '/fixtures/captcha/mf4azc.png'),
        'minLength' => 6,
        'maxLength' => 6,
    ])
)->wait()->getCode();
```

## Распознавание Рекапчи2 от гугла
siteURL	String  Адрес страницы на которой решается капча

siteKey	String	Ключ-индентификатор рекапчи на целевой странице. <div class="g-recaptcha" data-sitekey="ВОТ_ЭТОТ"></div>
```php
<?php
use unapi\anticaptcha\common\task\ReCaptcha2Task;

echo $service->resolve(
    new ReCaptcha2Task([
        'siteUrl' => 'http://mywebsite.com/recaptcha/test.php',
        'siteKey' => '6Lc_aCMTAAAAABx7u2N0D1XnVbI_v6ZdbM6rYf16',
    ])
)->wait()->getCode();
```
