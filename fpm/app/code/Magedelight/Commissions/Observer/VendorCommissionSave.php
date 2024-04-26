<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Description of Register
 * @author Rocket Bazaar Core Team
 */
class VendorCommissionSave implements ObserverInterface
{
    const XML_PATH_EMAIL_TEMPLATE_NEW = 'emailconfiguration/vendor_commission_new/template';
    const XML_PATH_EMAIL_TEMPLATE_CHANGE = 'emailconfiguration/vendor_commission_change/template';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     *
     * @param \Magedelight\Vendor\Model\Vendor $vendor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Theme\Model\Users $usersModel
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    public function __construct(
        \Magedelight\Vendor\Model\Vendor $vendor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Theme\Model\Users $usersModel,
        \Magento\Store\Model\StoreManagerInterface $_storeManager
    ) {
        $this->request = $request;
        $this->vendor = $vendor;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->usersModel = $usersModel;
        $this->_storeManager = $_storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $newRateEmail = $this->scopeConfig->getValue('emailconfiguration/vendor_commission_new/enabled');
        $changeRateEmail = $this->scopeConfig->getValue('emailconfiguration/vendor_commission_change/enabled');
        $vId = $observer->getEvent()->getVendorId();
        $isNew = $observer->getEvent()->getIsNew();
        $nComm = $observer->getEvent()->getNew();
        $websiteId = $observer->getEvent()->getWebsite();
        if (!$isNew) {
            $oComm = $observer->getEvent()->getOld();
            $oldVId = $observer->getEvent()->getOldVendorId();
            $isNew = ($oldVId !== $vId) ? 1 : 0;
        } else {
            $oComm = [];
        }

        $collection = $this->vendor->getCollection()->addFieldToFilter('vendor_id', ['in' => $vId]);
        $email = $collection->getFirstItem()->getEmail();
        $vendorName = $collection->getFirstItem()->getBusinessName();
        $userEmails = $this->usersModel->getUserEmails(
            $collection->getFirstItem()->getVendorId(),
            'Magedelight_Vendor::financial'
        );
        if ($isNew && $newRateEmail || !$isNew && $changeRateEmail) {
            $this->_sendNotification($email, $vendorName, $isNew, $oComm, $nComm, $websiteId, $userEmails);
        }
    }

    protected function _sendNotification($email, $vendorName, $isNew, $oComm, $nComm, $websiteId, $userEmails = [])
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store_email = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);

        $templateVars = [
            'cur_comm' => $nComm['commission'],
            'cur_mp_fee' => $nComm['marketplace_fee'],
            'cur_can_fee' => $nComm['cancellation_fee'],
            'store_email' => $store_email,
            'vendor_name' => $vendorName
        ];

        if (!$isNew) {
            $templateVars['old_comm'] = $oComm['commission'];
            $templateVars['old_mp_fee'] = $oComm['marketplace_fee'];
            $templateVars['old_can_fee'] = $oComm['cancellation_fee'];
        }
        $emailTemplate = ($isNew) ? self::XML_PATH_EMAIL_TEMPLATE_NEW : self::XML_PATH_EMAIL_TEMPLATE_CHANGE;
        $store_id = $this->_storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue($emailTemplate, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $store_id,
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
}
