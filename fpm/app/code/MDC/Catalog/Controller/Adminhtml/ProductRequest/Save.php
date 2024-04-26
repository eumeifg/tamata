<?php

namespace MDC\Catalog\Controller\Adminhtml\ProductRequest;

use Magedelight\Catalog\Controller\Adminhtml\ProductRequest\Save as MagedelightNewProductRequestSave;
use Magedelight\Catalog\Model\ProductRequest;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestStore\CollectionFactory as ProductRequestStoreCollectionFactory;
use Magedelight\Catalog\Model\ResourceModel\ProductRequestWebsite\CollectionFactory as ProductRequestWebsiteCollectionFactory;

class Save extends MagedelightNewProductRequestSave
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

    protected $jsonEncoder;

    protected $jsonDecoder;

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
        \Magento\Framework\Registry $registry,
       ProductRequestStoreCollectionFactory $productRequestStoreCollectionFactory,
       ProductRequestWebsiteCollectionFactory $productRequestWebsiteCollectionFactory,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Json\Decoder $jsonDecoder
    ) {

        parent::__construct($context,$productRequestFactory,$rejectProductRequest,$storeManager,$helper,$registry);

         $this->productRequestStoreCollectionFactory = $productRequestStoreCollectionFactory;
         $this->productRequestWebsiteCollectionFactory = $productRequestWebsiteCollectionFactory;
         $this->jsonEncoder = $jsonEncoder;
         $this->jsonDecoder = $jsonDecoder;
    }

    public function execute()
    {
        $oldUrl = str_replace("/","-",$this->_redirect->getRefererUrl());
        if (preg_match('/existing-(.*?)-/', $oldUrl, $display) === 1) {
            $oldExisting = $display[1];
        }
        if (preg_match('/status-(.*?)-/', $oldUrl, $display) === 1) {
            $oldStatus = $display[1];
        }
        $data = $this->getRequest()->getParam('product');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $storeId = $data['store_id'];
        if ($data) {
            $websiteId = (array_key_exists('website_id', $data)) ? $data['website_id'] : $this->storeManager->getDefaultStoreView()->getWebsiteId();

            $id = $data['product_request_id'];
            // if (isset($data['special_price']) && (($data['special_price'] === null) || $data['special_price'] == '')) {
            //     unset($data['special_price']);
            // }
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
                foreach ($data as $column=> $value) {
                    // $value = (array_key_exists($column, $data)) ? $data[$column] : '';
                    if (!($value === null) && $value != '') {
                        $this->productRequest->setData($column, $value);
                    }
                }

                if(isset($data['media_gallery']['images'])){
                    foreach ($data['media_gallery']['images'] as $imageKey => $imageValue) {
                        foreach ($imageValue as $key => $value) {
                            $data['media_gallery']['images'][$imageKey][$key] = trim(str_replace('.tmp', '', $value));
                        }
                    }
                    $galleryImages = json_encode($data['media_gallery']['images']);
                    $baseImage = json_encode($data['image']);

                    $this->productRequest->setData("images",$galleryImages);
                    $this->productRequest->setData("base_image",$baseImage);
                    $this->productRequest->setData("thumbnail",$baseImage);
                    $this->productRequest->setData("small_image",$baseImage);
                }

                if(isset( $data['swatch_image'])){
                    $data['swatch_image'] = trim(
                        str_replace('.tmp', '', $data['swatch_image'])
                    );
                }
                if(isset( $data['image'])){
                    $data['image'] = trim(
                        str_replace('.tmp', '', $data['image'])
                    );
                }
                if(isset( $data['thumbnail'])){
                    $data['thumbnail'] = trim(
                        str_replace('.tmp', '', $data['thumbnail'])
                    );
                }
                 if(isset( $data['small_image'])){
                    $data['small_image'] = trim(
                        str_replace('.tmp', '', $data['small_image'])
                    );
                }

                /* Gallery */
                $this->productRequest->setId($id);
                $this->registry->register('vendor_product_request', $this->productRequest);
            } else {
                $this->messageManager->addError(__('Product request does not exist.'));
                return $resultRedirect->setPath('*/*/');
            }
            try {
                $this->productRequest->save();
                /*SAVE request product data in request store view wise */
                $storeIds = array_keys($this->storeManager->getStores());
                if($storeId === "0"){
                    array_push($storeIds, (int)$storeId);
                    $allStores = $storeIds;
                }else{
                    $allStores[] = $storeId;
                }
                foreach ($allStores as $key => $storeValue) {

                    $productRequestStore = $this->productRequestStoreCollectionFactory->create()->addFieldToFilter('product_request_id', $id)->addFieldToFilter('store_id', $storeValue)->getFirstItem();
                    foreach ($data as $column=> $value) {
                        if (array_key_exists($column, $productRequestStore->getData())&& !($value === null) && $value != '' && $column != "store_id") {
                            $productRequestStore->setData($column, $value);
                        }
                        $decodeResquestAttrs = json_decode($productRequestStore->getData("attributes"),true);
                        if (!($decodeResquestAttrs === null) && array_key_exists($column, $decodeResquestAttrs) && $column != "store_id") {
                            $decodeResquestAttrs[$column] = $value;
                            $encodeResquestAttrs = json_encode($decodeResquestAttrs);
                            $productRequestStore->setData("attributes", $encodeResquestAttrs);
                        }
                    }
                    $productRequestStore->save();
                }
                $productRequestWebsite = $this->productRequestWebsiteCollectionFactory->create()->addFieldToFilter('product_request_id', $id)->getFirstItem();

                foreach ($data as $column=> $value) {
                    if (array_key_exists($column, $productRequestWebsite->getData()) ) {
                        $productRequestWebsite->setData($column, $value);
                        if (isset($data['special_price']) && (($data['special_price'] === null) || $data['special_price'] == '')) {
                            $productRequestWebsite->setData("special_price", NULL);
                        }
                    }
                }
                $productRequestWebsite->setData("cost_price_iqd", $data['cost_price_iqd']);
                $productRequestWebsite->setData("cost_price_usd", $data['cost_price_usd']);
                $productRequestWebsite->save();

                /*SAVE request product data in request store view wise */
                if ($status === ProductRequest::STATUS_PENDING) {
                    $this->getRequest()->setParam('prid', $id);
                    $this->getRequest()->setParam('store', $storeId);
                   return $resultRedirect->setPath('*/*/');

                } elseif ($status === ProductRequest::STATUS_DISAPPROVED) {
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


}
