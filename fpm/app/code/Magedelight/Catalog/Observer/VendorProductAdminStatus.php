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

class VendorProductAdminStatus implements ObserverInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_product_status/template';

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
     * @param \Magedelight\Theme\Model\Users $usersModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magedelight\Catalog\Model\ProductRequest $vendorproductRequest,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magedelight\Theme\Model\Users $usersModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->vendorproductRequest = $vendorproductRequest;
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
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_product_status/enabled')) {
            $id = $observer->getEvent()->getId();
            $status = $observer->getEvent()->getProductStatus();
            $postData = $observer->getEvent()->getPostData();

            if ($status == "disapproved") {
                $status = 'Disapproved';
            } else {
                $status = 'Approved';
            }

            $userEmails = $this->usersModel->getUserEmails(
                $postData['vendor_id'],
                'Magedelight_Catalog::manage_products'
            );
            $flag = true;
            foreach (['email', 'name', 'store_id'] as $field) {
                if (is_array($postData) && !array_key_exists($field, $postData)) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                $this->_sendNotification($status, $postData, $userEmails);
            }
        }
    }

    /**
     * @param $status
     * @param $postData
     * @param array $userEmails
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotification($status, $postData, $userEmails = [])
    {
        $templateVars = [
            'status' => $status,
            'product_name' => $postData['name'],
            'vendor_name' => $postData['business_name']
        ];
        /* Email templates not working on 0 store id. So set default store for notifications. */
        $storeId = $this->_storeManager->getDefaultStoreView()->getStoreId();
        if (!$storeId) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
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
                ->addTo($postData['email']);

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
