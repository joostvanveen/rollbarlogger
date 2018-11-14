<?php

namespace Joostvanveen\RollbarLogger\Model;

use Joostvanveen\RollbarLogger\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Logger\Monolog;
use Rollbar\Rollbar;

/**
 * Class RollbarLog
 *
 * @author Joost van Veen
 * @package Joostvanveen\RollbarLogger\Model
 */
class RollbarLog extends Monolog
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
     * @var array
     */
    protected $config = [];

    /**
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @param Data|Data\Proxy $data
     * @param Session\Proxy $customerSession
     */
    public function __construct(
        $name,
        array $handlers = [],
        array $processors = [],
        Data\Proxy $data,
        Session\Proxy $customerSession
    )
    {
        $this->data = $data;
        $this->customerSession = $customerSession;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Adds a log record to Rollbar and Magento.
     *
     * @param integer $level  The logging level
     * @param string $message The log message or Exception
     * @param array $context  The log context. This is what Rollbar calls 'extra'.
     *
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = [])
    {
        if ($this->isStringToLog($message)) {
            $this->logToRollbar($level, $message, $context);
        }

        return $this->logToMagento($level, $message, $context);
    }

    /**
     * @param $message
     * @param $logLevel
     * @param array $context
     *
     * @return bool
     */
    protected function logToRollbar($logLevel, $message, $context = [])
    {
        if ( ! $this->data->shouldLogToRollbar()) {
            return false;
        }

        if ($logLevel < $this->data->getLogThreshold()) {
            return false;
        }

        $logLevel = $this->logLevelAsRollbarString($logLevel);
        if ( ! $logLevel) {
            return false;
        }

        Rollbar::init($this->getRollbarConfig());

        $context = array_merge($context, $this->getExtraData());
        ksort($context);

        Rollbar::log($logLevel, $message, $context);
    }

    /**
     * @param $client
     * @param array $extra
     */
    protected function setExtraContext($client, $extra = [])
    {
        $defaultExtra = [];
        $extra = $this->getExtraData($extra);

        $extra = array_merge($defaultExtra, $extra);

        $client->extra_context($extra);
    }

    /**
     * @param $message
     *
     * @return bool
     */
    protected function isStringToLog($message)
    {
        $doNotLog = $this->data->getStringsToExclude();
        foreach ($doNotLog as $string) {
            if (stristr($message, $string)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    protected function getExtraData()
    {
        return array_merge(
            [
                'mage_mode' => $this->data->getAppState()
            ],
            $this->data->getModuleVersions()
        );
    }

    /**
     * @return \Magento\Customer\Model\Customer|null
     */
    protected function getCurrentUser()
    {
        if ( ! $this->customerSession || $this->customerSession->getCustomer()->isEmpty()) {
            return null;
        }

        return $this->customerSession->getCustomer();
    }

    /**
     * @param $logLevel
     *
     * @return string|null
     */
    protected function logLevelAsRollbarString($logLevel)
    {
        $logLevels = [
            200 => 'info',
            250 => 'notice',
            300 => 'warning',
            400 => 'error',
            500 => 'critical',
            550 => 'alert',
            600 => 'emergency',
        ];

        return ! empty($logLevels[ $logLevel ]) ? $logLevels[ $logLevel ] : null;
    }

    /**
     * @return array
     */
    protected function getRollbarConfig()
    {
        $config = [
            'access_token' => $this->data->getConfigValue('joostvanveen/rollbarlogger/token'),
            'environment' => $this->data->getConfigValue('joostvanveen/rollbarlogger/environment'),
            'capture_email' => true,
        ];
        if (($user = $this->getCurrentUser())) {
            $config['person'] = [
                'id' => $this->data->scrambleEmail($user->getEmail()),
            ];
        }

        return $config;
    }

    /**
     * @param $message
     * @param array $context
     *
     * @return array
     */
    protected function addExceptionToContext($message, array $context)
    {
        if ($message instanceof \Exception && ! isset($context['exception'])) {
            $context['is_exception'] = true;
            $context['exception'] = $message;
        }

        return $context;
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     *
     * @return bool
     */
    protected function logToMagento($level, $message, array $context)
    {
        if ($message instanceof \Exception && ! isset($context['exception'])) {
            $context['is_exception'] = true;
            $context['exception'] = $message;
        }
        $message = $message instanceof \Exception ? $message->getMessage() : $message;

        return parent::addRecord($level, $message, $context);
    }
}
