<?php

namespace CAT\AIRecommend\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use CAT\AIRecommend\Helper\Data;

class Slider extends \Magento\Catalog\Block\Product\AbstractProduct
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

    public function getviewallUrl()
    {
        return $this->getUrl('airecommend/recommend/index/');
    }
    public function getSliderCollection()
    {
        $sliders = [];
        /** @var Collection $collection */
        /** @var Collection $collection */
        $gendertxt = "unspecified";

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $Gender = $objectManager->get('Magento\Customer\Model\Session')->getCustomer()->getGender();
        if($Gender == '1'){
            $gendertxt = "male";
        }
        if($Gender == '2'){
            $gendertxt = "female";
        }
        $language = "English";
        if($this->_storeManager->getStore()->getId() == 2){
            $language = "Arabic";
        }
        $limit = $this->helperData->getLimit();
        $AIProducts = $this->helperData->getEventbasedProduct($language,$gendertxt);
        if(!empty($AIProducts)){
            //foreach($AIProducts as $key => $AI_Products){

                //$AI_Products = $this->helperData->getAIProducts();
                $collection = $this->_productCollectionFactory->create();
                $collection->addFieldToSelect('*');
                $collection->addFieldToFilter('entity_id', ['in' => $AIProducts['recommendation']]);
                $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
                $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
                $collection->setPageSize($limit)->setCurPage(1)->getSelect()->orderRand();
                $sliders[$AIProducts['event']] = $collection;
            //}
        }
        return $sliders;
    }

}
