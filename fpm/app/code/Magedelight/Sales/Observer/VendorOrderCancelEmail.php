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
namespace Magedelight\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

class VendorOrderCancelEmail implements ObserverInterface
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    private $_vendorHelper;

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_order_cancel/template';

    const XML_PATH_EMAIL_ADMIN_TEMPLATE = 'emailconfiguration/vendor_order_cancel/admin_template';

    /**
     * @var \Magedelight\Sales\Model\OrderFactory
     */
    private $vendorOrderFactory;

    /**
     * VendorOrderCancelEmail constructor.
     * @param \Magedelight\Vendor\Model\VendorRepository $vendor
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Vendor\Helper\Data $_vendorHelper
     * @param \Magedelight\Theme\Model\Users $usersModel
     */
    public function __construct(
        \Magedelight\Vendor\Model\VendorRepository $vendor,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Vendor\Helper\Data $_vendorHelper,
        \Magedelight\Theme\Model\Users $usersModel
    ) {
        $this->request = $request;
        $this->vendor = $vendor;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->vendorOrderFactory = $vendorOrderFactory;
        $this->_vendorHelper = $_vendorHelper;
        $this->usersModel = $usersModel;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_order_cancel/enabled')) {
            $vendorOrderIds = $observer->getEvent()->getVendorOrderIds();
            foreach ($vendorOrderIds as $voId) {
                /** @var /RB/Vendor/Model/Order */
                $vendorOrder  = $this->vendorOrderFactory->create()->load($voId);
                $vId = $vendorOrder->getVendorId();
                $vendorData = $this->vendor->getById($vId);
                $displayName = $this->_vendorHelper->getVendorNameById($vendorData->getVendorId());
                $order = $vendorOrder->getOriginalOrder();
                $email = $order->getCustomerEmail();
                $increementId = $order->getIncrementId()."-".$vendorOrder->getVendorOrderId();
                $adminemail = $this->scopeConfig->getValue('trans_email/ident_sales/email');
                $userEmails = $this->usersModel->getUserEmails($vId, 'Magedelight_Sales::manage_orders');
                $this->_sendNotification($email, $increementId, $userEmails, $vendorOrder->getStoreId());
                $this->_sendNotificationAdminEmail(
                    $adminemail,
                    $increementId,
                    $displayName,
                    $vendorOrder->getStoreId()
                );
            }
        }
    }

    /**
     * @param $email
     * @param $increementId
     * @param array $userEmails
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotification(
        $email,
        $increementId,
        $userEmails = [],
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $templateVars = [
            'increment_id' => $increementId
        ];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope('general')
            ->addTo($email);
        if (!empty($userEmails)) {
            foreach ($userEmails as $userEmail) {
                $this->_transportBuilder->addTo($userEmail);
            }
        }
        $transport = $this->_transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }

    /**
     * @param $adminemail
     * @param $increementId
     * @param $businessName
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotificationAdminEmail(
        $adminemail,
        $increementId,
        $businessName,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $templateVars = [
            'increment_id' => $increementId,
            'business_name' => $businessName
        ];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_ADMIN_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope('general')
            ->addTo($adminemail)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
