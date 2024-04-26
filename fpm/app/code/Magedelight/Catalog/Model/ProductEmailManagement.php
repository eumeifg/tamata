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
namespace Magedelight\Catalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Catalog\Model\ProductRequest;

/**
 * Handle various vendor product actions
 * @author Rocket Bazaar Core Team
 * Created at 04 April, 2016 2:20:31 PM
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ProductEmailManagement
{

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * Configuration paths for email templates and identities
     */
    const XML_PATH_NEW_PRODUCT_NOTIFICATION_EMAIL_ADMIN_TEMPLATE =
        'vendor_product/request/new_email_notification_admin_template';

    const XML_PATH_NOTIFICATION_EMAIL_IDENTITY = 'vendor_product/request/email_identity';

    const XML_PATH_NOTIFICATION_EMAIL_RECIPIENT = 'vendor_product/request/email_recipient';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    const NEW_PRODUCT_ADMIN_REQUEST = 'new_product_admin_request';

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        ProductRepositoryInterface $productRepository,
        \Magedelight\Backend\Model\Auth\Session $authSession
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->authSession = $authSession;
    }

    /**
     * Retrieve Vendor from session
     * @return \Magedelight\Vendor\Model\Vendor|\Magento\User\Model\User
     */
    protected function getVendor()
    {
        return $this->authSession->getUser();
    }

    /**
     * Send vendor prdouct request email to admin
     * @param \Magedelight\Catalog\Model\ProductRequest
     * @param array $requestData
     * @return \Magedelight\Catalog\Model\ProductEmailManagement
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendVendorNewProductRequestEmail(ProductRequest $productRequest, $requestData = [])
    {
        $types = $this->getTemplateTypes();

        $type = self::NEW_PRODUCT_ADMIN_REQUEST;

        if (!isset($types[$type])) {
            throw new LocalizedException(__('Please correct the transactional account email type.'));
        }

        if (array_key_exists('vital', $requestData)) {
            $productRequest->setName($requestData['vital']['name']);
        }

        $store = $this->storeManager->getStore($productRequest->getStoreId());

        $this->sendEmailAdminTemplate(
            $this->getVendor(),
            $types[$type],
            self::XML_PATH_NOTIFICATION_EMAIL_IDENTITY,
            self::XML_PATH_NOTIFICATION_EMAIL_RECIPIENT,
            ['vendor' => $this->getVendor(), 'product_request' => $productRequest, 'store' => $store],
            $productRequest->getStoreId()
        );

        return $this;
    }

    /**
     * Send corresponding email template
     *
     * @param VendorInterface $vendor
     * @param string $template configuration path of email template
     * @param $sender
     * @param string $receiver configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function sendEmailAdminTemplate(
        $vendor,
        $template,
        $sender,
        $receiver,
        $templateParams = [],
        $storeId = null
    ) {
        if ($this->scopeConfig->getValue($receiver, ScopeInterface::SCOPE_STORE, $storeId)) {
            $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
            $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setFromByScope('general')
            ->setTemplateOptions(
                ['area' => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE, 'store' => $storeId]
            )
            ->setTemplateVars($templateParams)
            ->addTo($this->scopeConfig->getValue($receiver, ScopeInterface::SCOPE_STORE, $storeId))
            ->getTransport();

            $transport->sendMessage();
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getTemplateTypes()
    {
        /**
         * self::NEW_PRODUCT_ADMIN_REQUEST    new product request notification email
         */
        $types = [
            self::NEW_PRODUCT_ADMIN_REQUEST => self::XML_PATH_NEW_PRODUCT_NOTIFICATION_EMAIL_ADMIN_TEMPLATE
        ];
        return $types;
    }
}
