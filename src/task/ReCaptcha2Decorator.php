<?php

namespace unapi\anticaptcha\antigate\task;

use unapi\anticaptcha\common\AnticaptchaTaskInterface;
use unapi\anticaptcha\common\task\ReCaptcha2Task;

class ReCaptcha2Decorator implements AnticaptchaTaskInterface
{
    const TYPE = 'NoCaptchaTaskProxyless';

    /** @var ReCaptcha2Task */
    private $task;
    /** @var array */
    private $options = [];

    /**
     * @param ReCaptcha2Task $task
     * @param array $options
     */
    public function __construct(ReCaptcha2Task $task, $options = [])
    {
        $this->task = $task;
        $this->options = $options;
    }

    /**
     * @return ReCaptcha2Task
     */
    public function getTask(): ReCaptcha2Task
    {
        return $this->task;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return string[]
     */
    public function asArray(): array
    {
        return array_filter(
            array_merge(
                [
                    'type' => self::TYPE,
                ],
                $this->getTask()->asArray(),
                $this->getOptions()
            ),
            function ($value) {
                return !is_null($value);
            }
        );
    }
}