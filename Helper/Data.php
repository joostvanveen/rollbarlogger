<?php

namespace Joostvanveen\RollbarLogger\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @author Joost van Veen
 * @package Joostvanveen\RollbarLogger\Helper
 */
class Data extends AbstractHelper
{

    const MODULES_CACHE_ID = 'rollbar_modules';

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var array
     */
    protected $moduleVersions;

    /**
     * @var \Joostvanveen\RollbarLogger\Helper\StringToArray
     */
    protected $stringToArrayHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Joostvanveen\RollbarLogger\Helper\StringToArray $stringToArrayHelper,
        \Magento\Framework\App\CacheInterface $cache
    )
    {
        parent::__construct($context);
        defined('C_RB_STORE_ID') || define('C_RB_STORE_ID', $storeManager->getStore()->getId());
        defined('C_RB_LOG_PACKAGES') || define('C_RB_LOG_PACKAGES', $stringToArrayHelper->configSettingToArray('joostvanveen/rollbarlogger/include_packages'));
        defined('C_RB_EXCLUDE_STRINGS') || define('C_RB_EXCLUDE_STRINGS', $stringToArrayHelper->configSettingToArray('joostvanveen/rollbarlogger/exclude_strings'));
        $this->appState = $appState;
        $this->cache = $cache;

        $this->stringToArrayHelper = $stringToArrayHelper;
    }

    /**
     * Get a setting from code_config_data for the current store.
     *
     * @param $field
     *
     * @return mixed
     */
    public function getConfigValue($field)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $this->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function shouldLogToRollbar()
    {
        if ( ! $this->getConfigValue('joostvanveen/rollbarlogger/enabled')) {
            return false;
        }

        if ( ! $this->isProductionMode() && empty($this->getConfigValue('joostvanveen/rollbarlogger/mage_mode_development'))) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getLogThreshold()
    {
        return (int) $this->getConfigValue('joostvanveen/rollbarlogger/log_level');
    }

    /**
     * @return bool
     */
    public function isProductionMode()
    {
        return $this->getAppState() == 'production';
    }

    /**
     * @return string
     */
    public function getAppState()
    {
        //dump_exit($this->appState);
        return $this->appState->getMode();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return C_RB_STORE_ID;
    }

    /**
     * Return a key => value array of module and their versions. Include
     * only the modules stored in joostvanveen/rollbarlogger/include_packages.
     *
     * @return array
     */
    public function getModuleVersions()
    {
        $this->moduleVersions = json_decode($this->cache->load(self::MODULES_CACHE_ID), true);

        if ($this->moduleVersions !== null) {
            return $this->moduleVersions;
        }

        $packagesToLog = C_RB_LOG_PACKAGES;
        $composer = json_decode(file_get_contents(BP . '/composer.lock'), true);

        $this->moduleVersions = [];
        foreach ($composer['packages'] as $package) {
            if (in_array($package['name'], $packagesToLog)) {
                $this->moduleVersions[ str_replace('/', '_', $package['name']) ] = $package['version'];
            }
            elseif ($package['name'] == 'magento/magento2-base') {
                $this->moduleVersions['_magento_version'] = $package['version'];
            }
        }

        $this->cache->save(json_encode($this->moduleVersions), self::MODULES_CACHE_ID, [], 86400);

        return $this->moduleVersions;
    }

    /**
     * Return an array of all (sub)strings which we
     * do not want to log, like 'Add of item'.
     *
     * @return array
     */
    public function getStringsToExclude()
    {
        if (defined('C_RB_EXCLUDE_STRINGS')) {
            return C_RB_EXCLUDE_STRINGS;
        }

        return [];
    }

    public function scrambleEmail ($email) {
        return preg_replace("/(?!^).(?=[^@]+@)/", "*", $email);
    }
}
