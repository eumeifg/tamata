<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Controller\Adminhtml\Manage;

use Magento\Framework\Exception\LocalizedException;
use Magedelight\Vendor\Controller\RegistryConstants;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magedelight\Vendor\Model\Source\RequestStatuses;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magedelight\Vendor\Controller\Adminhtml\Index
{

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     */
    protected $vendorWebsite;

    /**
     * @var \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface
     */
    protected $vendorWebsiteRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param \Magedelight\Vendor\Api\AccountManagementInterface $vendorAccountManagement
     * @param \Magedelight\Vendor\Api\Data\VendorInterfaceFactory $vendorDataFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magedelight\Vendor\Helper\View $viewHelper
     * @param \Magedelight\Vendor\Model\Vendor\Image $imageModel
     * @param \Magedelight\Vendor\Model\Vendor\File $fileModel
     * @param \Magedelight\Vendor\Model\Upload $uploadModel
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magedelight\Catalog\Model\Product $vendorProduct
     * @param \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite
     * @param \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Helper\Data $directoryHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magedelight\Vendor\Api\AccountManagementInterface $vendorAccountManagement,
        \Magedelight\Vendor\Api\Data\VendorInterfaceFactory $vendorDataFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magedelight\Vendor\Helper\View $viewHelper,
        \Magedelight\Vendor\Model\Vendor\Image $imageModel,
        \Magedelight\Vendor\Model\Vendor\File $fileModel,
        \Magedelight\Vendor\Model\Upload $uploadModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Catalog\Model\Product $vendorProduct,
        \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite,
        \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Helper\Data $directoryHelper
    ) {
        $this->vendorWebsite = $vendorWebsite;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        $this->storeManager = $storeManager;
        $this->directoryHelper = $directoryHelper;
        parent::__construct(
            $context,
            $coreRegistry,
            $vendorFactory,
            $vendorRepository,
            $vendorAccountManagement,
            $vendorDataFactory,
            $layoutFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resultForwardFactory,
            $dataObjectHelper,
            $resultJsonFactory,
            $viewHelper,
            $imageModel,
            $fileModel,
            $uploadModel,
            $localeDate,
            $date,
            $vendorProduct
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_User::edit_vendor_user');
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $errors = [];
        
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();

        $vendorId = isset($originalRequestData['vendor']['vendor_id']) ? $originalRequestData['vendor']['vendor_id'] : null;
        $oldStatus = VendorStatus::VENDOR_STATUS_PENDING;
        $isExistingVendor = (bool)$vendorId;
        $vendorOb = $this->_vendorRepository->getById($vendorId);

        if ($originalRequestData) {
            try {
                $this->validateVendorInfo($originalRequestData, $vendorId, $vendorOb, $resultRedirect);
                $vendor = $this->vendorDataFactory->create();
                $request = $this->getRequest();

                if ($isExistingVendor) {
                    $oldStatus = $vendorOb->getStatus();
                    
                    /* Avoid overriding the existing email. */
                    $originalRequestData['vendor']['email'] = trim($vendorOb->getEmail());
                    /* Avoid overriding the existing email. */
                    $vendor->setId($vendorId);
                }

                if (!$this->validate($originalRequestData)) {
                    $message = __('Make sure Vacation From Date and To Date are not less than current date.');
                    $this->_getSession()->setVendorData($originalRequestData);
                    $eavExc = new \Magento\Framework\Validator\Exception($message);
                    throw $eavExc;
                    return;
                } else {
                    if ('BiggerDate' === $this->validate($originalRequestData)) {
                        if ($originalRequestData['vendor']['status'] == VendorStatus::VENDOR_STATUS_VACATION_MODE) {
                            $vendor->setData('vacation_request_status', 1);
                        }
                        $originalRequestData['vendor']['status'] = $vendorOb->getStatus();
                    }
                }

                /** Send email to vendor after change a status */
                $sendStatusInformationEmail = $this->getStatusEmailFlag($originalRequestData['vendor']['status']);

                $this->_eventManager->dispatch(
                    'adminhtml_vendor_prepare_save',
                    ['vendor' => $vendor, 'request' => $request]
                );

                // Save vendor
                if ($isExistingVendor) {
                    foreach ($this->getFieldsForExistingVendor() as $field) {
                        if (array_key_exists($field, $originalRequestData['vendor'])) {
                            if ($field == 'mobile') {
                                $vendor->setData($field, '+'.$originalRequestData['vendor']['country_code'].$originalRequestData['vendor'][$field]);
                            } else {
                                $vendor->setData($field, $originalRequestData['vendor'][$field]);
                            }
                        }
                    }
                    $websiteId = (array_key_exists('website_id', $originalRequestData['vendor'])) ? $originalRequestData['vendor']['website_id'] : null;
                    $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData($vendorId, $websiteId);
                    if ($vendorWebsite && $vendorWebsite->getId()) {
                        $excludeWebsiteFields = $this->getExcludedFields();
                        foreach ($originalRequestData['vendor'] as $key => $vendorDataItem) {
                            if (in_array($key, $excludeWebsiteFields)) {
                                continue;
                            }
                            $vendorWebsite->setData($key, $vendorDataItem);
                        }
                        $vendorWebsite->setData('email_verified', 1);
                        $vendorWebsite->save();
                    }
                    $this->_vendorRepository->save($vendor);
                    $this->_eventManager->dispatch(
                        'adminhtml_vendor_save_after',
                        ['vendor' => $vendor, 'request' => $request,'old_status' => $oldStatus,'is_regn_new' => false]
                    );
                    $this->_getSession()->unsVendorData();
                    // Done Saving vendor, finish save action
                    $this->_coreRegistry->register(RegistryConstants::CURRENT_VENDOR_ID, $vendorId);
                    $this->messageManager->addSuccess(__('You saved the User.'));
                }

                // Reindex product data
                if ($vendor->getStatus() == VendorStatus::VENDOR_STATUS_ACTIVE && $oldStatus != VendorStatus::VENDOR_STATUS_ACTIVE) {
                    $vendorProducts = $this->vendorProduct->getVendorProductsById($vendor->getId());
                    $marketplaceProductIds = $vendorProducts->getColumnValues("marketplace_product_id");
                    $vendorProductIds = $vendorProducts->getColumnValues("vendor_product_id");
                    $parentIds = $vendorProducts->getColumnValues("parent_id");
                    $eventParams = [
                        'marketplace_product_ids' => array_unique(array_filter($marketplaceProductIds)), /* Used for indexing.*/
                        'parent_ids' => array_unique(array_filter($parentIds)) /* Used for indexing.*/
                    ];
                    $this->_eventManager->dispatch('vendor_product_mass_list_after', $eventParams);
                } elseif ($vendor->getStatus() != VendorStatus::VENDOR_STATUS_ACTIVE && $oldStatus == VendorStatus::VENDOR_STATUS_ACTIVE) {
                    $vendorProducts = $this->vendorProduct->getVendorProductsById($vendor->getId());
                    $marketplaceProductIds = $vendorProducts->getColumnValues("marketplace_product_id");
                    $parentIds = $vendorProducts->getColumnValues("parent_id");
                    $eventParams = [
                        'marketplace_product_ids' => array_unique(array_filter($marketplaceProductIds)), /* Used for indexing.*/
                        'parent_ids' => array_unique(array_filter($parentIds)) /* Used for indexing.*/
                    ];
                    $this->_eventManager->dispatch('vendor_product_mass_unlist_after', $eventParams);
                }

                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setVendorData($originalRequestData);
                $returnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $this->_getSession()->setVendorData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('Something went wrong while saving the vendor.'));
                $this->_getSession()->setVendorData($originalRequestData);
                $returnToEdit = true;
            }
        }

        if ($returnToEdit) {
            return $this->pageRedirect($vendorId);
        } else {
            return $resultRedirect->setPath('*/manage/index');
        }
        return $resultRedirect;
    }
    
    /**
     * @param integer $vendorId
     */
    protected function pageRedirect($vendorId = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($vendorId) {
            $resultRedirect->setPath(
                'vendor/manage/edit',
                ['vendor_id' => $vendorId, '_current' => false]
            );
        } else {
            $resultRedirect->setPath(
                'vendor/*/new',
                ['_current' => true]
            );
        }
        return $resultRedirect;
    }

    /**
     * Validate Vacation From date & To date.
     * @param type $originalRequestData
     * @return boolean
     */
    protected function validate($originalRequestData)
    {
        if ($originalRequestData['vendor']['status'] == VendorStatus::VENDOR_STATUS_VACATION_MODE) {
            $fromDate = $originalRequestData['vendor']['vacation_from_date'];
            $toDate = $originalRequestData['vendor']['vacation_to_date'];
            $date = $this->date;
            $currentDate = $date->timestamp($date->date('m/d/Y'));// get current date timestamp
            $fromDate = $date->timestamp($fromDate);
            $toDate = $date->timestamp($toDate);
            if (($fromDate <= $currentDate) && ($toDate >= $currentDate) && ($fromDate <= $toDate)) {
                return true;
            } elseif (($fromDate > $currentDate) && ($toDate >= $currentDate) && ($fromDate <= $toDate)) {
                return 'BiggerDate';
            }
            return false;
        }

        return true;
    }

    /**
     * @param type $status
     * @return boolean
     */
    protected function getStatusEmailFlag($status)
    {
        if ($status == VendorStatus::VENDOR_STATUS_DISAPPROVED
            or $status == VendorStatus::VENDOR_STATUS_INACTIVE
        ) {
            return true;
        }
        return false;
    }

    protected function getEmailVarify($email)
    {
        $collection = $this->vendorFactory->create()->getCollection();
        $collection->addFieldToFilter('email', ['eq'=>$email]);
        if ($collection->count()) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return array
     */
    protected function getFieldsForExistingVendor()
    {
        return [
            'vendor_id',
            'mobile',
            'email',
            'website_id',
            'status',
        ];
    }
    
    /**
     * Exlclude field on vendor save.
     *
     * @param string $event
     * @return array
     */
    protected function getExcludedFields($event = 'update')
    {
        $fields = [];
        if ($event === 'update') {
            $fields = [
                'categories_ids',
                'logo',
                'vat_doc',
                'website_ids'
                ];
        }
        return $fields;
    }
    
    /**
     * @param array $originalRequestData
     * @param integer $vendorId
     * @param type $vendorOb
     * @param type $resultRedirect
     */
    public function validateVendorInfo($originalRequestData, $vendorId, $vendorOb, $resultRedirect)
    {
        if (!$vendorId) {
            if (isset($originalRequestData['vendor']['email']) && !empty($originalRequestData['vendor']['email'])) {
                if ($this->getEmailVarify($originalRequestData['vendor']['email'])) {
                    $this->messageManager->addWarning('Email is already in used please use another one.');

                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }
        }

        if (isset($originalRequestData['vendor']['name']) && empty($originalRequestData['vendor']['name'])) {
            $this->messageManager->addWarning('Please enter Name');
            $this->_getSession()->setVendorData($originalRequestData);
            return $resultRedirect->setPath('*/*/new');
        } else {
            $regex = "/^[a-zA-Z ]{1,150}$/";
            if (preg_match($regex, $originalRequestData['vendor']['name'])) {
            } else {
                $this->messageManager->addWarning('Allow alphabatic and space value, character limit 150, not allowed special character for Name.');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            }
        }

        if (isset($originalRequestData['vendor']['email']) && empty($originalRequestData['vendor']['email'])) {
            $this->messageManager->addWarning('Please enter Email Address');
            $this->_getSession()->setVendorData($originalRequestData);
            return $resultRedirect->setPath('*/*/new');
        } else {
            $regex = "/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,100}$/i";
            if (preg_match($regex, $originalRequestData['vendor']['email'])) {
            } else {
                $this->messageManager->addWarning('Please Enter Valid Email.');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            }
        }
        
        if ($vendorId && !$vendorOb->getIsUser()) {
            
            if (isset($originalRequestData['vendor']['email']) && !empty($originalRequestData['vendor']['email'])) {
                if ($vendorOb->checkEmailExist($originalRequestData['vendor']) != true) {
                    $this->messageManager->addWarning('Email is already in used please use another one.');

                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }

            if (isset($originalRequestData['vendor']['mobile']) && empty($originalRequestData['vendor']['mobile'])) {
                $this->messageManager->addWarning('Please enter Mobile Number.');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            }
        }
    }
}
