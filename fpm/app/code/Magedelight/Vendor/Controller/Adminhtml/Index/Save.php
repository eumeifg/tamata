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
namespace Magedelight\Vendor\Controller\Adminhtml\Index;

use Magedelight\Vendor\Controller\RegistryConstants;
use Magedelight\Vendor\Model\Source\RequestStatuses;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magento\Framework\Exception\LocalizedException;

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
        return $this->_authorization->isAllowed('Magedelight_Vendor::edit_vendor');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $errors = [];

        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();

        if (array_key_exists('category', $originalRequestData)) {
            $originalRequestData['vendor']['categories_ids'] = $originalRequestData['category'];
            unset($originalRequestData['category']);
        }

        $vendorId = isset($originalRequestData['vendor']['vendor_id']) ?
            $originalRequestData['vendor']['vendor_id'] : null;
        $oldStatus = VendorStatus::VENDOR_STATUS_PENDING;
        $isExistingVendor = (bool)$vendorId;
        $vendorOb = $this->_vendorRepository->getById($vendorId);

        if ($originalRequestData) {
            try {
                $this->validateVendorInfo($originalRequestData, $vendorId, $vendorOb, $resultRedirect);
                $this->validateBusinessDetails($originalRequestData, $vendorId, $vendorOb, $resultRedirect);
                $this->validateShippingDetails($originalRequestData, $vendorId, $vendorOb, $resultRedirect);
                if ($isExistingVendor && !$vendorOb->getIsUser()) {
                    /* Validate vendor categories.*/
                    if (empty($originalRequestData['vendor']['categories_ids'])) {
                        $this->messageManager->addWarning(
                            'Please select at least one or more categories from the list.'
                        );
                        $this->_getSession()->setVendorData($originalRequestData);
                        return $this->pageRedirect($vendorId);
                    }
                    /* Validate vendor categories.*/

                    $errors = $this->vendorAccountManagement->validateBankingDetails(
                        $originalRequestData['vendor'],
                        $errors,
                        false,
                        false,
                        true
                    );

                    if (count($errors) > 0) {
                        $this->messageManager->addWarning(implode(', ', $errors));
                        $this->_getSession()->setVendorData($originalRequestData);
                        return $this->pageRedirect($vendorId);
                    }
                }

                $vendor = $this->vendorDataFactory->create();
                $request = $this->getRequest();

                if ($isExistingVendor) {
                    $oldStatus = $vendorOb->getStatus();
                    /* Avoid overriding the existing email. */
                    /*$originalRequestData['vendor']['email'] = trim($vendorOb->getEmail());*/
                    /* commented 'coz was creating issue while updating existing vendor's email id */
                    /* Avoid overriding the existing email. */
                    $vendor->setId($vendorId);
                }

                if (!$this->validate($originalRequestData)) {
                    $message = __('Make sure Vacation From Date and To Date are not less than current date.');
                    $this->_getSession()->setVendorData($originalRequestData);
                    $eavExc = new \Magento\Framework\Validator\Exception($message);
                    throw $eavExc;
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

                if (array_key_exists('categories_ids', $originalRequestData['vendor'])) {
                    $vendor->setData('categories_ids', $originalRequestData['vendor']['categories_ids']);
                } else {
                    $vendor->setData('categories_ids', []);
                }

                $this->_eventManager->dispatch(
                    'adminhtml_vendor_prepare_save',
                    ['vendor' => $vendor, 'request' => $request]
                );

                // Save vendor
                if ($isExistingVendor) {
                    foreach ($this->getFieldsForExistingVendor() as $field) {
                        if (array_key_exists($field, $originalRequestData['vendor'])) {
                            if ($field == 'mobile') {
                                $vendor->setData($field, '+' . $originalRequestData['vendor']['country_code']
                                    . $originalRequestData['vendor'][$field]);
                            } else {
                                $vendor->setData($field, $originalRequestData['vendor'][$field]);
                            }
                        }
                    }
                    $websiteId = (array_key_exists('website_id', $originalRequestData['vendor'])) ?
                        $originalRequestData['vendor']['website_id'] : null;
                    $vendorWebsite = $this->vendorWebsiteRepository->getVendorWebsiteData($vendorId, $websiteId);
                    if ($vendorWebsite && $vendorWebsite->getId()) {
                        $excludeWebsiteFields = $this->getExcludedFields();
                        foreach ($originalRequestData['vendor'] as $key => $vendorDataItem) {
                            if (in_array($key, $excludeWebsiteFields)) {
                                continue;
                            }
                            $vendorWebsite->setData($key, $vendorDataItem);
                        }
                        $this->processFiles($originalRequestData, $vendorWebsite);
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
                    $this->messageManager->addSuccess(__('You saved the vendor.'));
                }

                $this->createNewVendorsIfNotExists($originalRequestData['vendor']);

                // Reindex product data
                if ($vendor->getStatus() == VendorStatus::VENDOR_STATUS_ACTIVE &&
                    $oldStatus != VendorStatus::VENDOR_STATUS_ACTIVE) {
                    $vendorProducts = $this->vendorProduct->getVendorProductsById($vendor->getId());
                    $marketplaceProductIds = $vendorProducts->getColumnValues("marketplace_product_id");
                    $vendorProductIds = $vendorProducts->getColumnValues("vendor_product_id");
                    $parentIds = $vendorProducts->getColumnValues("parent_id");
                    $eventParams = [
                        'marketplace_product_ids' => array_unique(array_filter($marketplaceProductIds)),
                        /* Used for indexing.*/
                        'parent_ids' => array_unique(array_filter($parentIds)) /* Used for indexing.*/
                    ];
                    $this->_eventManager->dispatch('vendor_product_mass_list_after', $eventParams);
                } elseif ($vendor->getStatus() != VendorStatus::VENDOR_STATUS_ACTIVE &&
                    $oldStatus == VendorStatus::VENDOR_STATUS_ACTIVE) {
                    $vendorProducts = $this->vendorProduct->getVendorProductsById($vendor->getId());
                    $marketplaceProductIds = $vendorProducts->getColumnValues("marketplace_product_id");
                    $parentIds = $vendorProducts->getColumnValues("parent_id");
                    $eventParams = [
                        'marketplace_product_ids' => array_unique(array_filter($marketplaceProductIds)),
                        /* Used for indexing.*/
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
            switch ($oldStatus) {
                case VendorStatus::VENDOR_STATUS_DISAPPROVED:
                    $resultRedirect->setPath('*/*/rejected');
                    break;
                case VendorStatus::VENDOR_STATUS_PENDING:
                    $resultRedirect->setPath('*/*');
                    break;
                default:
                    $resultRedirect->setPath('*/*/approved');
                    break;
            }
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
                'vendor/*/edit',
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
            /*if ($fromDate > $toDate || $fromDate < $currentDate || $fromDate == $toDate) {
            if ($fromDate > $toDate || $fromDate < $currentDate) {*/
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
            || $status == VendorStatus::VENDOR_STATUS_INACTIVE
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param $email
     * @return bool
     */
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
     * @param array $postData
     * @return void
     */
    protected function createNewVendorsIfNotExists($postData)
    {
        $originalRequestData = $this->getRequest()->getPostValue();
        $vendorId = isset($originalRequestData['vendor']['vendor_id']) ?
            $originalRequestData['vendor']['vendor_id'] : null;
        $isExistingVendor = (bool)$vendorId;
        /* Register vendor for given website if not registered already. */
        if (array_key_exists('website_ids', $postData)) {
            foreach ($postData['website_ids'] as $websiteId) {
                $postData['website_id'] = $websiteId;
                if ($isExistingVendor) {
                    $postData['status'] = RequestStatuses::VENDOR_REQUEST_STATUS_PENDING;
                }
                $vendor = $this->vendorAccountManagement->createVendorAccountFromAdmin($postData);
                if ($vendor && $vendor->getId()) {
                    $vendorId = $vendor->getId();
                    $this->messageManager->addSuccess(
                        __(
                            'Vendor has been created for website %1.',
                            $this->storeManager->getWebsite($websiteId)->getName()
                        )
                    );
                    $this->_eventManager->dispatch(
                        'adminhtml_vendor_save_after',
                        [
                            'vendor' => $vendor,
                            'request' => $this->getRequest(),
                            'old_status' => null,
                            'is_regn_new' => true
                        ]
                    );
                }
            }
        }
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
     *
     * @param $originalRequestData
     * @param $vendorWebsite
     * @return void
     * @throws \Magento\Framework\Validator\Exception
     */
    protected function processFiles($originalRequestData, $vendorWebsite)
    {
        if (isset($originalRequestData['vendor']['logo']) && is_array($originalRequestData['vendor']['logo'])) {
            if (!empty($originalRequestData['vendor']['logo']['delete'])) {
                $this->vendorAccountManagement->deleteFile($vendorWebsite->getLogo(), 'vendor/logo');
                $vendorWebsite->setLogo('');
            }
        }

        if (isset($originalRequestData['vendor']['vat_doc']) && is_array($originalRequestData['vendor']['vat_doc'])) {
            if (!empty($originalRequestData['vendor']['vat_doc']['delete'])) {
                $this->vendorAccountManagement->deleteFile($vendorWebsite->getVatDoc(), 'vendor/vat_doc');
                $vendorWebsite->setVatDoc('');
            }
        }

        $files = $this->getRequest()->getFiles()->toArray();
        if (sizeof($files) > 0) {
            if (!isset($originalRequestData['vendor']['vendor_id'])) {
                if (isset($files['vat_doc']) && $files['vat_doc']['size'] < 1) {
                    $message = __('VAT Document can not be empty.');
                    $this->_getSession()->setVendorData($originalRequestData);
                    $eavExc = new \Magento\Framework\Validator\Exception($message);
                    throw $eavExc;
                }
            }

            if (!empty($files['logo']['tmp_name']) &&
                ($files['logo']['type'] == 'image/gif' ||
                    $files['logo']['size'] > '524288')) {
                $message = __('File type must be jpg/jpeg/png and size less than 512KB.');
                $this->_getSession()->setVendorData($originalRequestData);
                $eavExc = new \Magento\Framework\Validator\Exception($message);
                throw $eavExc;
            }

            if (!empty($files['vat_doc']['tmp_name']) &&
                ($files['vat_doc']['type'] == 'image/gif' ||
                    $files['vat_doc']['size'] > '524288')) {
                $message = __('File type must be jpg/jpeg/png and size less than 512KB.');
                $this->_getSession()->setVendorData($originalRequestData);
                $eavExc = new \Magento\Framework\Validator\Exception($message);
                throw $eavExc;
            }

            if (!empty($files['logo']['tmp_name'])) {
                try {
                    if ($logo = $this->uploadFile($originalRequestData['vendor'], 'logo', $vendorWebsite)) {
                        $vendorWebsite->setLogo($logo);
                    }
                } catch (\Exception $e) {
                    $this->_getSession()->setVendorData($originalRequestData);
                    $eavExc = new \Magento\Framework\Validator\Exception(__($e->getMessage()));
                    throw $eavExc;
                }
            }

            $this->uploadFile($originalRequestData['vendor'], 'vat_doc', $vendorWebsite);
        }
    }

    /**
     * Upload file.
     * @param array $postData
     */
    protected function uploadFile($postData = [], $field = '', $vendorWebsite, $fileTypes = [])
    {
        $files = $this->getRequest()->getFiles()->toArray();
        if (array_key_exists($field, $files) && !empty($files[$field]['tmp_name'])) {
            $uploadedFile = '';
            $uploadedFile = $this->uploadModel->uploadFileAndGetName(
                $field,
                $this->imageModel->getBaseDir('vendor/' . $field),
                $postData,
                $fileTypes
            );
            $vendorWebsite->setData($field, $uploadedFile);
            /* $this->deleteIfFileExists($postData, $field); */
        }
    }

    /**
     * Delete existing file on updating file.
     * @param array $postData
     */
    protected function deleteIfFileExists($postData = [], $field = '')
    {
        if ($field && !empty(($postData[$field]['value']))) {
            $postData[$field]['delete'] = 1;
            $this->uploadModel->uploadFileAndGetName($field, $this->imageModel->getBaseDir(), $postData);
        }
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
     * @return
     */
    public function validateVendorInfo($originalRequestData, $vendorId, $vendorOb, $resultRedirect)
    {
        $msg = '';
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
            if (!(preg_match($regex, $originalRequestData['vendor']['name']))) {
                $msg = 'Allow alphabatic and space value, character limit 150, not allowed special character for Name.';
                $this->messageManager->addWarning($msg);
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
            if (!(preg_match($regex, $originalRequestData['vendor']['email']))) {
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

            if (isset($originalRequestData['vendor']['address1']) &&
                empty($originalRequestData['vendor']['address1'])) {
                $this->messageManager->addWarning('Please enter address line 1');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            } else {
                $regex = "/^.{1,150}$/";
                if (!(preg_match($regex, $originalRequestData['vendor']['address1']))) {
                    $this->messageManager->addWarning('Character limit 150 for Address Line 1.');
                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }

            if (isset($originalRequestData['vendor']['address2']) &&
                !empty($originalRequestData['vendor']['address2'])) {
                $regex = "/^.{0,150}$/";
                if (!(preg_match($regex, $originalRequestData['vendor']['address2']))) {
                    $this->messageManager->addWarning('Character limit 150 for Address Line 2.');
                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }

            if (isset($originalRequestData['vendor']['country_id']) &&
                empty($originalRequestData['vendor']['country_id'])) {
                $this->messageManager->addWarning('Please select country');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            }

            if ($this->directoryHelper->isRegionRequired($originalRequestData['vendor']['country_id'])) {
                $regionExists = true;
                if ((array_key_exists('region_id', $originalRequestData['vendor']) &&
                    empty($originalRequestData['vendor']['region_id']))) {
                    $regionExists = false;
                }
                if (!$regionExists &&
                    (array_key_exists('region', $originalRequestData['vendor']) &&
                        empty($originalRequestData['vendor']['region']))) {
                    $regionExists = false;
                } else {
                    $regionExists = true;
                }
                if (!$regionExists) {
                    $this->messageManager->addWarning('Please select state.');
                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }

            if (isset($originalRequestData['vendor']['city']) && empty($originalRequestData['vendor']['city'])) {
                $this->messageManager->addWarning('Please enter city name.');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            } else {
                $regex = "/^[a-zA-Z ]{1,50}$/";
                if (!(preg_match($regex, $originalRequestData['vendor']['city']))) {
                    $msg = 'City filed allow only alphabate with space value, character limit 50, ';
                    $msg .= 'Not allowed special and numeric value.';
                    $this->messageManager->addWarning($msg);
                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }

            if (isset($originalRequestData['vendor']['pincode']) && empty($originalRequestData['vendor']['pincode'])) {
                $this->messageManager->addWarning('Please enter pincode');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            }
        }
    }

    /**
     * @param array $originalRequestData
     * @param integer $vendorId
     * @param type $vendorOb
     * @param type $resultRedirect
     * @return
     */
    public function validateBusinessDetails($originalRequestData, $vendorId, $vendorOb, $resultRedirect)
    {
        if ($vendorId && !$vendorOb->getIsUser()) {
            if (isset($originalRequestData['vendor']['business_name']) &&
                empty($originalRequestData['vendor']['business_name'])) {
                $this->messageManager->addWarning('Please enter business name.');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            } else {
                $regex = "/^.{1,150}$/";
                if (!(preg_match($regex, $originalRequestData['vendor']['business_name']))) {
                    $this->messageManager->addWarning('Character limit 150 for business name.');
                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }
        }
    }

    /**
     * @param array $originalRequestData
     * @param integer $vendorId
     * @param type $vendorOb
     * @param type $resultRedirect
     * @return
     */
    public function validateShippingDetails($originalRequestData, $vendorId, $vendorOb, $resultRedirect)
    {
        $msg = '';
        if ($vendorId && !$vendorOb->getIsUser()) {
            if (isset($originalRequestData['vendor']['pickup_address1']) &&
                empty($originalRequestData['vendor']['pickup_address1'])) {
                $this->messageManager->addWarning(
                    'Please enter value for address line 1 for Pickup and Shipping Information.'
                );
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            }

            if (isset($originalRequestData['vendor']['pickup_address2']) &&
                !empty($originalRequestData['vendor']['pickup_address2'])) {
                $regex = "/^.{0,150}$/";
                if (!(preg_match($regex, $originalRequestData['vendor']['pickup_address2']))) {
                    $this->messageManager->addWarning(
                        'Character limit 150 for Address Line 2 for Pickup and Shipping Information.'
                    );
                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }

            if (isset($originalRequestData['vendor']['pickup_country_id']) &&
                empty($originalRequestData['vendor']['pickup_country_id'])) {
                $this->messageManager->addWarning('Please select country for Pickup and Shipping Information.');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            }

            if ($this->directoryHelper->isRegionRequired($originalRequestData['vendor']['pickup_country_id'])) {
                $regionExists = true;
                if ((array_key_exists('pickup_region_id', $originalRequestData['vendor']) &&
                    empty($originalRequestData['vendor']['pickup_region_id']))) {
                    $regionExists = false;
                }
                if (!$regionExists && (array_key_exists('pickup_region', $originalRequestData['vendor']) &&
                        empty($originalRequestData['vendor']['pickup_region']))) {
                    $regionExists = false;
                } else {
                    $regionExists = true;
                }
                if (!$regionExists) {
                    $this->messageManager->addWarning('Please select state for Pickup and Shipping Information.');
                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }

            if (isset($originalRequestData['vendor']['pickup_city']) &&
                empty($originalRequestData['vendor']['pickup_city'])) {
                $this->messageManager->addWarning('Please enter city for Pickup and Shipping Information.');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            } else {
                $regex = "/^[a-zA-Z ]{1,50}$/";
                if (!(preg_match($regex, $originalRequestData['vendor']['pickup_city']))) {
                    $msg = 'City filed allow only alphabate with space value, character limit 50, ';
                    $msg .= 'Not allowed special and numeric value for Pickup and Shipping Information.';
                    $this->messageManager->addWarning($msg);
                    $this->_getSession()->setVendorData($originalRequestData);
                    return $resultRedirect->setPath('*/*/new');
                }
            }

            if (isset($originalRequestData['vendor']['pickup_pincode']) &&
                empty($originalRequestData['vendor']['pickup_pincode'])) {
                $this->messageManager->addWarning('Please enter pincode for Pickup and Shipping Information.');
                $this->_getSession()->setVendorData($originalRequestData);
                return $resultRedirect->setPath('*/*/new');
            }
        }
    }
}
