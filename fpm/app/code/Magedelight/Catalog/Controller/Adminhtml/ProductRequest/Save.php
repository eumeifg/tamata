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
namespace Magedelight\Catalog\Controller\Adminhtml\ProductRequest;

use Magedelight\Catalog\Model\ProductRequest;

class Save extends \Magedelight\Catalog\Controller\Adminhtml\ProductRequest
{

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\Reject
     */
    protected $rejectProductRequest;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $productRequest;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magedelight\Catalog\Model\Product\Request\Reject $rejectProductRequest
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Catalog\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magedelight\Catalog\Model\Product\Request\Reject $rejectProductRequest,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Catalog\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->rejectProductRequest = $rejectProductRequest;
        $this->productRequest = $productRequestFactory->create();
        $this->helper = $helper;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('product');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $storeId = $data['store_id'];
        if ($data) {
            $websiteId = (array_key_exists('website_id', $data)) ? $data['website_id'] :
                $this->storeManager->getDefaultStoreView()->getWebsiteId();

            $id = $data['product_request_id'];
            if (isset($data['special_price']) && (($data['special_price'] === null) || $data['special_price'] == '')) {
                unset($data['special_price']);
            }
            $status = (int) $data[ProductRequest::STATUS_PARAM_NAME];

            if ($status === ProductRequest::STATUS_APPROVED) {
                unset($data[ProductRequest::STATUS_PARAM_NAME]);
            }

            if ($id) {
                $this->productRequest->load($id);

                if ($this->helper->checkVendorSkuValidation() && !$this->productRequest->getIsRequestedForEdit()) {
                    $error = '';
                    if ($data['has_variants']) {
                        $error = '';
                    } else {
                        $error = $this->productRequest->validateUniqueVendorSku(
                            $data['vendor_id'],
                            $data['vendor_sku']
                        );
                    }
                    if (!empty($error)) {
                        $this->messageManager->addError($error);
                        return $resultRedirect->setPath('*/*/');
                    }
                }
                if (!empty($website_id)) {
                    $data['website_ids'] = implode(",", $website_id);
                }

                foreach ($this->getRequestPrimaryFields() as $column) {
                    $value = (array_key_exists($column, $data)) ? $data[$column] : '';
                    if (!($value === null) && $value != '') {
                        $this->productRequest->setData($column, $value);
                    }
                }
                $this->productRequest->setId($id);

                $this->registry->register('vendor_product_request', $this->productRequest);
            } else {
                $this->messageManager->addError(__('Product request does not exist.'));
                return $resultRedirect->setPath('*/*/');
            }
            try {
                $this->productRequest->save();
                if ($status === ProductRequest::STATUS_DISAPPROVED) {
                    $this->rejectProductRequest->execute($id, $this->getRequest()->getParam('product'));
                    $this->messageManager->addSuccess(__('Product request has been disapproved.'));
                    $params = [
                        'id' => $id,
                        '_current' => true,
                        ProductRequest::STATUS_PARAM_NAME => ProductRequest::STATUS_PENDING
                    ];
                    if ($data['existing']==1) {
                        $params['existing'] = 1;
                        return $resultRedirect->setPath('*/*/', $params);
                    }
                    return $resultRedirect->setPath('*/*/');
                } elseif ($status === ProductRequest::STATUS_APPROVED) {
                    $eventParams = [
                        'product_status' => 'approved',
                        'id' => $id,
                        'post_data' => $this->getRequest()->getParam('product')
                    ];
                    $this->_eventManager->dispatch('vendor_product_admin_status_change', $eventParams);
                    $this->getRequest()->setParam('prid', $id);
                    $this->getRequest()->setParam('store', $storeId);
                    $this->_forward('approve');
                    return;
                }
                $this->messageManager->addSuccess(__('Product request has been saved.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
                if ($this->getRequest()->getParam('existing', false)) {
                    return $resultRedirect->setPath('*/*/', ['existing' => 1]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addErrorMessage(__('Something went wrong while saving record.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id, 'store' => $storeId]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     *
     * @return array
     */
    protected function getRequestPrimaryFields()
    {
        return [
            'status',
            'disapprove_message',
            'has_variants',
            'vendor_sku',
            'qty'
        ];
    }
}
