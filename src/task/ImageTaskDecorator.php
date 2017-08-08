<?php

namespace unapi\anticaptcha\antigate\task;

use unapi\anticaptcha\common\AnticaptchaTaskInterface;
use unapi\anticaptcha\common\task\ImageTask;

class ImageTaskDecorator implements AnticaptchaTaskInterface
{
    const TYPE = 'ImageToTextTask';

    /** @var ImageTask */
    private $task;

    /**
     * @param ImageTask $task
     */
    public function __construct(ImageTask $task)
    {
        $this->task = $task;
    }

    /**
     * @return ImageTask
     */
    public function getTask(): ImageTask
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
            'body' => base64_encode($this->getTask()->getBody()),
            'numeric' => $this->getTask()->getNumeric(),
            'minLength' => $this->getTask()->getMinLength(),
            'maxLength' => $this->getTask()->getMaxLength(),
        ];
    }
}