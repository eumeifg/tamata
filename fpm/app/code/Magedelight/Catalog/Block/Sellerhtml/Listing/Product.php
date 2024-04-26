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
namespace Magedelight\Catalog\Block\Sellerhtml\Listing;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Product extends \Magedelight\Backend\Block\Template
{

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $productRequestFactory;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    public $catalogHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magedelight\Catalog\Model\VendorProduct\Listing\LiveProducts
     */
    protected $liveProducts;

    /**
     * @var \Magedelight\Catalog\Model\VendorProduct\Listing\ApprovedProducts
     */
    protected $approvedProducts;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Product constructor.
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magedelight\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magedelight\Catalog\Model\VendorProduct\Listing\LiveProducts $liveProducts
     * @param \Magedelight\Catalog\Model\VendorProduct\Listing\ApprovedProducts $approvedProducts
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magedelight\Catalog\Model\VendorProduct\Listing\LiveProducts $liveProducts,
        \Magedelight\Catalog\Model\VendorProduct\Listing\ApprovedProducts $approvedProducts,
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->productRequestFactory = $productRequestFactory;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->productFactory= $productFactory;
        $this->serializer = $serializer;
        $this->attributeRepository = $attributeRepository;
        $this->catalogHelper = $catalogHelper;
        $this->imageBuilder = $imageBuilder;
        $this->liveProducts = $liveProducts;
        $this->approvedProducts = $approvedProducts;
        parent::__construct($context, $data);
    }

    /**
     * get mylisting grid URL
     * @return string
     */
    public function getParamUrl()
    {
        return $this->getUrl('rbcatalog/listing/index/');
    }

    /**
     *
     * @param string $field
     * @param string|array $value
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function renderField($field, $value)
    {
        $attribute = $this->attributeRepository->get('catalog_product', $field);
        if (is_array($value)) {
            $value = $value[0];
        }
        $value = $attribute->getSource()->getOptionText($value);
        return $value;
    }

    /**
     *
     * @return string
     */
    public function getAjaxEditUrl()
    {
        return $this->getUrl('rbcatalog/listing/ajaxlive/', ['tab' => '1,0']);
    }

    /**
     * get current logged in vendor object
     * @return object
     */
    public function getVendor()
    {
        return $this->authSession->getUser();
    }

    /**
     * get current logged in vendor object
     * @return object
     */
    public function getSessionForGrid()
    {
        return $this->authSession;
    }

    /**
     *
     * @param array $reqParams
     * @return array
     */
    public function getSortOrderData($reqParams = [])
    {
        $sortOrder = $this->getRequest()->getParam('sort_order');
        switch ($this->getRequest()->getParam('dir')) {
            case 'ASC':
                $sortDir = 'DESC';
                break;
            case 'DESC':
                $sortDir = 'ASC';
                break;
            default:
                $sortDir = 'DESC';
        }
        $fields = [
            // 'marketplace_sku' => __('Marketplace Sku'),
            'vendor_sku' => __('Vendor Sku'),
            'product_name' => __('Product Name'),
            'price' => __('Price'),
            'special_price' => __('Special Price'),
            'qty' => __('Quantity')
        ];
        $sortOrderData = [];
        foreach ($fields as $field => $label) {
            $sortOrderData[$field]['sort_params'] = array_merge($reqParams, ['sort_order' => $field]);
            $sortOrderData[$field]['label'] = $label;
            if ($sortOrder == $field) {
                $sortOrderData[$field]['sort_params']['dir'] = $sortDir;
                if ($sortDir == 'ASC') {
                    $sortOrderData[$field]['sort_dir_class'] = ' sort-asc';
                    $sortOrderData[$field]['sort_dir_title'] = 'Set Ascending Direction';
                } else {
                    $sortOrderData[$field]['sort_dir_class'] = ' sort-desc';
                    $sortOrderData[$field]['sort_dir_title'] = 'Set Descending Direction';
                }
            } else {
                $sortOrderData[$field]['sort_dir_class'] = '';
                $sortOrderData[$field]['sort_dir_title'] = '';
                $sortOrderData[$field]['sort_params']['dir'] = "ASC";
            }
        }
        return $sortOrderData;
    }

    /**
     *
     * @param \Magento\Catalog\Block\Product $product
     * @param integer $childId
     * @return string
     */
    public function getProductUrl($product, $childId = null)
    {
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            return $product->getProductUrl();
        }

        if ($childId) {
            return $this->getProduct($childId)->getProductUrl();
        } else {
            return $product->getProductUrl() . '?v=' . $this->getVendor()->getVendorId();
        }
    }

    /**
     *
     * @return integer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentWebsite()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     *
     * @return integer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }

    /**
     *
     * @param $prodId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($prodId)
    {
        $product = $this->productFactory->create()->load($prodId);
        return $product;
    }
}
