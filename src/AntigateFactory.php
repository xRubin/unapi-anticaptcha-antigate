<?php
namespace unapi\anticaptcha\antigate;

use unapi\anticaptcha\antigate\task\ImageTaskDecorator;
use unapi\anticaptcha\antigate\task\ReCaptcha2Decorator;
use unapi\anticaptcha\common\AnticaptchaTaskInterface;
use unapi\anticaptcha\common\task\ImageTask;
use unapi\anticaptcha\common\task\ReCaptcha2Task;

class AntigateFactory
{
    /**
     * @param AnticaptchaTaskInterface $task
     * @param array $options
     * @return AnticaptchaTaskInterface
     */
    public static function decorate(AnticaptchaTaskInterface $task, $options = []): AnticaptchaTaskInterface
    {
        if ($task instanceof ImageTask)
            return new ImageTaskDecorator($task, $options);

        if ($task instanceof ReCaptcha2Task)
            return new ReCaptcha2Decorator($task, $options);

        throw new \InvalidArgumentException('Unsupported task type');
    }
}