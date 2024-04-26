<?php

namespace Ktpl\ReorderItem\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{

    protected $rmaHelper;
    protected $vendorHelper;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfigValue('reorder_item/general/enabled');
    }

    /**
     * @param string $path
     * @return string
     */
    public function getConfigValue(string $path): string
    {
        $value = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            null
        );

        return $value ?? '';
    }
}
