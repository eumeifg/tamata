<?php
namespace Magedelight\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerOrderCancel implements ObserverInterface
{
    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/customer_order_cancel/vendor_template';
    const XML_PATH_EMAIL_ADMIN_TEMPLATE = 'emailconfiguration/customer_order_cancel/admin_template';

    private $vendorOrderFactory;

    /**
     * CustomerOrderCancel constructor.
     * @param \Magedelight\Vendor\Model\VendorRepository $vendor
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magedelight\Vendor\Model\VendorRepository $vendor,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->vendor = $vendor;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->vendorOrderFactory = $vendorOrderFactory;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendorOrderIds = $observer->getEvent()->getVendorOrderIds();
        foreach ($vendorOrderIds as $voId) {
            $vendorOrder = $this->vendorOrderFactory->create()->load($voId);
            $order = $vendorOrder->getOriginalOrder();
            $orderIsConfirm = $order->getIsConfirmed();
            $orderIncrementId = $order->getIncrementId();
            $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            $vId = $vendorOrder->getVendorId();
            $storeId = $vendorOrder->getStoreId();
            $vendorData = $this->vendor->getById($vId);
            $businessName = $vendorData->getBusinessName();
            $vendorEmail = $vendorData->getEmail();
            $vendorOrderIncrementId = $vendorOrder->getIncrementId();
            /*$userEmails = $this->usersModel->getUserEmails($vId, 'RB_Vendor::manage_orders');*/
            $userEmails = [];
            if ($orderIsConfirm) {
                $this->_sendNotification(
                    $vendorEmail,
                    $businessName,
                    $vendorOrderIncrementId,
                    $storeId,
                    $userEmails,
                    $customerName
                );
            }
        }
        $adminEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email');
        $this->_sendNotificationAdminEmail($adminEmail, $orderIncrementId, $customerName, $storeId);
    }

    /**
     * @param type $vendorEmail
     * @param type $businessName
     * @param type $incrementId
     * @param int $storeId
     * @param array $userEmails
     * @param type $customerName
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotification(
        $vendorEmail,
        $businessName,
        $incrementId,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        $userEmails = [],
        $customerName
    ) {
        // Admin Email Send
        $salesRepresentativeEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email');
        $salesRepresentativeName = $this->scopeConfig->getValue('trans_email/ident_sales/name');
        if ($salesRepresentativeEmail == "" || $salesRepresentativeName == "") {
            return;
        }

        $templateVars = [
            'increment_id' => $incrementId,
            'business_name' => $businessName,
            'customer_name' => $customerName
        ];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )->setTemplateVars($templateVars)
            ->setFromByScope(['name' => $salesRepresentativeName,'email' => $salesRepresentativeEmail])
            ->addTo($vendorEmail);

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
     * @param $adminEmail
     * @param $incrementId
     * @param $customerName
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotificationAdminEmail(
        $adminEmail,
        $incrementId,
        $customerName,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $salesRepresentativeEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email');
        $salesRepresentativeName = $this->scopeConfig->getValue('trans_email/ident_sales/name');
        if ($salesRepresentativeEmail == "" || $salesRepresentativeName == "") {
            return;
        }

        $templateVars = [
            'increment_id' => $incrementId,
            'customer_name' => $customerName,
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
            ->setFromByScope(['name' => $salesRepresentativeName,'email' => $salesRepresentativeEmail])
            ->setTemplateVars($templateVars)
            ->addTo($adminEmail)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
