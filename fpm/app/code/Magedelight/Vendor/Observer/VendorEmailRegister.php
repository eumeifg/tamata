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

/**
 * Description of Register
 *
 * @author Rocket Bazaar Core Team
 */
class VendorEmailRegister implements ObserverInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    const XML_PATH_EMAIL_TEMPLATE  = 'emailconfiguration/new_vendor/template';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/new_vendor/notification')) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $store_email = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
            $postData = $this->request->getPost();
            $templateVars = [
                'email' => $postData['email'],
                'vendor_name' => $postData['business_name'],
                'store_email' => $store_email
            ];

            if (!$storeId = $this->storeManager->getStore()->getId()) {
                $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }
            $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
                    ->setTemplateOptions(
                        [
                                'area'  => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                                'store' => $storeId,
                            ]
                    )
                    ->setFromByScope('general')
                    ->setTemplateVars($templateVars)
                    ->addTo($postData['email'])
                    ->getTransport();
            try {
                $transport->sendMessage();
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->_messageManager->addException(
                    $e,
                    __('Email could not be sent. Please try again or contact us.')
                );
            }
        }
    }
}
