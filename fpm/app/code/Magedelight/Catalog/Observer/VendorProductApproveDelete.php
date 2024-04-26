<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class VendorProductApproveDelete implements ObserverInterface
{
    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_product_approve_delete/template';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magedelight\Theme\Model\Users
     */
    protected $usersModel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * VendorProductApproveDelete constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magedelight\Theme\Model\Users $usersModel
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magedelight\Theme\Model\Users $usersModel,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->usersModel = $usersModel;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_product_approve_delete/enabled')) {
            $id = $observer->getEvent()->getId();
            $notificationData = $observer->getEvent()->getNotificationData();

            if (!empty($notificationData)) {
                $userEmails = $this->usersModel->getUserEmails(
                    $notificationData['vendor_id'],
                    'Magedelight_Catalog::manage_products'
                );
                $this->_sendNotification(
                    $notificationData['email'],
                    $notificationData['vendor_name'],
                    $notificationData['offer_sku'],
                    $notificationData['store_id'],
                    $userEmails
                );
            }
        }
    }

    /**
     * @param string $email
     * @param string $vendorName
     * @param string $offerSku
     * @param integer $storeId
     * @param array $userEmails
     */
    protected function _sendNotification(
        $email,
        $vendorName,
        $offerSku,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        $userEmails = []
    ) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store_name = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $templateVars = [
            'vendor_name' => $vendorName,
            'offer_sku' => $offerSku,
            'store_name' => $store_name
        ];

        $this->transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
                ->setTemplateOptions(
                    [
                            'area' => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                            'store' => $storeId,
                        ]
                )
                ->setFromByScope('general')
                ->setTemplateVars($templateVars)
                ->addTo($email);

        if (!empty($userEmails)) {
            foreach ($userEmails as $userEmail) {
                $this->transportBuilder->addTo($userEmail);
            }
        }

        $transport = $this->transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
