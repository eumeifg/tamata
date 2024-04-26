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
namespace Magedelight\Vendor\Controller\Sellerhtml\Categories\Request;

use Magedelight\Backend\App\Action\Context;
use Magedelight\Vendor\Model\CategoryRequest;
use Magento\Framework\View\Result\PageFactory;

/**
 * Description of Save.
 *
 * @author Rocket Bazaar Core Team
 */
class Save extends \Magedelight\Backend\App\Action
{
    const XML_PATH_EMAIL_NEW_CATEGORY_REQUEST_ADMIN_TEMPLATE = 'vendor/new_category_request_admin_notification/template';

    const XML_PATH_EMAIL_NEW_CATEGORY_REQUEST_VENDOR_TEMPLATE = 'vendor/new_category_request_vendor_notification/template';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magedelight\Vendor\Api\Data\CategoryRequestInterface
     */
    protected $categoryRequest;

    /**
     * @var \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface
     */
    protected $categoryRequestRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Api\Data\CategoryRequestInterface $categoryRequest
     * @param \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magedelight\Vendor\Api\Data\CategoryRequestInterface $categoryRequest,
        \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->categoryRequest = $categoryRequest;
        $this->categoryRequestRepository = $categoryRequestRepository;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->vendorRepository = $vendorRepository;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        $tab = $this->getRequest()->getParam('tab');
        $categories = '';
        if (array_key_exists('child_categories', $data)) {
            $categories = implode(',', $data['child_categories']);
        }
        try {
            if (!empty($categories)) {
                $this->categoryRequest->setVendorId($this->_auth->getUser()->getVendorId());
                $this->categoryRequest->setCategories($categories);
                $this->categoryRequest->setStoreId($this->storeManager->getStore()->getId());
                $request = $this->categoryRequestRepository->save($this->categoryRequest);
                $this->sendNotificationToAdmin($request);
                $this->sendNotificationToVendor($request);
                $this->messageManager->addSuccess(__('New category request has been successfully raised.'));
            } else {
                $this->messageManager->addErrorMessage(
                    __('Please select unchecked or not in your list categories to raise the request.')
                );
            }
            $tabData = explode(",", $this->getRequest()->getParam('tab'));
            if (isset($tabData[1])) {
                $tabData[1] = $tabData[1] + 1;
            }
            $tab = implode(',', $tabData);
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/', ['tab' => $tab]);
            }
            return $resultRedirect->setPath('*/*/', ['tab' => $tab]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Zend_Db_Statement_Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/', ['tab' => $tab]);
    }

    /**
     * @param Magedelight\Vendor\Api\Data\CategoryRequestInterface $request
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function sendNotificationToAdmin($request)
    {
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $email = $this->scopeConfig->getValue('contact/email/recipient_email', $storeScope);
            if ($email) {
                $template = $this->scopeConfig->getValue(
                    self::XML_PATH_EMAIL_NEW_CATEGORY_REQUEST_ADMIN_TEMPLATE,
                    $storeScope
                );
                $templateVars = [];
                $templateVars['categories'] = $this->getCategories($request);

                $templateVars['vendor_name'] = $this->_auth->getUser()->getBusinessName();
                $this->_sendNotification($email, $template, $templateVars);
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }

    /**
     * @param Magedelight\Vendor\Api\Data\CategoryRequestInterface $request
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function sendNotificationToVendor($request)
    {
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            if (!empty($request->getVendorId())) {
                $vendor = $this->vendorRepository->getById($request->getVendorId());
                if ($vendor->getEmail()) {
                    $template = $this->scopeConfig->getValue(
                        self::XML_PATH_EMAIL_NEW_CATEGORY_REQUEST_VENDOR_TEMPLATE,
                        $storeScope
                    );
                    $templateVars = [];
                    $templateVars['categories'] = $this->getCategories($request);
                    $templateVars['vendor_name'] = $this->_auth->getUser()->getBusinessName();
                    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    $this->_sendNotification($vendor->getEmail(), $template, $templateVars);
                }
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }

    /**
     * @param type $email
     * @param type $template
     * @param type $templateVars
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _sendNotification($email, $template, $templateVars)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions(
                [
                        'area' => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                        'store' => $storeId,
                    ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope('general')
            ->addTo($email)
            ->getTransport();
        $transport->sendMessage();
    }

    /**
     *
     * @param string $request
     * @return array
     */
    protected function getCategories($request = '')
    {
        $categories = explode(',', $request->getCategories());
        $categoryNameCollect = [];
        foreach ($categories as $categoryid) {
            $category = $this->categoryFactory->create()->load($categoryid);
            $categoryNameCollect[] = $category->getName();
        }
        return implode(",", $categoryNameCollect);
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products_requested_categories');
    }
}
