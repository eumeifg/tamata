<?php

declare(strict_types=1);

namespace Magedelight\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class OnlyXLeft implements ResolverInterface
{
    protected $_productRepository;    
    protected $storeManager;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        \MDC\Catalog\Helper\OnlyXLeft $mdcHelper
    ) {
      
        $this->_productRepository= $productRepository;        
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->mdcHelper = $mdcHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var $product Product */
        $product = $value['model'];

        $productId = $product->getId();
        $productSku = $product->getSku();
        $productTypeId = $product->getTypeId();

        /*Get qty threshold for only x left item*/
        $thresholdQty = $this->scopeConfig->getValue("cataloginventory/options/stock_threshold_qty",ScopeInterface::SCOPE_STORE);            
        /*Get qty threshold for only x left item*/

        if($productTypeId === "simple"){

            $salableQuantityDataBySku = $this->getSalableQuantityDataBySku->execute($productSku);

            $salableQty = $salableQuantityDataBySku[0]['qty'];

            $thresoldStatus = $this->mdcHelper->getProductXleftById($productId);

            if($thresoldStatus["status"]){
                
                return $salableQty;
            }else{
                return "";
            }

        }else{
            return "";
        }

    }

    
}
