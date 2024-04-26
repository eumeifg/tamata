<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\MailException;

/**
 * Description of Register
 *
 * @author Rocket Bazaar Core Team
 */
class SendEmailAfterStatusRequestSave implements ObserverInterface
{
    const XML_PATH_NOTIFICATION_EMAIL_IDENTITY = 'vendor/status_request/email_identity';
    const XML_PATH_NOTIFICATION_EMAIL_RECIPIENT = 'vendor/status_request/email_recipient';
    const XML_PATH_STATUS_NOTIFICATION_EMAIL_ADMIN_TEMPLATE = 'vendor/status_request/email_notification_admin_template';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendor = $observer->getEvent()->getVendor();
        $statusRequestType = $observer->getEvent()->getStatusRequestType();

        $storeId = 0;
        $store = $this->_storeManager->getStore($storeId);

        if ($statusRequestType == 2) {
            $data = ['vendor' => $vendor, 'store' => $store, 'statusRequestTypeClosed'=> true];
        } else {
            $data = ['vendor' => $vendor, 'store' => $store, 'statusRequestTypeVacation'=> true];
        }
        $this->_sendNotificationAdminEmail(
            $vendor,
            self::XML_PATH_STATUS_NOTIFICATION_EMAIL_ADMIN_TEMPLATE,
            self::XML_PATH_NOTIFICATION_EMAIL_IDENTITY,
            self::XML_PATH_NOTIFICATION_EMAIL_RECIPIENT,
            $data,
            $storeId
        );
    }

    protected function _sendNotificationAdminEmail(
        $vendor,
        $template,
        $sender,
        $receiver,
        $templateParams = [],
        $storeId = null
    ) {
        try {
            $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
            $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area'  => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($templateParams)
            ->setFromByScope($this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId))
            ->addTo($this->scopeConfig->getValue($receiver, ScopeInterface::SCOPE_STORE, $storeId))
            ->setReplyTo($vendor->getEmail())
            ->getTransport();
            $transport->sendMessage();
        } catch (MailException $e) {
            $this->logger->critical($e);
        }
    }
}
