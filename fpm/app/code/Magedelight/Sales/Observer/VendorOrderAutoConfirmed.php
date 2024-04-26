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

/**
 * Email notification to vendor when order setting is auto confirmed
 */
class VendorOrderAutoConfirmed implements ObserverInterface
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    private $_vendorHelper;

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_order/template';

    /**
     * VendorOrderAutoConfirmed constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magedelight\Sales\Model\Order $vendororder
     * @param \Magedelight\Vendor\Model\Vendor $vendor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Vendor\Helper\Data $_vendorHelper
     * @param \Magedelight\Theme\Model\Users $usersModel
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Sales\Model\Order $vendororder,
        \Magedelight\Vendor\Model\Vendor $vendor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Vendor\Helper\Data $_vendorHelper,
        \Magedelight\Theme\Model\Users $usersModel
    ) {
        $this->vendororder = $vendororder;
        $this->vendor = $vendor;
        $this->logger = $logger;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_vendorHelper = $_vendorHelper;
        $this->usersModel = $usersModel;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_order/enabled')) {
            if ($this->scopeConfig->getValue('vendor_sales/order/auto_confirm')) {
                $order_id = $observer->getEvent()->getOrderId();
                $collections = $this->vendororder->getCollection()->addFieldToFilter('order_id', $order_id);

                foreach ($collections as $collection) {
                    $vendordetails = $this->vendor->load($collection->getVendorId(), 'vendor_id');
                    $purchaseorderid = $collection->getIncrementId();
                    $to = $vendordetails->getEmail();
                    $name = $this->_vendorHelper->getVendorNameById($vendordetails->getVendorId());
                    $userEmails = $this->usersModel->getUserEmails(
                        $collection->getVendorId(),
                        'Magedelight_Sales::manage_orders'
                    );
                    $storeId = $collection->getStoreId();
                    $this->sendEmail($to, $name, $purchaseorderid, $vendordetails, $userEmails, $storeId);
                }
            }
        }
    }

    /**
     * @param $to
     * @param $name
     * @param $purchaseorderid
     * @param $vendordetails
     * @param array $userEmails
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function sendEmail(
        $to,
        $name,
        $purchaseorderid,
        $vendordetails,
        $userEmails = [],
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $templateVars = [
            'vendor_name' => $name,
            'purchase_order_id' => $purchaseorderid,
            'vendor_data' => $vendordetails
        ];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId ,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope('general')
            ->addTo($to);
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
