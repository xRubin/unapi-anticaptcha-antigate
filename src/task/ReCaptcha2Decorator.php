<?php

namespace unapi\anticaptcha\antigate\task;

use unapi\anticaptcha\common\AnticaptchaTaskInterface;
use unapi\anticaptcha\common\task\ReCaptcha2Task;

class ReCaptcha2Decorator implements AnticaptchaTaskInterface
{
    const TYPE = 'NoCaptchaTaskProxyless';

    /** @var ReCaptcha2Task */
    private $task;

    /**
     * @param ReCaptcha2Task $task
     */
    public function __construct(ReCaptcha2Task $task)
    {
        $this->task = $task;
    }

    /**
     * @return ReCaptcha2Task
     */
    public function getTask(): ReCaptcha2Task
    {
        return $this->task;
    }

    /**
     * @return string[]
     */
    public function asArray(): array
    {
        return [
            'type' => self::TYPE,
            'websiteURL' => $this->getTask()->getSiteUrl(),
            'websiteKey' => $this->getTask()->getSiteKey()
        ];
    }
}