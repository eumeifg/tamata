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

class VendorProductAdminList implements ObserverInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_product_list/template';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magedelight\Catalog\Model\Product
     */
    protected $vendorproduct;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magedelight\Theme\Model\Users
     */
    protected $usersModel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @param \Magedelight\Catalog\Model\Product $vendorproduct
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Theme\Model\Users $usersModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magedelight\Catalog\Model\Product $vendorproduct,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Theme\Model\Users $usersModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->attributeRepository = $attributeRepository;
        $this->vendorproduct = $vendorproduct;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->usersModel = $usersModel;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_product_list/enabled')) {
            $id = $observer->getEvent()->getId();
            $collection = $this->vendorproduct->getCollection()->addFieldToFilter('main_table.vendor_product_id', $id);
            $collection->getSelect()
                ->joinLeft(
                    ['rbv' => 'md_vendor'],
                    "main_table.vendor_id = rbv.vendor_id",
                    ['rbv.email']
                );

            $vendor = $collection->getFirstItem();
            if ($vendor) {
                $email = $vendor->getEmail();
                $pname = $vendor->getProductName();
                $storeId = $this->_storeManager->getDefaultStoreView()->getStoreId();
                $vendorBusinessName = $vendor->getBusinessName();
                $userEmails = $this->usersModel->getUserEmails(
                    $vendor->getVendorId(),
                    'Magedelight_Catalog::manage_products'
                );
                $this->_sendNotification($email, $pname, $vendorBusinessName, $userEmails, $storeId);
            }
        }
    }

    /**
     * @param $email
     * @param $pname
     * @param $vendorBusinessName
     * @param array $userEmails
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotification(
        $email,
        $pname,
        $vendorBusinessName,
        $userEmails = [],
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store_name = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);

        $templateVars = [
            'product_name' => $pname,
            'store_name' => $store_name,
            'vendor_name' => $vendorBusinessName
        ];
        $this->_transportBuilder
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
                $this->_transportBuilder->addTo($userEmail);
            }
        }

        $transport = $this->_transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
