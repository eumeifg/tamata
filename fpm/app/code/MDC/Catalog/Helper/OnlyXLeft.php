<?php

namespace MDC\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;

class OnlyXLeft extends AbstractHelper
{

	public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magedelight\Catalog\Helper\Data $mdHelper
    ) {
      
        $this->_productRepository= $productRepository;        
        $this->scopeConfig = $scopeConfig;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->productFactory = $productFactory;
        $this->mdHelper = $mdHelper;
    }

    public function getProductXleftById($productId){

        $product = $this->productFactory->create()->load($productId);

        $productSku =  $product->getSku();
        
        /*Get qty threshold for only x left item*/
        $thresholdQty = $this->scopeConfig->getValue("cataloginventory/options/stock_threshold_qty",ScopeInterface::SCOPE_STORE);            
        /*Get qty threshold for only x left item*/

        $salableQuantityDataBySku = $this->getSalableQuantityDataBySku->execute($productSku);

        $salableQty = $salableQuantityDataBySku[0]['qty'];

        // $vendorData = $this->mdHelper->getAvailableVendorsForProduct($productId, false, true);
        $noOfVendors = $this->mdHelper->getProductNoOfVendorsForGraphQl($productId);

        if($salableQty > 0 && $noOfVendors >= 0 && $salableQty <= $thresholdQty){
            $data = array("status" => true, "qty" => $salableQty);
        }else{
            $data = array("status" => false, "qty" => 0);
        }

        return $data;

    }
}
