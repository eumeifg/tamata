<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magedelight\Sales\Model\Order as VendorOrder;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_CAN_GENERATE_INVOICE = 'vendor_sales/payment_settings/allow_to_generate_invoice';
    const XML_PATH_INVOICE_PAYMENT_METHODS = 'vendor_sales/payment_settings/payment_methods';
    const XML_PATH_CUSTOMER_CANCEL_ORDER_FLAG = 'vendor_sales/cancel_order/module_enable';
    const XML_PATH_CUSTOMER_CANCEL_ORDER_REASONS = 'vendor_sales/cancel_order/order_cancel_reasons_customer';

    /**
     * @param $config_path
     * @param string $scope
     * @return mixed
     */
    public function getConfig($config_path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            $scope
        );
    }
    
    /**
     * return true if vendor is allowed to generate invoice.
     * @return boolean true | false
     */
    public function canGenerateInvoice()
    {
        return $this->getConfig(self::XML_PATH_CAN_GENERATE_INVOICE, ScopeInterface::SCOPE_STORE);
    }

    public function getAllowedPaymentMethodsForInvoice()
    {
        return $this->getConfig(self::XML_PATH_INVOICE_PAYMENT_METHODS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return boolean
     */
    public function isEnabledCustomerCancelOrder()
    {
        return $this->getConfig(self::XML_PATH_CUSTOMER_CANCEL_ORDER_FLAG, ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * @return boolean
     */
    public function showMainOrderStatus()
    {
        return $this->getConfig(
            VendorOrder::IS_MAGENTO_ORDER_STATUS_ALLOWED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getCustomerCancelOrderReason()
    {
        $isEnableForBuyer = $this->isEnabledCustomerCancelOrder();
        if (!$isEnableForBuyer) {
            return [];
        }
        $orderCancelReasonForBuyerObject = json_decode($this->getConfig(self::XML_PATH_CUSTOMER_CANCEL_ORDER_REASONS));
        $orderCancelReasonForBuyerArray = (array) $orderCancelReasonForBuyerObject;
        return $orderCancelReasonForBuyerArray;
    }

    /* Order Cancel Item Url */
    public function getCancelOrderItemUrl()
    {
        return $this->_getUrl('rbsales/*/customercancelorderitem/');
    }
}
