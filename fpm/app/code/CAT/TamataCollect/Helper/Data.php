<?php

namespace CAT\TamataCollect\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;
use Magento\Framework\App\ResourceConnection;

class Data extends AbstractHelper
{

    protected $scopeConfig;
    public function __construct(
               ResourceConnection $resourceConnection,
               \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(){
        return $this->scopeConfig->getValue('tamata_collect/general/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getOptionId(){
        return $this->scopeConfig->getValue('tamata_collect/general/option_id',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}