<?php

namespace CAT\AIRecommend\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use CAT\AIRecommend\Helper\Data;

class Marketbased extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Default template to use for slider widget
     */
    protected $_template = 'CAT_AIRecommend::marketsproducts.phtml';

    /**
     * @var Data
     */
    protected $helperData;
    protected $_storeManager;
    protected $cart;

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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $cart
    ) {
        $this->helperData = $helperData;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->_storeManager = $storeManager;
        $this->cart = $cart;
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

        if($this->helperData->IsMarketbasedEnable()){
            $cartitems =  $this->cart->getQuote()->getAllVisibleItems();
            $productID = '';
            foreach($cartitems as $key => $cartitem){
                if($key > 0){
                    $productID.= '&';
                }
                $productID.= 'items_list='.$cartitem->getProductId();
                //break;
            }
            //$productID = 750909;
            $AIProducts = $this->helperData->getMarketbasedProduct($productID);
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

}
