<?php

/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Magedelight\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Description of Register
 *
 * @author Rocket Bazaar Core Team
 */
class CustomerCancelOrderItem implements ObserverInterface
{
    const XML_PATH_EMAIL_CUSTOMER_CANCEL_ITEM_TEMPLATE = 'emailconfiguration/customer_order_cancel/item_cancel_customer_template';
    const XML_PATH_EMAIL_CUSTOMER_CANCEL_ITEM_VENDOR_TEMPLATE = 'emailconfiguration/customer_order_cancel/item_cancel_vendor_template';
    const XML_PATH_EMAIL_CUSTOMER_CANCEL_ITEM_ADMIN_TEMPLATE = 'emailconfiguration/customer_order_cancel/item_cancel_admin_template';

    protected $logger;

    /**
     * CustomerCancelOrderItem constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magedelight\Vendor\Model\Vendor $vendor
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\Vendor\Model\Vendor $vendor,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    ) {
        $this->logger = $logger;
        $this->vendor = $vendor;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_priceHelper = $priceHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order =  $observer->getEvent()->getOrder();
        $cancelItemArray = $observer->getEvent()->getCancelItemArray();

        // Customer Email Send
        $customerName = $order->getCustomerFirstname() . " " . $order->getCustomerLastname();
        $customerEmail = $order->getCustomerEmail();
        $orderIncrementId = $order->getIncrementId();
        $storeId = $order->getStoreId();

        $this->sendEmailToCustomer(
            $customerEmail,
            $customerName,
            $orderIncrementId,
            $cancelItemArray,
            $storeId
        );
        // Vendor Email Send
        $vendorId = $cancelItemArray['vendorId'];
        $vendorDetail = $this->vendor->load($vendorId);
        $isConfirm = $order->getIsConfirmed();
        $vendorName = $vendorDetail->getBusinessName();
        if ($isConfirm) {
            $vendorEmail = $vendorDetail->getEmail();
            $vendorOrderId = $orderIncrementId . '-' . $vendorId;
            $this->sendEmailToVendor(
                $vendorEmail,
                $vendorName,
                $vendorOrderId,
                $cancelItemArray,
                $storeId
            );
        }

        // Admin Email Send
        $salesRepresentativeEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email');
        $salesRepresentativeName = $this->scopeConfig->getValue('trans_email/ident_sales/name');

        $this->sendEmailToAdmin(
            $salesRepresentativeEmail,
            $salesRepresentativeName,
            $vendorName,
            $orderIncrementId,
            $cancelItemArray,
            $storeId
        );
    }

    /*
     * Send Email To Customer
     */
    /**
     * @param $customerEmail
     * @param $customerName
     * @param $orderIncrementId
     * @param $cancelItemArray
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function sendEmailToCustomer(
        $customerEmail,
        $customerName,
        $orderIncrementId,
        $cancelItemArray,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $salesRepresentativeEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email');
        $salesRepresentativeName = $this->scopeConfig->getValue('trans_email/ident_sales/name');
        if ($salesRepresentativeEmail == "" || $salesRepresentativeName == "") {
            return;
        }

        $itemPrice = $this->_priceHelper->currency($cancelItemArray['price'], true, false);

        $templateVars = [
            'customer_name' => $customerName,
            'increment_id' => $orderIncrementId,
            'item_name' => $cancelItemArray['name'],
            'item_qty' => intval($cancelItemArray['qty']),
            'item_price' => $itemPrice
        ];

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_CUSTOMER_CANCEL_ITEM_TEMPLATE,
                $storeScope
            ))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setFromByScope(['name' => $salesRepresentativeName,'email' => $salesRepresentativeEmail])
            ->setTemplateVars($templateVars)
            ->addTo($customerEmail);

        $transport = $this->_transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }

    /*
     * Send Email To Vendor
     */
    /**
     * @param $vendorEmail
     * @param $vendorName
     * @param $vendorOrderId
     * @param $cancelItemArray
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function sendEmailToVendor(
        $vendorEmail,
        $vendorName,
        $vendorOrderId,
        $cancelItemArray,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $salesRepresentativeEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email');
        $salesRepresentativeName = $this->scopeConfig->getValue('trans_email/ident_sales/name');
        if ($salesRepresentativeEmail == "" || $salesRepresentativeName == "") {
            return;
        }

        $itemPrice = $this->_priceHelper->currency($cancelItemArray['price'], true, false);

        $templateVars = [
            'vendor_name' => $vendorName,
            'increment_id' => $vendorOrderId,
            'item_name' => $cancelItemArray['name'],
            'item_qty' => intval($cancelItemArray['qty']),
            'item_price' => $itemPrice
        ];

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_CUSTOMER_CANCEL_ITEM_VENDOR_TEMPLATE,
                $storeScope
            ))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setFromByScope(['name' => $salesRepresentativeName,'email' => $salesRepresentativeEmail])
            ->setTemplateVars($templateVars)
            ->addTo($vendorEmail);

        $transport = $this->_transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }

    /*
     * Send Email To Vendor
     */
    /**
     * @param $adminEmail
     * @param $adminName
     * @param $vendorName
     * @param $orderIncrementId
     * @param $cancelItemArray
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function sendEmailToAdmin(
        $adminEmail,
        $adminName,
        $vendorName,
        $orderIncrementId,
        $cancelItemArray,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $salesRepresentativeEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email');
        $salesRepresentativeName = $this->scopeConfig->getValue('trans_email/ident_sales/name');
        if ($salesRepresentativeEmail == "" || $salesRepresentativeName == "") {
            return;
        }

        $itemPrice = $this->_priceHelper->currency($cancelItemArray['price'], true, false);

        $templateVars = [
            'admin_name' => $adminName,
            'vendor_name' => $vendorName,
            'increment_id' => $orderIncrementId,
            'item_name' => $cancelItemArray['name'],
            'item_qty' => intval($cancelItemArray['qty']),
            'item_price' => $itemPrice
        ];

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_CUSTOMER_CANCEL_ITEM_ADMIN_TEMPLATE,
                $storeScope
            ))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setFromByScope(['name' => $salesRepresentativeName,'email' => $salesRepresentativeEmail])
            ->setTemplateVars($templateVars)
            ->addTo($adminEmail);

        $transport = $this->_transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
