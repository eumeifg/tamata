<?php

namespace MDC\Sales\Block\Sellerhtml\Vendor\Order;

class SummarizedDetails extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order\SummarizedDetails
{
    public function getSummarizedOrderData() {
        $vendorSkus = [];
        if($this->getOrder()){

            /* display variant prodcut image if ordered item is configurable */
            $variantProductImg = '';
            foreach ($this->getOrder()->getItems() as $item){

                if($item->getVendorId() != $this->getVendor()->getVendorId() || $item->getParentItem()){
                    $variantProductImg = $item->getExtensionAttributes()->getOrderItemImageData()->getImageUrl();
                }
            }
            /* display variant prodcut image if ordered item is configurable */

            foreach ($this->getOrder()->getItems() as $item){
                if($item->getVendorId() != $this->getVendor()->getVendorId() || $item->getParentItem()){
                    continue;
                }
                $vendorProduct  = $item->getVendorProduct($this->getVendor()->getVendorId());
                if(!$vendorProduct){
                    continue;
                }
                $orderItemExtensionAtts = $item->getExtensionAttributes();

                $vendorSkus[$vendorProduct->getVendorSku()]['product_name'] = $vendorProduct->getProductName();
                // $vendorSkus[$vendorProduct->getVendorSku()]['image_url'] = $orderItemExtensionAtts->getOrderItemImageData()->getImageUrl();

                /* display variant prodcut image if ordered item is configurable */
                $vendorSkus[$vendorProduct->getVendorSku()]['image_url'] = isset( $variantProductImg ) && !empty( $variantProductImg )? $variantProductImg : $orderItemExtensionAtts->getOrderItemImageData()->getImageUrl();
                /* display variant prodcut image if ordered item is configurable */

                $vendorSkus[$vendorProduct->getVendorSku()]['qty'] = $item->getQtyOrdered();
                $vendorSkus[$vendorProduct->getVendorSku()]['row_total'] = $item->getRowTotal();
                $params = ['approve' => '1' , 'id' => $vendorProduct->getVendorProductId(), 'tab' => '1,0'];
                $productUrl = $this->getUrl('rbcatalog/product/approvededit/', $params);
                $vendorSkus[$vendorProduct->getVendorSku()]['product_url'] = $productUrl;
                if($item->getProduct()->getTypeId() === 'configurable'){
                    /* Configurable or parent products can not be edited in seller panel. Only child can be edited. */
                    $vendorSkus[$vendorProduct->getVendorSku()]['product_url'] = '';
                }

                if($item->getProductType() == "configurable") {
                    $productOptions = $item->getProductOptions();
                    $vendorSkus[$vendorProduct->getVendorSku()]['options'] = $productOptions['attributes_info'];
                }

                $vendorSkus[$vendorProduct->getVendorSku()]['product_storefront_url'] = $item->getProduct()->getProductUrl();
                $vendorSkus[$vendorProduct->getVendorSku()]['barcode'] = $item->getProduct()->getBarCode();

            }
        }
        return $vendorSkus;
    }
}
