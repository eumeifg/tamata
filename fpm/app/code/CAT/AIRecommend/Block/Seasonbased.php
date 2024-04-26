<?php

namespace CAT\AIRecommend\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use CAT\AIRecommend\Helper\Data;

class Seasonbased extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Default template to use for slider widget
     */
    protected $_template = 'CAT_AIRecommend::seasonslider.phtml';

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

    public function getviewallUrl()
    {
        return $this->getUrl('airecommend/recommend/index/');
    }
    public function getSliderCollection()
    {

        /** @var Collection $collection */
        if($this->helperData->IsSeasonbasedEnable()){
            $AIProducts = $this->helperData->getSeasonbasedProduct();
           // $AIProducts = $this->helperData->getAIProducts();
            $limit = $this->helperData->getLimit();
            $collection = $this->_productCollectionFactory->create();
            $collection->addFieldToSelect('*');
            $collection->addFieldToFilter('entity_id', ['in' => $AIProducts]);
            $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
            $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
            $collection->setPageSize($limit)->setCurPage(1)->getSelect()->orderRand();
            return $collection;
        }
        else{
            return [];
        }
    }

}
