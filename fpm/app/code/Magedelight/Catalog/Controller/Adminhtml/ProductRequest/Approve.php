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

use Magedelight\Catalog\Api\ProductRequestRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;

class Approve extends \Magedelight\Catalog\Controller\Adminhtml\ProductRequest
{

    /**
     * @var \Magento\Framework\Json\Encoder
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $_jsonDecoder;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_mathRandom;

    /**
     * @var ProductRequestRepositoryInterface
     */
    protected $_productRequestRepository;

    /**
     * @var Configurable
     */
    protected $_typeConfigurableModelFactory;

    /**
     * @var \Magedelight\Catalog\Model\Product\Request\Type\Simple
     */
    protected $_simpleProductRequest;

    /**
     * @var \Magedelight\Catalog\Model\VendorProduct\Type\Simple
     */
    protected $_simpleVendorProduct;

    /**
     * @var \Magedelight\Catalog\Model\Product\Type\Simple
     */
    protected $_simpleProduct;

    /**
     * @var \Magedelight\Catalog\Model\Product\Type\Configurable
     */
    protected $_configurableProduct;

    /**
     * @var \Magedelight\Backend\Log\LoggerInterface
     */
    protected $_logger;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param \Magento\Framework\Json\Encoder $jsonEncoder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param ProductRequestRepositoryInterface $productRequestRepository
     * @param Configurable $typeConfigurableModelFactory
     * @param \Magedelight\Catalog\Model\VendorProduct\Type\Simple $simpleVendorProduct
     * @param \Magedelight\Catalog\Model\Product\Type\Simple $simpleProduct
     * @param \Magedelight\Catalog\Model\Product\Type\Configurable $configurableProduct
     * @param \Magedelight\Catalog\Model\Product\Request\Type\Simple $simpleProductRequest
     * @param \Magedelight\Backend\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Math\Random $mathRandom,
        ProductRequestRepositoryInterface $productRequestRepository,
        Configurable $typeConfigurableModelFactory,
        \Magedelight\Catalog\Model\VendorProduct\Type\Simple $simpleVendorProduct,
        \Magedelight\Catalog\Model\Product\Type\Simple $simpleProduct,
        \Magedelight\Catalog\Model\Product\Type\Configurable $configurableProduct,
        \Magedelight\Catalog\Model\Product\Request\Type\Simple $simpleProductRequest,
        \Magedelight\Backend\Log\LoggerInterface $logger
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_productRepository = $productRepository;
        $this->_mathRandom = $mathRandom;
        $this->_productRequestRepository = $productRequestRepository;
        $this->_typeConfigurableModelFactory = $typeConfigurableModelFactory;
        $this->_simpleProductRequest = $simpleProductRequest;
        $this->_simpleVendorProduct = $simpleVendorProduct;
        $this->_simpleProduct = $simpleProduct;
        $this->_configurableProduct = $configurableProduct;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('prid');
        $tempStatus = 0;
        $storeId = $this->getRequest()->getParam('store', 0);
        if ($id) {
            try {
                $this->approveProductRequest($id, $this->getRequest()->getPostValue());
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setFormData(false);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addError(__('No such product available.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/edit', ['id' => $id, 'store' => $storeId]);
            }
        }

        $params = ['existing' => 1, 'store' => $storeId];
        if ($tempStatus != 0 && $tempStatus != 1) {
            $params['status'] = $tempStatus;
            return $resultRedirect->setPath('*/productrequest', $params);
        }
        $postValue = $this->getRequest()->getPostValue();
         if ($postValue['product']['can_list'] == '' && $postValue['product']['status'] == 1) {
            $resultRedirect->setPath('*/product', ['status' => 0,'store' => $storeId]);
        } elseif ($postValue['product']['can_list']  === '0' && $postValue['product']['status'] === "1") {
            $resultRedirect->setPath('*/product', ['status' => 0,'store' => $storeId]);
        }elseif ($postValue['product']['can_list'] === "1" && $postValue['product']['status'] === "1") {
            $resultRedirect->setPath('*/product', ['status' => 1,'store' => $storeId]);
        }

        return $resultRedirect;
    }

