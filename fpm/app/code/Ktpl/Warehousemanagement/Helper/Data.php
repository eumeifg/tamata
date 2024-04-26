<?php

namespace Ktpl\Warehousemanagement\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SCAN_LIMIT        = 'warehousemanagement/general/scanlimit';
    const DEL_VEN_TO_WRHSE  = 'warehousemanagement/deliverystatus/vendortowarehouse';
    const DEL_WRHSE_TO_CUST = 'warehousemanagement/deliverystatus/warehousetocustomer';
    const RET_CUST_TO_WRHSE = 'warehousemanagement/returnsstatus/vendortowarehouse';
    const RET_WRHSE_TO_VEN  = 'warehousemanagement/returnsstatus/warehousetocustomer';


    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array  $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Retrieve number of barcode scan
     *
     * @return int
     */
    public function getScanLimit()
    {
        return $this->scopeConfig->getValue(self::SCAN_LIMIT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve status of Delivery vendor to warehouse status
     *
     * @return string
     */
    public function getDeliveryVendortoWarehouseStatus()
    {
        return $this->scopeConfig->getValue(self::DEL_VEN_TO_WRHSE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve status of Delivery warehouse to customer status
     *
     * @return string
     */
    public function getDeliveryWarehousetoCustomer()
    {
        return $this->scopeConfig->getValue(self::DEL_WRHSE_TO_CUST, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve status of Returns customer to warehouse status
     *
     * @return string
     */
    public function getReturnsVendortoWarehouse()
    {
        return $this->scopeConfig->getValue(self::RET_CUST_TO_WRHSE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve status of Returns warehouse to customer status
     *
     * @return string
     */
    public function getReturnsWarehousetoCustomer()
    {
        return $this->scopeConfig->getValue(self::RET_WRHSE_TO_VEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getCSVHeaders()
    {
        return [
            'Barcode'
        ];
    }
    
    public function getSampleRow()
    {
        return [
            "D000000474|000000717-11052"
        ];
    }
}
