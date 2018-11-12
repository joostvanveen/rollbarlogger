<?php

namespace Joostvanveen\RollbarLogger\Plugin;

use Joostvanveen\RollbarLogger\Helper\Data;
use Joostvanveen\RollbarLogger\Model\RollbarLog;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Http;
use Magento\Framework\App\State;
use Monolog\Logger;

/**
 * Class ExceptionCatcher
 *
 * @author Joost van Veen
 * @package Joostvanveen\RollbarLogger\Plugin
 */
class ExceptionCatcher
{

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * ExceptionCatcher constructor
     *
     * @param Data $data
     * @param Session $catalogSession
     * @param State $state
     * @param RollbarLog $logger
     */
    public function __construct(
        Data $data,
        Session $catalogSession,
        State $state,
        RollbarLog $logger
    )
    {
        $this->data = $data;
        $this->state = $state;
        $this->logger = $logger;
        $this->customerSession = $catalogSession;
    }

    /**
     * Catch any exceptions and notify Rollbar
     *
     * @param Http $subject
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     *
     * @return array
     */
    public function beforeCatchException(
        Http $subject,
        Bootstrap $bootstrap,
        \Exception $exception
    )
    {
        // When dealing with an exception, send the entire exception to Rollbar as 2nd argument, for strack tracing
        $this->logger->addRecord(Logger::CRITICAL, $exception, ['is_exception' => true]);

        return [$bootstrap, $exception];
    }

}
