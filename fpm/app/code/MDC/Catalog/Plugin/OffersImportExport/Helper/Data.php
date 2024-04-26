<?php

namespace MDC\Catalog\Plugin\OffersImportExport\Helper;
/**
 * Class Data
 * @package MDC\Catalog\Plugin\OffersImportExport\Helper
 */
class Data
{
    /**
     * @param \Magedelight\OffersImportExport\Helper\Data $subject
     * @param $result
     * @return mixed
     */
    public function afterGetSampleData(\Magedelight\OffersImportExport\Helper\Data $subject, $result) {
        $result['cost_price_iqd'] = "350";
        $result['cost_price_usd'] = '5';
        $result['product_name'] = 'xyz';
        return $result;
    }

    /**
     * @param \Magedelight\OffersImportExport\Helper\Data $subject
     * @param $result
     * @return mixed
     */
    public function afterGetCSVFields(\Magedelight\OffersImportExport\Helper\Data $subject, $result) {
        $result['cost_price_iqd'] = 'cost_price_iqd';
        $result['cost_price_usd'] = 'cost_price_usd';
        $result['product_name'] = 'product_name';
        return $result;
    }

    /**
     * @param \Magedelight\OffersImportExport\Helper\Data $subject
     * @param $result
     * @return string
     */
    public function afterGetTemplate(\Magedelight\OffersImportExport\Helper\Data $subject, $result) {
        $result = $result.',"{{cost_price_iqd}}","{{cost_price_usd}}","{{product_name}}"';
        return $result;
    }
}
