<?php
namespace CAT\Custom\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class EntityOptions
 * @package CAT\Custom\Model\Source
 */
class EntityOptions implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $this->options = [];
        $this->options =[
            ['value' => Option::STORE_CREDIT_KEYWORD, 'label' => __('Store Credit')],
            ['value' => Option::PRODUCT_OFFERS_KEYWORD, 'label' => __('Product Offers')],
            ['value' => Option::PRODUCT_SKU_KEYWORD, 'label' => __('Product Sku')]
            /*['value' => Option::INVOICE_SHIPMENT_KEYWORD, 'label' => __('Invoice/Shipment')],*/
            /*['value' => Option::VENDOR_PAYMENT_STATUS, 'label' => __('Vendor Payment')]*/
        ];
        return $this->options;
    }
}
