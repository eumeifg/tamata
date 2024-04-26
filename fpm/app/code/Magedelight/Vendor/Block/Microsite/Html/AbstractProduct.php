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
namespace Magedelight\Vendor\Block\Microsite\Html;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\Helper\Data;

class AbstractProduct extends \Magento\Catalog\Block\Product\AbstractProduct
{

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $vendorHelperData;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Data
     */
    protected $urlHelper;
    
    /**
	* @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
	*/
	protected $_eavAttribute;

    /**
     * AbstractProduct constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param Product\Visibility $catalogProductVisibility
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magedelight\Catalog\Helper\Data $vendorHelperData
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param CategoryRepositoryInterface $categoryRepository
     * @param FlatState $flatState
     * @param Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magedelight\Catalog\Helper\Data $vendorHelperData,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        CategoryRepositoryInterface $categoryRepository,
        FlatState $flatState,
        Data $urlHelper,
        array $data = []
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->vendorHelperData = $vendorHelperData;
        $this->priceHelper = $priceHelper;
        $this->urlHelper = $urlHelper;
        $this->_eavAttribute = $eavAttribute;
        $this->flatState = $flatState;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCollectionForSimple()
    {
        $collection = $this->_productCollectionFactory->create();

        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToFilter('status', '1');
        } else {
            $collection->addAttributeToFilter('status', '1');
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection->getSelect()->join(
            ['vprodc' => 'md_vendor_product'],
            "e.entity_id = vprodc.marketplace_product_id AND vprodc.qty > 0 AND vprodc.vendor_id = "
            . $this->getRequest()->getParam('vid')
        );

        $collection->getSelect()->join(
            ['rbvpw' => 'md_vendor_product_website'],
            "rbvpw.vendor_product_id = vprodc.vendor_product_id AND rbvpw.website_id = "
            . $this->_storeManager->getStore()->getWebsiteId() . " AND rbvpw.status = 1",
            ['rbvpw.status']
        );
        return $collection;
    }

    /**
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCollectionForConfig()
    {
        $collection = $this->_productCollectionFactory->create();
        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToFilter('status', '1');
        } else {
            $collection->addAttributeToFilter('status', '1');
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection->getSelect()->join(
            ['vprodc' => 'md_vendor_product'],
            "e.entity_id = vprodc.parent_id AND vprodc.qty > 0 AND vprodc.vendor_id = "
            . $this->getRequest()->getParam('vid')
        );
        $collection->getSelect()->join(
            ['rbvpw' => 'md_vendor_product_website'],
            "rbvpw.vendor_product_id = vprodc.vendor_product_id AND rbvpw.website_id = "
            . $this->_storeManager->getStore()->getWebsiteId() . " AND rbvpw.status = 1",
            ['rbvpw.status']
        );

        return $collection;
    }

    /**
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
                ->setImageId($imageId)
                ->setAttributes($attributes)
                ->create();
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return parent::getProductPrice($product);
    }

    /**
     * @param $product
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRatingSummary($product)
    {
        $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();

        if ($ratingSummary == '' || $ratingSummary == 0) {
            $ratingSummary = 0;
            return $ratingSummary;
        } else {
            return $ratingSummary;
        }
    }

    /**
     * Get post parameters
     *
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
}
