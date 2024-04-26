<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */

namespace Ktpl\LazyLoad\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * Init lazy load
 */
class Lazy extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve block html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_scopeConfig->getValue(
            'ktpllazyzoad/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return parent::_toHtml();
        }

        return '';
    }
}
