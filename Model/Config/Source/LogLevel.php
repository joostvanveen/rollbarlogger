<?php

namespace Joostvanveen\RollbarLogger\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Monolog\Logger;

/**
 * Class LogLevel
 *
 * @author Joost van Veen
 * @package Joostvanveen\RollbarLogger\Model\Config\Source
 */
class LogLevel implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Logger::INFO, 'label' => __('Info')],
            ['value' => Logger::NOTICE, 'label' => __('Notice')],
            ['value' => Logger::WARNING, 'label' => __('Warning')],
            ['value' => Logger::CRITICAL, 'label' => __('Critical')],
            ['value' => Logger::ALERT, 'label' => __('Alert')],
            ['value' => Logger::EMERGENCY, 'label' => __('Emergency')],
        ];
    }
}
