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

class VendorProductAdminDelete implements ObserverInterface
{

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_product_delete/template';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequest
     */
    protected $vendorproductRequest;

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
     * @param \Magedelight\Catalog\Model\ProductRequest $vendorproductRequest
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Theme\Model\Users $usersModel
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magedelight\Catalog\Model\ProductRequest $vendorproductRequest,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Theme\Model\Users $usersModel,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->attributeRepository = $attributeRepository;
        $this->vendorproductRequest = $vendorproductRequest;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->usersModel = $usersModel;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_product_delete/enabled')) {
            $id = $observer->getEvent()->getId();
            $collection = $this->vendorproductRequest->getCollection()->addFieldToFilter('product_request_id', $id);
            if ($collection->getFirstItem()->getMarketplaceProductId()) {
                $collection->getSelect()->joinLeft(
                    ['cpe' => 'catalog_product_entity'],
                    'cpe.entity_id = main_table.marketplace_product_id',
                    ['cpe.entity_id']
                );
                $collection->getSelect()->joinLeft(
                    ['cpev' => 'catalog_product_entity_varchar'],
                    'cpev.row_id = cpe.row_id AND cpev.attribute_id=' . $this->getAttributeIdofProductName() . '',
                    ['name' => 'value']
                );
                $collection->getSelect()->joinLeft(
                    ['rbv' => 'md_vendor'],
                    "main_table.vendor_id = rbv.vendor_id",
                    [
                        'rbv.email'
                    ]
                );
                $collection->getSelect()->joinLeft(
                    ['rbwd' => 'md_vendor_website_data'],
                    "main_table.vendor_id = rbv.vendor_id",
                    [
                        'rbwd.business_name'
                    ]
                );
            } else {
                $collection->getSelect()->joinLeft(
                    ['rbv' => 'md_vendor'],
                    "main_table.vendor_id = rbv.vendor_id",
                    [
                        'rbv.email',
                    ]
                );
                $collection->getSelect()->joinLeft(
                    ['rbwd' => 'md_vendor_website_data'],
                    "main_table.vendor_id = rbv.vendor_id",
                    [
                        'rbwd.business_name'
                    ]
                );
            }

            $collectionData = $collection->getData();

            $storeId = null;
            if (!empty($collectionData[0]) && array_key_exists('store_id', $collectionData[0])) {
                $storeId = $collectionData[0]['store_id'];
            }

            $pname = $collectionData[0]['business_name'];
            $email = $collectionData[0]['email'];
            $userEmails = $this->usersModel->getUserEmails(
                $collectionData[0]['vendor_id'],
                'Magedelight_Catalog::manage_products'
            );
            $this->_sendNotification($email, $pname, $userEmails, $storeId);
        }
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttributeIdofProductName()
    {
        return $this->attributeRepository->get('catalog_product', 'name')->getId();
    }

    /**
     * @param $email
     * @param $pname
     * @param array $userEmails
     * @param int $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotification(
        $email,
        $pname,
        $userEmails = [],
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store_name = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $templateVars = [
            'product_name' => $pname,
            'store_name' => $store_name
        ];

        $this->_transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
                ->setTemplateOptions(
                    [
                            'area' => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                            'store' => $this->storeManager->getDefaultStoreView()->getStoreId(),
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
