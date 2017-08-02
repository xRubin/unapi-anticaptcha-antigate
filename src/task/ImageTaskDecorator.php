<?php

namespace unapi\anticaptcha\antigate\task;

use unapi\anticaptcha\common\AnticaptchaTaskInterface;
use unapi\anticaptcha\common\task\ImageTask;

class ImageTaskDecorator implements AnticaptchaTaskInterface
{
    const TYPE = 'ImageToTextTask';

    /** @var ImageTask */
    private $task;
    /** @var array */
    private $options = [];

    /**
     * @param ImageTask $task
     * @param array $options
     */
    public function __construct(ImageTask $task, $options = [])
    {
        $this->task = $task;
        $this->options = $options;
    }

    /**
     * @return ImageTask
     */
    public function getTask(): ImageTask
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