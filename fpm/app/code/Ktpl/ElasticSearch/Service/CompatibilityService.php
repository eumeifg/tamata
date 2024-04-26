<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Service;

use Magento\Framework\App\ObjectManager;

/**
 * Class CompatibilityService
 *
 * @package Ktpl\ElasticSearch\Service
 */
class CompatibilityService
{
    /**
     * @return bool
     */
    public static function is20()
    {
        list($a, $b,) = explode('.', self::getVersion());

        return $a == 2 && $b == 0;
    }

    /**
     * @return bool
     */
    public static function is21()
    {
        list($a, $b,) = explode('.', self::getVersion());

        return $a == 2 && $b == 1;
    }

    /**
     * @return bool
     */
    public static function is22()
    {
        list($a, $b,) = explode('.', self::getVersion());

        return $a == 2 && $b == 2;
    }

    /**
     * @return bool
     */
    public static function is23()
    {
        list($a, $b,) = explode('.', self::getVersion());

        return $a == 2 && $b == 3;
    }

    /**
     * Check if module is enterprise
     *
     * @return bool
     */
    public static function isEnterprise()
    {
        return self::getEdition() === 'Enterprise';
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        /** @var \Magento\Framework\App\ProductMetadata $metadata */
        $metadata = self::getObjectManager()->get('Magento\Framework\App\ProductMetadata');

        return $metadata->getVersion();
    }

    /**
     * Get edition name
     *
     * @return string
     */
    public static function getEdition()
    {
        /** @var \Magento\Framework\App\ProductMetadata $metadata */
        $metadata = self::getObjectManager()->get('Magento\Framework\App\ProductMetadata');

        if (self::hasModule('Magento_Enterprise')) {
            return 'Enterprise';
        }

        return $metadata->getEdition();
    }

    /**
     * Check has module
     *
     * @param $moduleName
     * @return bool
     */
    public static function hasModule($moduleName)
    {
        /** @var \Magento\Framework\Module\FullModuleList $list */
        $list = self::getObjectManager()->get('Magento\Framework\Module\FullModuleList');

        return $list->has($moduleName);
    }

    /**
     * Get object manager instance
     *
     * @return ObjectManager
     */
    public static function getObjectManager()
    {
        return ObjectManager::getInstance();
    }
}
