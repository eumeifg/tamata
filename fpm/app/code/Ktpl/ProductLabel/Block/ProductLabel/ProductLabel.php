<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ProductLabel
 * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ProductLabel\Block\ProductLabel;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Api\Data\ProductInterface;
use Ktpl\ProductLabel\Api\Data\ProductLabelInterface;
use Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory as ProductLabelCollectionFactory;

class ProductLabel extends Template implements IdentityInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ProductLabelCollectionFactory
     */
    protected $productLabelCollectionFactory;

    /**
     * @var \Ktpl\ProductLabel\Model\ImageLabel\Image
     */
    protected $imageHelper;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Ktpl\ProductLabel\Model\ImageLabel\Image $imageHelper,
        ProductLabelCollectionFactory $productLabelCollectionFactory,
        \Magento\Framework\App\CacheInterface $cache,
        array $data = []
    ) {
        $this->registry                      = $registry;
        $this->imageHelper                   = $imageHelper;
        $this->productLabelCollectionFactory = $productLabelCollectionFactory;
        $this->cache                         = $cache;
        $this->storeManager                  = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    public function getCurrentView()
    {
        $view = ProductLabelInterface::PRODUCTLABEL_DISPLAY_LISTING;
        if ($this->getRequest()->getControllerName('controller') == 'product') {
            $view = ProductLabelInterface::PRODUCTLABEL_DISPLAY_PRODUCT;
        }

        return $view;
    }

    public function getWrapperClass()
    {
        $class = 'listing';

        if ($this->getCurrentView() === ProductLabelInterface::PRODUCTLABEL_DISPLAY_PRODUCT) {
            $class = 'product';
        }

        return $class;
    }

    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }

    public function getProduct()
    {
        if (null === $this->product) {
            $this->product = $this->registry->registry('current_product');
        }

        return $this->product;
    }

    public function getAttributesOfCurrentProduct()
    {
        $attributesList = [];
        $attributeIds   = array_column($this->getProductLabelsList(), 'attribute_id');
        $productEntity  = $this->getProduct()->getResourceCollection()->getEntity();

        foreach ($attributeIds as $attributeId) {
            $attribute = $productEntity->getAttribute($attributeId);
            if ($attribute) {
                $optionIds = $this->getProduct()->getCustomAttribute($attribute->getAttributeCode());

                $attributesList[$attribute->getId()] = [
                    'id'      => $attribute->getId(),
                    'label'   => $attribute->getFrontend()->getLabel(),
                    'options' => ($optionIds) ? $optionIds->getValue() : '',
                ];
            }
        }

        return $attributesList;
    }

    public function getProductLabels($fromReadHandler = false)
    {
        $productLabels     = [];
        $productLabelList  = $this->getProductLabelsList();
        $attributesProduct = $this->getAttributesOfCurrentProduct();

        foreach ($productLabelList as $productLabel) {
            $attributeIdLabel = $productLabel['attribute_id'];
            $optionIdLabel    = $productLabel['option_id'];
            foreach ($attributesProduct as $attribute) {
                if (isset($attribute['id']) && ($attributeIdLabel == $attribute['id'])) {
                    $options = $attribute['options'] ?? [];
                    if (!is_array($options)) {
                        $options = explode(',', $options);
                    }
                    if (in_array($optionIdLabel, $options) && in_array($this->getCurrentView(), $productLabel['display_on'])) {
                        $productLabel['class'] = $this->getCssClass($productLabel);
                        $productLabel['image'] = $this->getImageUrl($productLabel['image']);
                        $class = $this->getCssClass($productLabel);
                        if ($fromReadHandler) {
                            $productLabels[] = $productLabel;
                        } else {
                            $productLabels[$class][] = $productLabel;
                        }
                        
                    }
                }
            }
        }

        return $productLabels;
    }

    public function getImageUrl($imageName)
    {
        return $this->imageHelper->getBaseUrl() . '/' . $imageName;
    }

    public function getIdentities()
    {
        $identities = [];

        /**
         * @var IdentityInterface $product
        */
        $product = $this->getProduct();
        if ($product) {
            $identities = array_merge($identities, $product->getIdentities(), [\Ktpl\ProductLabel\Model\ProductLabel::CACHE_TAG]);
        }

        return $identities;
    }

    private function getCssClass($productLabel)
    {
        $class = '';

        if ($this->getCurrentView() === ProductLabelInterface::PRODUCTLABEL_DISPLAY_PRODUCT) {
            $class = $productLabel['position_product_view'] . ' product';
        }

        if ($this->getCurrentView() === ProductLabelInterface::PRODUCTLABEL_DISPLAY_LISTING) {
            $class = $productLabel['position_category_list'] . ' category';
        }

        return $class;
    }

    private function getProductLabelsList()
    {
        $storeId          = $this->getStoreId();
        $cacheKey         = 'ktpl_productlabel_frontend_' . $storeId;
        $productLabelList = $this->cache->load($cacheKey);

        if (is_string($productLabelList)) {
            $productLabelList = json_decode($productLabelList, true);
        }

        if ($productLabelList === false) {
            /**
             * @var \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory
            */
            $productLabelsCollection = $this->productLabelCollectionFactory->create();
            $productLabelList        = $productLabelsCollection
                ->addStoreFilter($storeId)
                ->addFieldToFilter('is_active', true)
                ->getData();

            $productLabelList        = array_map(
                function ($label) {
                    $label['display_on'] = explode(',', $label['display_on']);

                    return $label;
                },
                $productLabelList
            );

            $this->cache->save(json_encode($productLabelList), $cacheKey, [\Ktpl\ProductLabel\Model\ProductLabel::CACHE_TAG]);
        }

        return $productLabelList;
    }

    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
