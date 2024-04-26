<?php

namespace MDC\Vendor\Controller\Adminhtml\Categories\Request;

use Magedelight\Vendor\Model\Category\Request\Source\Status as RequestStatuses;
/**
 * 
 */
class Save extends \Magedelight\Vendor\Controller\Adminhtml\Categories\Request\Save
{
	
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

                    $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
                    $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
                     $categories = $categoryCollection->create();
                     $categories->addAttributeToSelect('*');

                    $allCategoryIds = [];
                       foreach ($categories as $category) {

                        $allCategoryIds [] = $category->getId();
                          
                       }
                    
                    $mergedVendorCategories = array_merge($existingCategories, $newCategories);
                    $finalVendorCategories = [];
                    foreach ($mergedVendorCategories as $key => $value) {
                        
                        if(in_array($value, $allCategoryIds)){
                            
                            $finalVendorCategories[$key] = $value;
                        }
                        
                    }
                   
                    // $vendorModel->setCategoriesIds(array_merge($existingCategories, $newCategories));
                    $vendorModel->setCategoriesIds($finalVendorCategories);
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
}