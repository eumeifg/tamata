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

class NotifyAdminOnVendorProfileUpdate implements ObserverInterface
{

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_profile_update/template';
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendor = $observer->getEvent()->getVendor();
        if ($vendor && $vendor->getVendorId()) {
            $this->_sendNotificationAdminEmail(
                $this->scopeConfig->getValue('contact/email/recipient_email'),
                $vendor
            );
        }
    }

    protected function _sendNotificationAdminEmail($adminemail, $vendor)
    {
        $templateVars = [
            'business_name' => $vendor->getBusinessName(),
            'status_description' => $vendor->getStatusDescription()
        ];
        
        if (!$storeId = $this->storeManager->getStore()->getId()) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => $storeId,
                ]
            )
            ->setFromByScope('general')
            ->setTemplateVars($templateVars)
            ->addTo($adminemail)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            throw new \Exception(__('Email could not be sent. Please try again or contact us.'));
        }
    }
}
