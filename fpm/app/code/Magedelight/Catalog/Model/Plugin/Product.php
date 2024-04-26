<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\Plugin;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Product
{

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var bool|float
     */
    protected $value = null;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        TimezoneInterface $localeDate
    ) {
        $this->localeDate = $localeDate;
    }

    /**
     * Returns special price
     *
     * @return float
     */
    public function afterGetSpecialPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        $specialPrice = $subject->getData('vendor_special_price');
        if (!isset($specialPrice)):
            $specialPrice = $subject->getData('special_price');
        endif;
        if ($specialPrice !== null && $specialPrice !== false &&
            $subject->getData('special_from_date') !== null &&
            $this->_isScopeDateInInterval(
                $subject->getStore(),
                $subject->getData('special_from_date'),
                $subject->getData('special_to_date')
            )
        ) {
            return (float) $specialPrice;
        }
        return null;
    }

    /**
     * Returns starting date of the special price
     *
     * @return mixed
     */
    public function afterGetSpecialFromDate(\Magento\Catalog\Model\Product $subject, $result)
    {
        return $subject->getData('vendor_special_from_date');
    }

    /**
     * Returns end date of the special price
     *
     * @return mixed
     */
    public function afterGetSpecialToDate(\Magento\Catalog\Model\Product $subject, $result)
    {
        return $subject->getData('vendor_special_to_date');
    }

    /**
     * @return bool
     */
    protected function _isScopeDateInInterval($store, $specialFromDate, $specialToDate)
    {
        return $this->localeDate->isScopeDateInInterval(
            $store,
            $specialFromDate,
            $specialToDate
        );
    }
}
