<?php

namespace Ktpl\Ajaxscroll\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function isAjaxEnabled()
    {
        return $this->scopeConfig->isSetFlag('ajaxscroll/settings/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