    /**
     * {@inheritdoc}
     */
    public function approveProductRequest($requestId, $postData = [])
    {
        try {
            $productRequest = $this->_productRequestRepository->getById($requestId);
            if (!$productRequest->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('No Product request found.'));
            }

            $requestData = $productRequest->getData();
            $newData = array_diff_key($requestData, $postData['product']);

            // make single array of productRequestData & postvaluedata
            foreach ($newData as $key => $value) {
                $postData['product'][$key] = $value;
            }

            if (array_key_exists('qty', $postData['product'])) {
                $postData['product']['quantity_and_stock_status'] = [
                        'qty' => $postData['product']['qty'],
                        'is_in_stock' => 1,
                    ];
            }

            if (array_key_exists('category_id', $postData['product'])) {
                $postData['product']['category_ids'] = [$postData['product']['category_id']];
            }

            $postData['product']['sku'] = $this->_mathRandom->getRandomString(8);
            $postData['set'] = $postData['product']['attribute_set_id'];
            $postData['store_id'] = $postData['product']['store_id'];
            $postData['product']['current_store_id'] = 0;
            $postData['type'] = \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE;
            unset($postData['product']['store_id']);

            $postData['is_downloadable'] = 0;
            $postData['affect_configurable_product_attributes'] = 1;
            $postData['new-variations-attribute-set-id'] = $postData['product']['attribute_set_id'];

            if (empty($postData)) {
                throw new \Exception(__('No data found to save.'));
            }

            $marketplaceProductId = (array_key_exists(
                'marketplace_product_id',
                $postData['product']
            ) && $postData['product']['marketplace_product_id'] > 0) ?
                $postData['product']['marketplace_product_id'] : false;
            /** No marketplace product id then create new product */
            if (!$marketplaceProductId) {
                if (array_key_exists('has_variants', $postData['product'])
                        && $postData['product']['has_variants'] == 1) {
                    //Create Configurable product
                    $product = $this->_configurableProduct->createConfigurableProduct(false, $postData);

                    //update parentId for all associated product in md_vendor_product
                    if ($product->getId()) {
                        // create configurable product in md_vendor_product table.
                        $vendorProductIds = $this->_simpleVendorProduct->createVendorProduct(
                            $product,
                            $postData,
                            true
                        );
                        $this->_simpleVendorProduct->updateParentIdForChildProduct(
                            $product,
                            $this->_configurableProduct->getVendorProductIds()
                        );
                    }
                } else {
                    $product = $this->_simpleProduct->createSimpleProduct($postData);
                    //Create simple product
                    if ($product) {
                        if ($product->getId()) {
                            // Save vendor product as approved but not listed.
                            $this->_simpleVendorProduct->createVendorProduct($product, $postData);
                        }
                    }
                }
                $this->_eventManager->dispatch(
                    'controller_action_vendor_product_save_entity_after',
                    ['controller' => $this, 'product' => $product]
                );
            } else {
                $editMode = false;
                $storeId = $postData['store_id'];
                $postData['product']['current_store_id'] = $storeId;

                if ($postData['product']['is_requested_for_edit']) {
                    if ($postData['product']['has_variants']) {
                        $this->_updateConfigurableProduct($postData);
                    } else {
                        $this->_updateSimpleProduct($postData);
                    }
                    $editMode = true;
                }

                try {
                    $product = $this->_productRepository->getById($marketplaceProductId, $editMode, $storeId);
                } catch (NoSuchEntityException $e) {
                    throw new \Exception(__('Product does not found.'));
                }

                $offerData = $postData;
                $offerData['editMode'] = $editMode;

                $this->addOfferOnProduct(
                    $marketplaceProductId,
                    $postData['product']['vendor_id'],
                    $offerData,
                    $this->_configurableProduct->getVendorProductIds()
                );
            }
            $this->_productRequestRepository->deleteById($requestId);
        } catch (\Exception $e) {
            $this->_logger->info("approve product request - ", $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $this;
    }

    /**
     * Update existing core product while request submitted for already approved product
     */
    protected function _updateSimpleProduct($postData)
    {
        $product = $this->_simpleProduct->createSimpleProduct($postData);
        $this->_simpleVendorProduct->updateVendorProduct($product, $postData);
    }

    /**
     * Update existing core (super) product as well as update vendor products
     * while ecdit request submitted for already approved product.
     * update main product if this is created by any vendor. otherwise skip it.
     */
    protected function _updateConfigurableProduct($postData)
    {
        $product = $this->_configurableProduct->createConfigurableProduct(true, $postData);
    }

    /**
     * {@inheritdoc}
     */
    protected function addOfferOnProduct($productId, $vendorId, $offerData, $vendorProductIds = [])
    {
        $postData =  $offerData;
        $marketplaceProductId = $postData['product']['marketplace_product_id'];
        $storeId = $postData['store_id'];
        $product = $this->_productRepository->getById($marketplaceProductId, $postData['editMode'], $storeId);
        // Create vendor offer on existing simple product
        if (($product->getTypeId() == \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE) &&
            !($postData['product']['is_requested_for_edit'])) {
            $parentId = $this->_simpleVendorProduct->getProductData($marketplaceProductId);
            $postData['product']['parent_id'] = ($parentId) ? $parentId : null;
            $this->_simpleVendorProduct->createVendorProduct($product, $postData);
        }

        // Create vendor offer on existing configurable product
        if (($product->getTypeId() == Configurable::TYPE_CODE) && !($postData['product']['is_requested_for_edit'])) {
            $varianData = $this->_jsonDecoder->decode($postData['product']['variants']);

            // get existing used product id of super product
            $_configurable = $product->getTypeInstance()->getUsedProductIds($product);

            $attributesInfo = [];
            $productIds = [];

            foreach ($varianData as $data) {
                $attributesInfo = []; // reset array variable

                foreach ($this->_configurableProduct->getUsedConfigurableAttributeCodes($postData) as $key => $value) {
                    $attributesInfo[$key] = $data[$value];
                }
                $subProduct = $this->_typeConfigurableModelFactory->getProductByAttributes($attributesInfo, $product);

                if ($subProduct) {
                    $postData['product']['name'] = $subProduct->getName();
                    foreach ($data as $key => $value) {
                        $postData['product'][$key] = $value; // override the element values for each product
                    }
                    $vendorProductIds[] = $this->_simpleVendorProduct->createVendorProduct($subProduct, $postData);
                } else {
                    $data['name'] = $product->getName();
                    $id = $this->_configurableProduct->createSimpleAssociateProduct($data, $postData);
                    $productIds[] = $id;
                }
            }
            if (is_array($productIds) && count($productIds)) {
                $productIds = array_merge($_configurable, $productIds);
                $this->_typeConfigurableFactory->create()->saveProducts($product, $productIds);
            }

            // update parent_id for all newly created child product in vendor produt table
            $this->_simpleVendorProduct->updateParentIdForChildProduct($product, $vendorProductIds);
        }
    }
}
