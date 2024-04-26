<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Controller\Sellerhtml\Rates;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    protected $_pricingHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $sessionForm;

    protected $shippingMatrixRates;

    /**
     *
     * @param Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Pricing\Helper\Data $helperData
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Backend\Model\Session $sessionForm
     * @param \Magedelight\Shippingmatrix\Model\Shippingmatrix $shippingMatrixRates
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Pricing\Helper\Data $helperData,
        \Magento\Directory\Helper\Data $directoryHelper,
        PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Model\Session $sessionForm,
        \Magedelight\Shippingmatrix\Model\Shippingmatrix $shippingMatrixRates,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_pricingHelper = $helperData;
        $this->directoryHelper = $directoryHelper;
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->sessionForm = $sessionForm;
        $this->_storeManager = $storeManager;
        $this->shippingMatrixRates = $shippingMatrixRates;
    }
        
    public function execute()
    {
        $flag = false;
        
        $data = $this->getRequest()->getPostValue();
        
        $block =  $this->layoutFactory->create()->createBlock('Magedelight\Shippingmatrix\Block\Sellerhtml\Rates\Rate');
        
        if ($data['dest_country_id'] === "0") {
            $data['country_name'] = 'All';
        } else {
            $data['country_name'] = $block->getCountryName($data['dest_country_id']);
        }
        
        if (isset($data['dest_region_id']) && $data['dest_region_id']!=0) {
            $data['region_name'] = $block->getRegionName($data['dest_region_id']);
        } else {
            $data['region_name'] = 'All';
        }
        
        $conditionName = $block->getShippingConditionName($data['condition_name']);

        $data['conditionName']=$conditionName['label']->getText();
        
        if (isset($data['category_id'])) {
            if ($data['category_id'] === "0") {
                $data['category_name']= 'All';
            } else {
                $categoryBlock =  $this->layoutFactory->create()->createBlock('RB\CategoryShipping\Block\Rates\Rate');
                $data['category_name'] = $categoryBlock->getCategoryName($data['category_id']);
            }
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {

            /** @var \Ktpl\BrandManagement\Model\Brand $model */
            $model = $this->shippingMatrixRates;

            if ($data['pk']=='') {
                $data['pk']=null;
            }

            if ($data['vendor_id'] == '') {
                $data['vendor_id'] = $this->_auth->getUser()->getVendorId();
            }
            $data['website_id'] =  $this->_storeManager->getStore()->getWebsiteId();
            $data['delivery_type'] = null;
            //$data['shipping_method'] = 'Freight';
            $model->setData($data);

            try {
                $model->save();
                $this->sessionForm->setFormData(false);
                $flag = true;
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $ratingData['error'] = __('This shipping rate already exists.');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Zend_Db_Statement_Exception $e) {
                if (isset($data['category_id'])) {
                    $this->messageManager->addException($e, __(
                        'Duplicate (Country "%1", City "%2", Category Id "%3" and Price "%4")',
                        $data['country_name'],
                        $data['dest_city'],
                        $data['category_name'],
                        $data['price']
                    ));
                } else {
                    $this->messageManager->addException($e, __(
                        'Duplicate (Country "%1", City "%2", and Price "%3")',
                        $data['country_name'],
                        $data['dest_city'],
                        $data['price']
                    ));
                }
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the matrix rates.'));
            }
        }
        
        if ($flag) {
            $ratingData = $model->getData();
            if ($conditionName['value'] == 'package_qty') {
                $ratingData['condition_from_value'] = $ratingData['condition_from_value'];
                $ratingData['condition_to_value'] = $ratingData['condition_to_value'];
            } elseif ($conditionName['value'] == 'package_value') {
                $ratingData['condition_from_value'] = $this->_pricingHelper->currency($ratingData['condition_from_value']);
                $ratingData['condition_to_value'] = $this->_pricingHelper->currency($ratingData['condition_to_value']);
            } elseif ($conditionName['value'] == 'package_weight') {
                $ratingData['condition_from_value'] = $ratingData['condition_from_value'];
                $ratingData['condition_to_value'] = $ratingData['condition_to_value'].'('.$this->directoryHelper->getWeightUnit().')';
            }
            $ratingData['price'] = $this->_pricingHelper->currency($ratingData['price']);
            $ratingData['delete_url'] = $block->getDeletePostActionUrl($ratingData['pk']);
            $ratingData['shipping_method'] = $block->getShippingMethodNameById($ratingData['shipping_method']);
            $ratingData['success'] = $flag;
            $ratingData['successMessage'] = __('Shipping rate has been successfully saved.');
        }
        
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($ratingData));
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::shippingmethod');
    }
}
