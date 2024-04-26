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

class VendorProductAdminMassDelete implements ObserverInterface
{

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_product_massdelete/template';

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
     * @var \Magedelight\Theme\Model\Users
     */
    protected $usersModel;

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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * VendorProductAdminMassDelete constructor.
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
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_product_massdelete/enabled')) {
            $id = $observer->getEvent()->getIds();
            $collection = $this->vendorproductRequest->getCollection()->addFieldToFilter(
                'main_table.product_request_id',
                ['in' => $id]
            );
            $collection->getSelect()->joinLeft(
                ['mvprs' => 'md_vendor_product_request_store'],
                "mvprs.product_request_id = main_table.product_request_id",
                [
                    'mvprs.name'
                ]
            );
            $collection->getSelect()->joinLeft(
                ['cpe' => 'catalog_product_entity'],
                'cpe.entity_id = main_table.marketplace_product_id',
                ['cpe.entity_id']
            );
            $collection->getSelect()->joinLeft(
                ['cpev' => 'catalog_product_entity_varchar'],
                'cpev.row_id = cpe.row_id AND cpev.attribute_id = ' . $this->getAttributeIdofProductName() . '',
                ['pname' => 'value']
            );
            $collection->getSelect()->joinLeft(
                ['rbv' => 'md_vendor'],
                "main_table.vendor_id = rbv.vendor_id",
                [
                    'rbv.email'
                ]
            )->joinLeft(
                ['rbvw' => 'md_vendor_website_data'],
                "rbv.vendor_id = rbvw.vendor_id",
                [
                    'rbvw.business_name as vendor_name'
                ]
            )->group('main_table.product_request_id');

            /* Prepare vendor-wise data. */
            $notificationData = [];
            foreach ($collection as $data) {
                $notificationData[$data->getVendorId()]['email'] = $data->getEmail();
                $notificationData[$data->getVendorId()]['vendor_name'] = $data->getVendorName();
                $notificationData[$data->getVendorId()]['products'][] = ($data->getPname()) ?
                    $data->getPname() : $data->getName();
                $notificationData[$data->getVendorId()]['store_id'] = $data->getStoreId();
            }
            /* Prepare vendor-wise data. */

            foreach ($notificationData as $vendorId => $notification) {
                $userEmails = $this->usersModel->getUserEmails(
                    $data->getVendorId(),
                    'Magedelight_Catalog::manage_products'
                );
                $this->_sendNotification(
                    $notification['email'],
                    implode(', ', $notification['products']),
                    $notification['vendor_name'],
                    $notification['store_id'],
                    $userEmails
                );
            }
        }
    }

    /**
     * @return integer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttributeIdofProductName()
    {
        return $this->attributeRepository->get('catalog_product', 'name')->getId();
    }

    /**
     * @param string $email
     * @param string $products
     * @param string $vendorName
     * @param int $storeId
     * @param array $userEmails
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotification(
        $email,
        $products,
        $vendorName,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        $userEmails = []
    ) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store_name = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);

        $templateVars = [
            'vendor_name'=>$vendorName,
            'products' => $products,
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
