<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\App;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Backend config accessor.
 */
class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config
     */
    protected $appConfig;

    /**
     * @var array
     */
    private $data;

    /**
     *
     * @param \Magento\Framework\App\Config $appConfig
     */
    public function __construct(\Magento\Framework\App\Config $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    /**
     * @inheritdoc
     */
    public function getValue($path)
    {
        if (isset($this->data[$path])) {
            return $this->data[$path];
        }

        $configPath = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if ($path) {
            $configPath .= '/' . $path;
        }
        return $this->appConfig->get(System::CONFIG_TYPE, $configPath);
    }

    /**
     * @inheritdoc
     */
    public function setValue($path, $value)
    {
        $this->data[$path] = $value;
    }

    /**
     * @inheritdoc
     */
    public function isSetFlag($path)
    {
        $configPath = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if ($path) {
            $configPath .= '/' . $path;
        }
        return (bool) $this->appConfig->get(System::CONFIG_TYPE, $configPath);
    }
}
