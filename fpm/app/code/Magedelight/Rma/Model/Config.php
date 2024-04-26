<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Rma
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Rma\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    const RETURN_PERIOD_XML_PATH = 'rma/policy/return_period';

    /**
     * @param null|int $store
     * @return int
     */
    public function getPolicyReturnPeriod($store = null)
    {
        return $this->getConfigValue(self::RETURN_PERIOD_XML_PATH);
    }
    
    /**
     * @param string $field
     * @param null|int $store
     * @return int
     */
    public function getConfigValue($field, $store = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }
}
