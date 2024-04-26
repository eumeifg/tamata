<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_VendorCommissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\VendorCommissions\Model\Commission\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Quote extends \Magedelight\Commissions\Model\Commission\Quote
{

    const VENDOR_CATEGORY_COMMISSION_TABLE = "md_vendor_category_commission";

    const COMMISSION_LEVEL_VENDOR_CATEGORY = 5;

    /**
     * @var array
     */
    protected $vendorCategoryCommissionRates = [];

    /**
     *
     * @param int $websiteId
     * @param int $categoryId
     * @return array
     */
    protected function _getVendorCategoryCommissionRateByCatId($websiteId, $categoryId, $vendorId)
    {
        if (!$categoryId) {
            return [];
        }
        if (!array_key_exists($vendorId.'_'.$categoryId, $this->vendorCategoryCommissionRates)) {
            $connection = $this->_getConnection();
            $select = $connection->select()
                ->from(self::VENDOR_CATEGORY_COMMISSION_TABLE, ['calculation_type','commission_value',
                    'marketplace_fee', 'marketplace_fee_type', 'cancellation_fee_commission_value',
                    'cancellation_fee_calculation_type'])
                ->where('status = ?', 1)
                ->where('vendor_id = ?', $vendorId)
                ->where('product_category = ?', $categoryId)
                ->where('website_id = ?', $websiteId);
            $this->vendorCategoryCommissionRates[$vendorId.'_'.$categoryId] = $connection->fetchRow($select);
        }
        return $this->vendorCategoryCommissionRates[$vendorId.'_'.$categoryId];
    }
    
    
    
    /**
     * calculate commission based on precedence level
     * @param int $precedence
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    public function aroundGetCommissionAmountByPrecedence(
        \Magedelight\Commissions\Model\Commission\Quote $subject,
        \Closure $proceed,
        $precedence,
        \Magento\Quote\Api\Data\CartItemInterface $item,
        $websiteId
    ) {

        if ($precedence == self::COMMISSION_LEVEL_VENDOR_CATEGORY) {
            return $this->_getCommissionBasedOnVendorCategoryRate($item, $subject, $websiteId);
        }
        return $proceed($precedence, $item, $websiteId);
    }
    
    /**
     * calculate commission based on precedence level
     * @param int $precedence
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    public function aroundGetMarketPlaceAmountByPrecedence(
        \Magedelight\Commissions\Model\Commission\Quote $subject,
        \Closure $proceed,
        $precedence,
        \Magento\Quote\Api\Data\CartItemInterface $item,
        $websiteId
    ) {

        if ($precedence == self::COMMISSION_LEVEL_VENDOR_CATEGORY) {
            return $this->_getMarketPlaceBasedOnVendorCategoryRate($item, $subject, $websiteId);
        }

        return $proceed($precedence, $item, $websiteId);
    }

    /**
     * calculate commission based on precedence level
     * @param int $precedence
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    public function aroundGetCancellationAmountByPrecedence(
        \Magedelight\Commissions\Model\Commission\Quote $subject,
        \Closure $proceed,
        $precedence,
        \Magento\Quote\Api\Data\CartItemInterface $item
    ) {
        if ($precedence == self::COMMISSION_LEVEL_VENDOR_CATEGORY) {
            return $this->_getCancellationBasedOnVendorCategoryRate($item, $subject, $this->_getWebsiteId($item));
        }

        return $proceed($precedence, $item);
    }
    

    /**
     * Commission Calculations based on category rate
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnVendorCategoryRate(
        \Magento\Quote\Api\Data\CartItemInterface $item,
        $subject,
        $websiteId)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $prod = $this->_getItemProduct($product->getId());
        $vendorId = $item->getVendorId();
        unset($product);
        $categoryIds = $prod->getCategoryIds();
        if (!empty($categoryIds)) {
            $commRates = $this->_getVendorCategoryCommissionRateByCatId($websiteId, $categoryIds[0], $vendorId);
            if (!empty($commRates)) {
                $total = $subject->getItemRowTotalWithAdjustedDiscount($item);
                return $subject->calculateRate(
                    $total,
                    $commRates['calculation_type'],
                    $commRates['commission_value'],
                    $item->getQty()
                );
            }
        }
    }

    /**
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    protected function _getMarketPlaceBasedOnVendorCategoryRate(
        \Magento\Quote\Api\Data\CartItemInterface $item,
        $subject,
        $websiteId
    )
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $vendorId = $item->getVendorId();
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $catmarketRates = $this->_getVendorCategoryCommissionRateByCatId($websiteId, $categoryIds[0], $vendorId);
            if (!empty($catmarketRates)) {
                $total = $subject->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                    : $item->getQtyOrdered();

                return $subject->calculateRate(
                    $total,
                    $catmarketRates['marketplace_fee_type'],
                    $catmarketRates['marketplace_fee'],
                    $qty
                );
            }
        }
    }

    /**
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    protected function _getCancellationBasedOnVendorCategoryRate(
        \Magento\Quote\Api\Data\CartItemInterface $item,
        $subject,
        $websiteId
    )
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $vendorId = $item->getVendorId();
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $catmarketRates = $this->_getVendorCategoryCommissionRateByCatId($websiteId, $categoryIds[0], $vendorId);
            if (!empty($catmarketRates)) {
                $total = $subject->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                    : $item->getQtyOrdered();

                return $subject->calculateRate(
                    $total,
                    $catmarketRates['cancellation_fee_calculation_type'],
                    $catmarketRates['cancellation_fee_commission_value'],
                    $qty
                );
            }
        }
    }
}
