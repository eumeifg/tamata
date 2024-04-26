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
class VendorCategoryCommissionSave implements ObserverInterface
{

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory
     */
    private $vendorCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    const XML_PATH_EMAIL_TEMPLATE_NEW = 'emailconfiguration/vendor_catcommission_new/template';
    const XML_PATH_EMAIL_TEMPLATE_CHANGE = 'emailconfiguration/vendor_catcommission_change/template';

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     *
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    public function __construct(
        \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $_storeManager
    ) {
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $_storeManager;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $newRateEmail = $this->scopeConfig->getValue('emailconfiguration/vendor_catcommission_new/enabled');
        $changeRateEmail = $this->scopeConfig->getValue('emailconfiguration/vendor_catcommission_change/enabled');

        $isNew = $observer->getEvent()->getIsNew();
        $nComm = $observer->getEvent()->getNew();
        $categoryId = $observer->getEvent()->getCategory();
        $websiteId = $observer->getEvent()->getWebsite();
        $isCommChanged = false;
        if (!$isNew) {
            $oComm = $observer->getEvent()->getOld();
            $oCat = $observer->getEvent()->getOldCategory();
            $isCommChanged = ($oComm['commission'] !== $nComm['commission'] ||
                $oComm['marketplace_fee'] !== $nComm['marketplace_fee'] ||
                $oComm['cancellation_fee'] !== $nComm['cancellation_fee']
                );
            $isNew = ($oCat !== $categoryId) ? 1 : 0;
        } else {
            $oComm = [];
        }
        $categoryData = $this->categoryRepository->get($categoryId);
        $catName = $categoryData->getName();
        $collection = $this->vendorCollectionFactory->create();
        $collection->_addWebsiteData(['status'], $websiteId)
            ->addFieldToFilter('rvwd.status', ['in' => ['1', '4']]);

        if ($isNew && $newRateEmail || !$isNew && $changeRateEmail) {
            if ($isNew || $isCommChanged) {
                foreach ($collection as $collData) {
                    $email = $collData->getEmail();
                    $vendorName = $collData->getBusinessName();
                    $this->_sendNotification($email, $isNew, $nComm, $oComm, $catName, $vendorName, $websiteId);
                }
            }
        }
    }
    
    /**
     *
     * @param string $email
     * @param boolean $isNew
     * @param array $nComm
     * @param array $oComm
     * @param string $catName
     * @param string $vendorName
     * @param int $websiteId
     */
    protected function _sendNotification($email, $isNew, $nComm, $oComm, $catName, $vendorName, $websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);

        $templateVars = [
            'is_new' => $isNew,
            'cur_comm' => $nComm['commission'],
            'cur_mp_fee' => $nComm['marketplace_fee'],
            'cur_can_fee' => $nComm['cancellation_fee'],
            'category' => $catName,
            'store_email' => $storeEmail,
            'vendor_name' => $vendorName
        ];

        if (!$isNew) {
            $templateVars['old_comm'] = $oComm['commission'];
            $templateVars['old_mp_fee'] = $oComm['marketplace_fee'];
            $templateVars['old_can_fee'] = $oComm['cancellation_fee'];
        }
        $emailTemplate = ($isNew) ? self::XML_PATH_EMAIL_TEMPLATE_NEW : self::XML_PATH_EMAIL_TEMPLATE_CHANGE;
        $store_id = $this->_storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue($emailTemplate, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $store_id,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope('general')
            ->addTo($email)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\MailException(__('Email could not be sent to vendors.'));
        }
    }
}
