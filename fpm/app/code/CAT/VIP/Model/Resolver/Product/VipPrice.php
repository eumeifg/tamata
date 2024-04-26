<?php

namespace CAT\VIP\Model\Resolver\Product;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class VipPrice implements ResolverInterface
{
    protected $dataHelper;
    protected $catalogHelper;

    public function __construct(
        \CAT\VIP\Helper\Data $dataHelper,
        \CAT\VIP\Helper\UpdatesData $catalogHelper
    ) {

        $this->dataHelper = $dataHelper;
        $this->catalogHelper = $catalogHelper;
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
        if ($product->getTypeId() == 'configurable') {
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($childProducts as $childProduct) {
                $isVip = $this->isCheckVip($childProduct->getId());
                if ($isVip !== false){
                   return $isVip;
                }
            }
        } else {
            $productId = $product->getId();
            return $this->isCheckVip($productId);
        }
    }

    /**
     * @param $productId
     * @return false|float
     * @throws \Exception
     */
    public function isCheckVip($productId) {
        // Check VIP
        $defaultVendorProduct = $this->catalogHelper->getProductDefaultVendor($productId);
        $productVid = $defaultVendorProduct->getVendorId();
        $vip = $this->dataHelper->isvipProduct($productId,$productVid);
        if($vip){
            if ($this->catalogHelper->getVendorSpecialPrice($productVid, $productId) !== null) {
                $ProductPrice = (float)$this->catalogHelper->getVendorSpecialPrice($productVid, $productId);
            } else {
                $ProductPrice = (float)$this->catalogHelper->getVendorPrice($productVid, $productId);
            }
            // Fixed discount
            if($vip->getDiscountType() == 'Fixed'){
                $finalprice = ( $ProductPrice - (float)$vip->getDiscount());
            }
            else{
                $discount = (float)$vip->getDiscount();
                $discountPrice = (($ProductPrice / 100) * $discount);
                $finalprice = ( $ProductPrice - $discountPrice);
            }
            return $finalprice;
        }
        return false;
    }


}
