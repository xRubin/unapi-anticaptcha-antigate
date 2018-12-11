<?php

namespace unapi\anticaptcha\antigate\task;

use unapi\anticaptcha\common\AnticaptchaTaskInterface;

class NoCaptchaTask implements AnticaptchaTaskInterface
{
    const TYPE = 'NoCaptchaTask';

    /** @var string адрес страницы на которой решается капча */
    private $websiteURL;

    /** @var string ключ-индентификатор рекапчи на целевой странице. <div class="g-recaptcha" data-sitekey="ВОТ_ЭТОТ"></div> */
    private $websiteKey;

    /** @var string|null Секретный токен для предыдущей версии рекапчи. В большинстве случаев сайты используют новую версию и этот токен не требуется. */
    private $websiteSToken;

    /**
     * @var string
     * 'http' - обычный http/https прокси
     * 'socks4' - socks4 прокси
     * 'socks5' - socks5 прокси
     */
    private $proxyType;

    /**
     * @var string
     * IP адрес прокси ipv4/ipv6. Не допускается:
     * использование имен хостов.
     * использование прозрачных прокси (там где можно видеть IP клиента)
     * использование прокси на локальных машинах
     */
    private $proxyAddress;

    /** @var int Порт прокси */
    private $proxyPort;

    /** @var string|null Логин от прокси-сервера */
    private $proxyLogin;

    /** @var string|null Пароль от прокси-сервера */
    private $proxyPassword;

    /** @var string User-Agent браузера, используемый в эмуляции. Необходимо использовать подпись современного браузера, иначе Google будет возвращать ошибку, требуя обновить браузер. */
    private $userAgent;

    /**
     * @var string|null
     * Дополнительные cookies которые мы должны использовать во время взаимодействия с целевой страницей.
     * Формат: cookiename1=cookievalue1; cookiename2=cookievalue2
     */
    private $cookies;

    /**
     * @var bool|null
     * Указать что рекапча невидимая. Флаг отобразит соответствующий виджет рекапчи у наших работников.
     * В большинстве случаев флаг указывать не нужно, т.к. невидимая рекапча распознается автоматически, но на это требуется несколько десятков задач для обучения системы.
     */
    private $isInvisible;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['websiteURL'])) {
            throw new \InvalidArgumentException('websiteURL required');
        } else {
            $this->setWebsiteURL($config['websiteURL']);
        }

        if (!isset($config['websiteKey'])) {
            throw new \InvalidArgumentException('websiteKey required');
        } else {
            $this->setWebsiteKey($config['websiteKey']);
        }

        if (isset($config['websiteSToken'])) {
            $this->setWebsiteSToken($config['websiteSToken']);
        }

        if (!isset($config['proxyType'])) {
            throw new \InvalidArgumentException('proxyType required');
        } else {
            $this->setProxyType($config['proxyType']);
        }

        if (!isset($config['proxyAddress'])) {
            throw new \InvalidArgumentException('proxyAddress required');
        } else {
            $this->setProxyAddress($config['proxyAddress']);
        }

        if (!isset($config['proxyPort'])) {
            throw new \InvalidArgumentException('proxyPort required');
        } else {
            $this->setProxyPort($config['proxyPort']);
        }

        if (isset($config['proxyLogin'])) {
            $this->setProxyLogin($config['proxyLogin']);
        }

        if (isset($config['proxyPassword'])) {
            $this->setProxyPassword($config['proxyPassword']);
        }

        if (!isset($config['userAgent'])) {
            throw new \InvalidArgumentException('userAgent required');
        } else {
            $this->setUserAgent($config['userAgent']);
        }

        if (isset($config['cookies'])) {
            $this->setCookies($config['cookies']);
        }

        if (isset($config['isInvisible'])) {
            $this->setIsInvisible($config['isInvisible']);
        }
    }

    /**
     * @return string
     */
    public function getWebsiteURL(): string
    {
        return $this->websiteURL;
    }

    /**
     * @param string $websiteURL
     */
    public function setWebsiteURL(string $websiteURL): void
    {
        $this->websiteURL = $websiteURL;
    }

    /**
     * @return string
     */
    public function getWebsiteKey(): string
    {
        return $this->websiteKey;
    }

    /**
     * @param string $websiteKey
     */
    public function setWebsiteKey(string $websiteKey): void
    {
        $this->websiteKey = $websiteKey;
    }

    /**
     * @return null|string
     */
    public function getWebsiteSToken(): ?string
    {
        return $this->websiteSToken;
    }

    /**
     * @param null|string $websiteSToken
     */
    public function setWebsiteSToken(?string $websiteSToken): void
    {
        $this->websiteSToken = $websiteSToken;
    }

    /**
     * @return string
     */
    public function getProxyType(): string
    {
        return $this->proxyType;
    }

    /**
     * @param string $proxyType
     */
    public function setProxyType(string $proxyType): void
    {
        $this->proxyType = $proxyType;
    }

    /**
     * @return string
     */
    public function getProxyAddress(): string
    {
        return $this->proxyAddress;
    }

    /**
     * @param string $proxyAddress
     */
    public function setProxyAddress(string $proxyAddress): void
    {
        $this->proxyAddress = $proxyAddress;
    }

    /**
     * @return int
     */
    public function getProxyPort(): int
    {
        return $this->proxyPort;
    }

    /**
     * @param int $proxyPort
     */
    public function setProxyPort(int $proxyPort): void
    {
        $this->proxyPort = $proxyPort;
    }

    /**
     * @return null|string
     */
    public function getProxyLogin(): ?string
    {
        return $this->proxyLogin;
    }

    /**
     * @param null|string $proxyLogin
     */
    public function setProxyLogin(?string $proxyLogin): void
    {
        $this->proxyLogin = $proxyLogin;
    }

    /**
     * @return null|string
     */
    public function getProxyPassword(): ?string
    {
        return $this->proxyPassword;
    }

    /**
     * @param null|string $proxyPassword
     */
    public function setProxyPassword(?string $proxyPassword): void
    {
        $this->proxyPassword = $proxyPassword;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return null|string
     */
    public function getCookies(): ?string
    {
        return $this->cookies;
    }

    /**
     * @param null|string $cookies
     */
    public function setCookies(?string $cookies): void
    {
        $this->cookies = $cookies;
    }

    /**
     * @return bool|null
     */
    public function isInvisible(): ?bool
    {
        return $this->isInvisible;
    }

    /**
     * @param bool|null $isInvisible
     */
    public function setIsInvisible(bool $isInvisible): void
    {
        $this->isInvisible = $isInvisible;
    }

    /**
     * @return string[]
     */
    public function asArray(): array
    {
        return array_filter([
            'type' => self::TYPE,
            'websiteURL' => $this->getWebsiteURL(),
            'websiteKey' => $this->getWebsiteKey(),
            'websiteSToken' => $this->getWebsiteSToken(),
            'proxyType' => $this->getProxyType(),
            'proxyAddress' => $this->getProxyAddress(),
            'proxyPort' => $this->getProxyPort(),
            'proxyLogin' => $this->getProxyLogin(),
            'proxyPassword' => $this->getProxyPassword(),
            'userAgent' => $this->getUserAgent(),
            'cookies' => $this->getCookies(),
            'isInvisible' => $this->isInvisible() ? 'true' : null
        ]);
    }
}