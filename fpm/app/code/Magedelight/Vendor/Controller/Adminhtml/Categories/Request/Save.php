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
namespace Magedelight\Vendor\Controller\Adminhtml\Categories\Request;

use Magedelight\Vendor\Model\Category\Request\Source\Status as RequestStatuses;

/**
 * @author Rocket Bazaar Core Team
 */
class Save extends \Magedelight\Vendor\Controller\Adminhtml\Categories\Request
{
    const XML_PATH_EMAIL_REQUEST_APPROVAL_VENDOR_NOTIFICATION_TEMPLATE = 'vendor/category_request_approval_vendor_notification/template';

    /**
     * @var \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface
     */
    protected $categoryRequestRepository;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor
     */
    protected $vendorResource;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository
     * @param \Magedelight\Vendor\Model\ResourceModel\Vendor $vendorResource
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Vendor\Api\CategoryRequestRepositoryInterface $categoryRequestRepository,
        \Magedelight\Vendor\Model\ResourceModel\Vendor $vendorResource,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->categoryRequestRepository = $categoryRequestRepository;
        $this->vendorResource = $vendorResource;
        $this->vendorRepository = $vendorRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->categoryFactory = $categoryFactory;
        $this->authSession = $authSession;
        parent::__construct($context, $coreRegistry);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue('request');
        $id = $data['request_id'];

        try {
            $request = $this->categoryRequestRepository->getById($id);
            $currentAadminEmail = $this->authSession->getUser()->getEmail();

            $categoryStr = '';
            $requestedCategories = [];
            if ($request->getId()) {
                $categoryStr = $request->getCategories();
            } else {
                return $resultRedirect->setPath('*/*/');
            }
            if (!empty($categoryStr)) {
                $requestedCategories = explode(',', $categoryStr);
            }

            if (!empty($requestedCategories) && !empty($data['status'])) {
                /* Avoid saving categories if denied by admin. */
                if ($data['status'] != RequestStatuses::STATUS_DENIED) {
                    $vendorModel = $this->vendorRepository->getById($request->getVendorId());
                    $existingCategories = $this->vendorResource->lookupCategoryIds($request->getVendorId());

                    /* Remove categories if already exist in vendor's list. Avoid saving duplicate categories. */
                    $newCategories = array_diff($requestedCategories, $existingCategories);
                    /* Remove categories if already exist in vendor's list. Avoid saving duplicate categories. */

                    $vendorModel->setCategoriesIds(array_merge($existingCategories, $newCategories));
                    $this->vendorRepository->save($vendorModel);
                }

                /* Update request status once categories updated. */
                $request = $this->categoryRequestRepository->getById($id);
                $request->setStatus($data['status']);
                if (array_key_exists('status_description', $data) && !empty($data['status_description'])) {
                    $request->setStatusDescription($data['status_description']);
                    $request->setRejectedBy($currentAadminEmail);
                }
                $this->categoryRequestRepository->save($request);
                $this->sendNotificationToVendor($request);

                if ($data['status'] == RequestStatuses::STATUS_DENIED) {
                    $this->messageManager->addSuccess(__('Category request has been rejected.'));
                } else {
                    $this->messageManager->addSuccess(__(
                        'New categories has been successfully added to vendor\'s existing category list.'
                    ));
                }
                /* Update request status once categories updated. */
            } else {
                $this->messageManager->addErrorMessage(__('Failed to save categories.'));
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Zend_Db_Statement_Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }

    protected function sendNotificationToVendor($request)
    {
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            if (!empty($request->getVendorId())) {
                $vendor = $this->vendorRepository->getById($request->getVendorId());
                $storeId = $request->getStoreId();
                if ($vendor->getEmail()) {
                    $template = $this->scopeConfig->getValue(
                        self::XML_PATH_EMAIL_REQUEST_APPROVAL_VENDOR_NOTIFICATION_TEMPLATE,
                        $storeScope
                    );
                    $templateVars = [];
                    $categories = explode(',', $request->getCategories());
                    $categoryName = '';
                    $categoryNameCollect = [];
                    foreach ($categories as $categoryid) {
                        $category = $this->categoryFactory->create()->load($categoryid);
                        $categoryNameCollect[] = $category->getName();
                    }

                    $categoryName = implode(",", $categoryNameCollect);

                    $templateVars ['msg_vendor'] = __('Dear %1,', [$vendor->getBusinessName()]);

                    if ($request->getStatus() == RequestStatuses::STATUS_DENIED) {
                        /*$templateVars['msg'] = __('Dear %1, Your request %2 for new categories
                         has been rejected.', [$vendor->getBusinessName(), $categoryName]);*/
                        $templateVars['msg'] = __('Your categories request for %1 has been rejected.', [$categoryName]);
                        $templateVars['reason'] = $request->getStatusDescription();
                    } else {
                        $templateVars['msg'] =__('Your categories request for %1 has been rejected.', [$categoryName]);
                    }
                    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    $this->_sendNotification($vendor->getEmail(), $template, $templateVars, $storeId);
                }
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }

    protected function _sendNotification(
        $email,
        $template,
        $templateVars,
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID
    ) {
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
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::save_category_request');
    }
}
