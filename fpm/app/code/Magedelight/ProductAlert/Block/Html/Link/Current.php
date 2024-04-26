<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Block\Html\Link;

class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    protected function _toHtml()
    {
        if (!$this->_scopeConfig->getValue(
            'catalog/productalert/allow_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
