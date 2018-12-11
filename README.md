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

## Распознавание Рекапчи2 от гугла c использованием прокси (NoCaptchaTask)
| Параметр | Тип | Обязательный | Значение |
|----------|-----|--------------|----------|
| websiteURL | String | Да | Адрес страницы на которой решается капча |
| websiteKey | String |	Да | Ключ-индентификатор рекапчи на целевой странице |
| websiteSToken	| String | Нет | Секретный токен для предыдущей версии рекапчи. В большинстве случаев сайты используют новую версию и этот токен не требуется |
| proxyType	| String | Да | 'http' - обычный http/https прокси, 'socks4' - socks4 прокси, 'socks5' - socks5 прокси |
| proxyAddress | String	| Да| IP адрес прокси ipv4/ipv6 |
| proxyPort	| Integer | Да | Порт прокси |
| proxyLogin | String | Нет | Логин от прокси-сервера |
| proxyPassword	| String | Нет | Пароль от прокси-сервера |
| userAgent | String | Да | User-Agent браузера, используемый в эмуляции |
| cookies | String	| Нет | Дополнительные cookies. Формат: cookiename1=cookievalue1; cookiename2=cookievalue2 |
| isInvisible |	Boolean	| Нет | Указать что рекапча невидимая |
```php
<?php
use unapi\anticaptcha\antigate\task\NoCaptchaTask;

echo $service->resolve(
   new NoCaptchaTask([
                   'websiteURL' => 'http://mywebsite.com/recaptcha/test.php',
                   'websiteKey' => '6Lc_aCMTAAAAABx7u2N0D1XnVbI_v6ZdbM6rYf16',
                   'proxyType' => 'http',
                   'proxyAddress' => '8.8.8.8',
                   'proxyPort' => 8080,
                   'proxyLogin' => 'proxyLoginHere',
                   'proxyPassword' => 'proxyPasswordHere',
                   'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36'
               ])
)->wait()->getCode();
```