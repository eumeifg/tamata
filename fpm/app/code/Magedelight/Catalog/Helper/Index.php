<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Helper;

class Index extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    public function getPeriodCondition($field, $settingKey)
    {
        $period = intVal(
            $this->_scopeConfig->getValue(
                'rbsort/' . $settingKey,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        if ($period) {
            $period = date('Y-m-d', time() - $period * 24 * 3600);
            $period = " AND $field > '$period' ";
        } else {
            $period = '';
        }

        return $period;
    }

    public function getStoreCondition($field)
    {
        return " AND $field = " . $this->_storeManager->getStore()->getId();
    }
}
