<?php

namespace Joostvanveen\RollbarLogger\Helper;

/**
 * Class StringToArray
 *
 * @package Joostvanveen\RollbarLogger
 */
class StringToArray extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @param $string
     * @param null $assocSeparator
     * @param string $lineSeparator
     *
     * @return array
     */
    public function stringToArray($string, $assocSeparator = null, $lineSeparator = "\n")
    {
        // Remove Windows carriage returns
        $string = str_replace("\r", '', $string);

        $array = explode($lineSeparator, $string);

        // Clean up array values
        $array = array_map('trim', $array);
        $returnArray = [];
        foreach ($array as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            if ($assocSeparator) {
                $line = explode($assocSeparator, $value);
                if (count($line) > 1) {
                    // This line contains a key => value pair, like 'foo=bar'.
                    $line = array_map('trim', $line);
                    $returnArray[ $line[0] ] = $line[1];
                    continue;
                }
            }
            else {
                // This line contains a single value.
                $returnArray[  ] = $value;
            }
        }

        return $returnArray;
    }

    /**
     * @param $path
     * @param string $scopeType
     * @param null $assocSeparator
     * @param string $lineSeparator
     *
     * @return array
     */
    public function configSettingToArray($path, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $assocSeparator = null, $lineSeparator = "\n")
    {
        if (false == ($setting = $this->scopeConfig->getValue($path, $scopeType))) {
            return [];
        }

        return $this->stringToArray($setting, $assocSeparator, $lineSeparator);
    }
}
