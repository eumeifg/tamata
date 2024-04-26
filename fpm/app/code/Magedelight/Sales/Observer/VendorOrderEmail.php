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

class VendorOrderEmail implements ObserverInterface
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $_vendorHelper;

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_order/template';

    /**
     * @param \Magedelight\Sales\Model\Order $vendororder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Vendor\Helper\Data $_vendorHelper
     * @param \Magedelight\Theme\Model\Users $usersModel
     */
    public function __construct(
        \Magedelight\Sales\Model\Order $vendororder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Vendor\Helper\Data $_vendorHelper,
        \Magedelight\Theme\Model\Users $usersModel
    ) {
        $this->vendororder = $vendororder;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_vendorHelper = $_vendorHelper;
        $this->usersModel = $usersModel;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_order/enabled')) {
            $order_id =  $observer->getEvent()->getOrder()->getEntityId();
            $collections = $this->vendororder->getCollection()->addFieldToFilter('order_id', $order_id);

            foreach ($collections as $collection) {
                $vendordetails = $this->_vendorHelper->getVendorDetails(
                    $collection->getVendorId(),
                    ['email'],
                    ['business_name']
                );
                $userEmails = $this->usersModel->getUserEmails(
                    $collection->getVendorId(),
                    'Magedelight_Sales::manage_orders'
                );
                $purchaseorderid = $collection->getIncrementId();
                $storeId = $collection->getStoreId();
                $this->sendEmail($vendordetails, $purchaseorderid, $userEmails, $storeId);
            }
        }
    }

    /**
     * @param \Magedelight\Vendor\Model\Vendor $vendordetails
     * @param int $purchaseorderid
     * @param array $userEmails
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function sendEmail(
        $vendordetails,
        $purchaseorderid,
        $userEmails = [],
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $templateVars = [
            'vendor_name' => $vendordetails->getBusinessName(),
            'purchase_order_id' => $purchaseorderid,
            'vendor_data' => $vendordetails
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
            ->addTo($vendordetails->getEmail());

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
}
