<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\ProductView\Helper;

/**
 * Price locale format.
 */
class Format extends \Magento\Framework\Locale\Format
{

    /**
     * Returns an array with price formatting info
     *
     * @param string $localeCode Locale code.
     * @param string $currencyCode Currency code.
     * @return array
     */
    public function getPriceFormat($localeCode = null, $currencyCode = null)
    {
        $localeCode = $localeCode ?: $this->_localeResolver->getLocale();
        if ($currencyCode) {
            $currency = $this->currencyFactory->create()->load($currencyCode);
        } else {
            $currency = $this->_scopeResolver->getScope()->getCurrentCurrency();
        }

        $formatter = new \NumberFormatter(
            $currency->getCode() ? $localeCode . '@currency=' . $currency->getCode() : $localeCode,
            \NumberFormatter::CURRENCY
        );
        /*Ktpl customization starts*/
        //echo $localeCode;die;
        //if($localeCode == 'en_US') {
            $formatter->setPattern('¤#,##0.00');
        //}
        /*Ktpl customization ends*/
        
        $format = $formatter->getPattern();
        $decimalSymbol = $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        $groupSymbol = $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);

        $pos = strpos($format, ';');
        if ($pos !== false) {
            $format = substr($format, 0, $pos);
        }
        $format = preg_replace("/[^0\#\.,]/", '', $format);
        $totalPrecision = 0;
        $decimalPoint = strpos($format, '.');
        if ($decimalPoint !== false) {
            $totalPrecision = strlen($format) - (strrpos($format, '.') + 1);
        } else {
            $decimalPoint = strlen($format);
        }
        $requiredPrecision = $totalPrecision;
        $t = substr($format, $decimalPoint);
        $pos = strpos($t, '#');
        if ($pos !== false) {
            $requiredPrecision = strlen($t) - $pos - $totalPrecision;
        }

        if (strrpos($format, ',') !== false) {
            $group = $decimalPoint - strrpos($format, ',') - 1;
        } else {
            $group = strrpos($format, '.');
        }

        $result = [
            //TODO: change interface
            'pattern' => $currency->getOutputFormat(),
            'precision' => $totalPrecision,
            'requiredPrecision' => $requiredPrecision,
            'decimalSymbol' => $decimalSymbol,
            'groupSymbol' => $groupSymbol,
            'groupLength' => $group,
            'integerRequired' => $totalPrecision == 0,
        ];

        return $result;
    }
}
