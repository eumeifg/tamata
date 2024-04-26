<?php

namespace CAT\AIRecommend\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use CAT\AIRecommend\Helper\Data;

class Fashionbased extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Default template to use for slider widget
     */
    protected $_template = 'CAT_AIRecommend::productslider.phtml';

    /**
     * @var Data
     */
    protected $helperData;
    protected $_storeManager;

    /**
     * Data constructor.
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Data $helperData,
        CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = [],
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helperData = $helperData;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getStore(){
        return $this->_storeManager->getStore();
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getSliderCollection()
    {

        /** @var Collection $collection */

        if($this->helperData->IsFashionbasedEnable()){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');//get current product
            $productID = $product->getId();
            $categories = $product->getCategoryIds();
            $isfashion = false;
            foreach($categories as $category){
                $cat = $objectManager->create('Magento\Catalog\Model\Category')->load($category);
                if($cat->getParentCategories()){
                    foreach ($cat->getParentCategories() as $parent) {
                        if ($parent->getLevel() == 2) {
                            // reurns the level 1 category id;
                            if($parent->getId() == 4 ){
                                $isfashion = true;
                            }
                        }
                    }
                }
            }
            if($isfashion){

                //$productID = 603402;
                $AIProducts = $this->helperData->getFashionbasedProduct($productID);
                //$AIProducts = $this->helperData->getAIProducts();
                $collection = $this->_productCollectionFactory->create();
                $collection->addFieldToSelect('*');
                $collection->addFieldToFilter('entity_id', ['in' => $AIProducts]);
                $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
                $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
                $collection->getSelect()->orderRand();
                return $collection;
            }
            else{
                return [];
            }

        }
        else{
            return [];
        }
    }

}
