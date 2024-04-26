<?php
namespace CAT\Custom\Model\Source;

use Magento\Framework\DataObject;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Option
 * @package CAT\Custom\Model\Source
 */
class Option extends DataObject implements OptionSourceInterface
{
    const STORE_CREDIT_KEYWORD = 'store_credit';

    const PRODUCT_OFFERS_KEYWORD = 'product_offer';

    const VENDOR_QTY_KEYWORD = 'vendor_qty';
    const VENDOR_QTY_UPDATE_BATCH_LIMIT = 'automation/vendor_qty_update_config/batch_limit';

    const PRODUCT_OFFER_UPDATE_BATCH_LIMIT = 'automation/offer_update_config/batch_limit';

    const PRODUCT_SKU_KEYWORD = 'product_sku';

    const PRODUCT_SKU_UPDATE_BATCH_LIMIT = 'automation/sku_update_config/batch_limit';

    /*const INVOICE_SHIPMENT_KEYWORD = 'invoice_shipment';*/

    /*const VENDOR_PAYMENT_STATUS = 'vendor_payment_status';*/

    const ATTRIBUTE_LIST = [
        self::STORE_CREDIT_KEYWORD,
        self::PRODUCT_OFFERS_KEYWORD,
        self::PRODUCT_SKU_KEYWORD
        /*self::INVOICE_SHIPMENT_KEYWORD,*/
        /*self::VENDOR_PAYMENT_STATUS*/
    ];

    /**
     * Option constructor.
     * @param array $data
     */
    public function __construct(
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [
            '' => __('Please Select'),
            self::STORE_CREDIT_KEYWORD =>  __('Store Credit'),
            self::PRODUCT_OFFERS_KEYWORD => __('Product Offers'),
            self::PRODUCT_SKU_KEYWORD => __('Sku Update'),
            /*self::INVOICE_SHIPMENT_KEYWORD => __('Invoice/Shipment'),
            self::VENDOR_PAYMENT_STATUS => __('Vendor Payment')*/
        ];
        return $optionArray;
    }
}
