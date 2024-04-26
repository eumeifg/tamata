<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Description of Register
 * @author Rocket Bazaar Core Team
 */
class VendorProductCommissionSaveAfter implements ObserverInterface
{

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_product_commission_change/template';

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magedelight\Catalog\Model\ProductFactory $vendorproductFactory,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->registry = $registry;
        $this->_vendorproductFactory = $vendorproductFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_product_commission_change/enabled')) {
            $old_value = $this->registry->registry('commission_value');
            $new_value = $observer->getEvent()->getProduct()->getMdCommission();
            $cType = $observer->getEvent()->getProduct()->getMdCalculationType();
            $name = $observer->getEvent()->getProduct()->getName();
            if ($cType == 1) {
                $cType = "Flat";
            } else {
                $cType = "Percentage";
            }
            if ($new_value != $old_value) {
                $collection = $this->getProductVendor();

                foreach ($collection as $collData) {
                    $email = $collData->getEmail();
                    $vendorName = $collData->getBusinessName();
                    $this->_sendNotification($email, $vendorName, $new_value, $cType, $old_value, $name);
                }
            }
        }
    }

    public function getProductVendor()
    {
        $ids = $this->getExistVendor();
        $collection = $this->_vendorFactory->create()->getCollection();
        if ($ids != null) {
            $collection->getSelect()->where("vendor_id IN (?)", $ids);
        }
        return $collection;
    }

    public function getExistVendor()
    {
        $productId = $this->registry->registry('current_product')->getId();
        $collection = $this->_vendorproductFactory->create()->getCollection()->addFieldToFilter(
            'marketplace_product_id',
            $productId
        );
        $collection->addFieldToFilter('rbvpw.status', ['in' => ['1', '4']]);
        $vId = [];
        foreach ($collection as $col) {
            $vId[] = $col->getVendorId();
        }
        return $vId;
    }

    protected function _sendNotification(
        $email,
        $vendorName,
        $new_value,
        $cType,
        $old_value,
        $name
    ) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store_name = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);

        $templateVars = [
            'cal_type'  => $cType,
            'old_value' => $old_value,
            'cur_value' => $new_value,
            'product'   => $name,
            'vendor_name' => $vendorName,
            'store_name' => $store_name
        ];

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($templateVars)
            ->addTo($email)
            ->setFromByScope('general')
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
