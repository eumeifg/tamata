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
namespace Magedelight\Catalog\Helper\Pricing;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Pricing data helper
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);
        $this->priceCurrency =  $priceCurrency;
    }

    /**
     * Convert and format price value for current application store
     *
     * @param   float $value
     * @param   bool $format
     * @param   bool $includeContainer
     * @param   bool $convertAndRound
     * @param   string $currency
     * @return  float|string
     */
    public function currency(
        $value,
        $format = true,
        $includeContainer = true,
        $convertAndRound = false,
        $currency = null
    ) {
        if ($convertAndRound) {
            return $this->priceCurrency->convertAndRound($value, null, $currency);
        }
        return $format
            ? $this->priceCurrency->convertAndFormat($value, $includeContainer)
            : $this->priceCurrency->convert($value);
    }
    
    /**
     * Format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $scope
     * @param \Magento\Framework\Model\AbstractModel|string|null $currency
     * @return float
     */
    public function format(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        return $this->priceCurrency->format(
            $amount,
            $includeContainer,
            $precision,
            $scope,
            $currency
        );
    }
    
    /**
     * Used mostly in product edit forms not for display purpose in storefront.
     * @param float $price
     * @param integer $precision
     * @return float
     */
    public function formatPrice($price = null, $precision = PriceCurrencyInterface::DEFAULT_PRECISION)
    {
        return ($price) ? number_format((float)$price, $precision, '.', '') : $price;
    }
}
