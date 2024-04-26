<?php

namespace MDC\Vendor\Controller\Adminhtml\Index;

use Magedelight\Vendor\Controller\RegistryConstants;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magedelight\Vendor\Controller\Adminhtml\Index\Save
{
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
        parent::__construct($context, $coreRegistry, $vendorFactory, $vendorRepository, $vendorAccountManagement, $vendorDataFactory, $layoutFactory, $resultLayoutFactory, $resultPageFactory, $resultForwardFactory, $dataObjectHelper, $resultJsonFactory, $viewHelper, $imageModel, $fileModel, $uploadModel, $localeDate, $date, $vendorProduct, $vendorWebsite, $vendorWebsiteRepository, $storeManager, $directoryHelper);
    }

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
                                $mobileData = "+".$originalRequestData['vendor']['country_code']
                                    . str_replace(' ', '', $originalRequestData['vendor'][$field]);
                                $vendor->setData($field, $mobileData);
                            } elseif($field == 'email' && $originalRequestData['vendor']['email_id'] !== $originalRequestData['vendor']['email']) {
                                $vendor->setData($field, $originalRequestData['vendor']['email_id']);
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
                $originalRequestData['vendor']['email'] = $originalRequestData['vendor']['email_id'];
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
}
