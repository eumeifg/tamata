<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ktpl\Productslider\Helper\Data;

/**
 * Class CategoryId
 * @package Ktpl\Productslider\Block
 */
class CategoryId extends AbstractSlider
{
    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

     /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * Catalog product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * CategoryId constructor.
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param HttpContext $httpContext
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        DateTime $dateTime,
        Data $helperData,
        HttpContext $httpContext,
        CategoryFactory $categoryFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;

        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $dateTime,
            $helperData,
            $httpContext,
            $data
        );
    }

    /**
     * Get Product Collection by Category Ids
     *
     * @return $this|array
     */
    public function getProductCollection()
    {
        // $productIds = $this->getProductIdsByCategory();
        // $collection = [];
        // if (!empty($productIds)) {
        //     $collection = $this->_productCollectionFactory->create()
        //     ->addIdFilter($productIds)
        //     ->setPageSize($this->getProductsCount());
        //     $this->_addProductAttributesAndPrices($collection);
        // }

        /* Changed collection flow from above as collection was overrided further and sorting by position was not working */        
        return $this->getProductIdsByCategory(); 

        // return $collection;
    }

    /**
     * Get ProductIds by Category
     *
     * @return array
     */
    public function getProductIdsByCategory()
    {
        $productIds = [];
        $catIds = $this->getSliderCategoryIds();
        if (is_array($catIds)) {
            foreach ($catIds as $catId) {
                $category = $this->_categoryFactory->create()->load($catId);
                $collection = $this->_productCollectionFactory->create();
                $collection->addAttributeToSelect('*')->addCategoryFilter($category)->addAttributeToSort('position', 'ASC');
                foreach ($collection as $item) {
                    $productIds[] = $item->getData('entity_id');
                }
            }

        } else {
            $collection = $this->_productCollectionFactory->create();
            $category = $this->_categoryFactory->create()->load($catIds);
            $collection->addAttributeToSelect('*')->addCategoryFilter($category)->addAttributeToSort('position', 'ASC');
            foreach ($collection as $item) {
                $productIds[] = $item->getData('entity_id');
            }
        }

        return $collection;
        // return $productIds;
    }

    /**
     * Get Slider CategoryIds
     *
     * @return array|int|mixed
     */
    public function getSliderCategoryIds()
    {
        if ($this->getData('category_id')) {
            return $this->getData('category_id');
        }
        if ($this->getSlider()) {
            $catIds = explode(',', $this->getSlider()->getCategoriesIds());

            return $catIds;
        }

        return 2;
    }

    public function getReviewSummary($productId)
    {
        $reviewFactory = $this->_reviewFactory->create();
        $product = $this->_productFactory->create()->load($productId);
        $storeId = $this->_storeManager->getStore()->getStoreId();
        $reviewFactory->getEntitySummary($product, $storeId);

        $reviewData['ratingSummary'] = $product->getRatingSummary()->getRatingSummary();
        $reviewData['reviewCount'] = $product->getRatingSummary()->getReviewsCount();

        return $reviewData;
    }


    public function getSliderViewAllLink()
    {
        return $this->getSlider()->getSliderViewAllLink();
    }
}
