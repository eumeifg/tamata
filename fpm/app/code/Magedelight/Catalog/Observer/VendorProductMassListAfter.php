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

class VendorProductMassListAfter implements ObserverInterface
{
    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_product_masslist/template';

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $vendorProductFactory;

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
     * VendorProductMassListAfter constructor.
     * @param \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory $vendorProductFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magedelight\Theme\Model\Users $usersModel
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory $vendorProductFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magedelight\Theme\Model\Users $usersModel,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->vendorProductFactory = $vendorProductFactory;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->usersModel = $usersModel;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_product_masslist/enabled')) {
            $collection = $observer->getEvent()->getCollectionObject();

            /* Prepare vendor-wise data. */
            $notificationData = [];
            if ($collection) {
                foreach ($collection as $data) {
                    $notificationData[$data->getVendorId()]['email'] = $data->getEmail();
                    $notificationData[$data->getVendorId()]['vendor_name'] = $data->getVendorName();
                    $notificationData[$data->getVendorId()]['offer_skus'][] = $data->getVendorSku();
                    $notificationData[$data->getVendorId()]['store_id'] = $data->getStoreId();
                }
            }
            /* Prepare vendor-wise data. */

            foreach ($notificationData as $vendorId => $notification) {
                $userEmails = $this->usersModel->getUserEmails(
                    $vendorId,
                    'Magedelight_Catalog::manage_products'
                );
                $this->_sendNotification(
                    $notification['email'],
                    implode(', ', $notification['offer_skus']),
                    $notification['vendor_name'],
                    $notification['store_id'],
                    $userEmails
                );
            }
        }
    }

    /**
     * @param string $email
     * @param string $offerSkus
     * @param string $vendorName
     * @param integer $storeId
     * @param array $userEmails
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotification($email, $offerSkus, $vendorName, $storeId, $userEmails = [])
    {
        $templateVars = [
            'offer_skus' => $offerSkus,
            'vendor_name' => $vendorName
        ];

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
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
