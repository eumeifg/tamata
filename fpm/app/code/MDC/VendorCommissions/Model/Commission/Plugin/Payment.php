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
use Magento\Framework\Serialize\Serializer\Json;

class Payment extends \Magedelight\Commissions\Model\Commission\Payment
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
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|null
     */
    public function aroundGetCommissionAmountByPrecedence(
        \Magedelight\Commissions\Model\Commission\Payment $subject,
        \Closure $proceed,
        $precedence,
        \Magento\Sales\Model\Order\Item $item
    ) {
        
        if ($precedence == self::COMMISSION_LEVEL_VENDOR_CATEGORY) {
            $rate = $this->_getCommissionBasedOnVendorCategoryRate($item, $subject);
            return $rate;
        }

        return $proceed($precedence, $item);
    }
    
    /**
     * calculate commission based on precedence level
     * @param int $precedence
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|null
     */
    public function aroundGetMarketPlaceAmountByPrecedence(
        \Magedelight\Commissions\Model\Commission\Payment $subject,
        \Closure $proceed,
        $precedence,
        \Magento\Sales\Model\Order\Item $item
    ) {

       if ($precedence == self::COMMISSION_LEVEL_VENDOR_CATEGORY) {
            return $this->_getMarketPlaceBasedOnVendorCategoryRate($item, $subject);
       }

        return $proceed($precedence, $item);
    }

    /**
     * calculate commission based on precedence level
     * @param int $precedence
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|null
     */
    public function aroundCancellationAmountByPrecedence(
        \Magedelight\Commissions\Model\Commission\Payment $subject,
        \Closure $proceed,
        $precedence,
        \Magento\Sales\Model\Order\Item $item
    ) {
        if ($precedence == self::COMMISSION_LEVEL_VENDOR_CATEGORY) {
            return $this->_getCancellationBasedOnVendorCategoryRate($item, $subject);
        }

        return $proceed($precedence, $item);
    }
    
    /**
     * Commission Calculations based on category rate
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnVendorCategoryRate(\Magento\Sales\Model\Order\Item $item, $subject)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $categoryIds = $product->getCategoryIds();
        $vendorId = $item->getVendorId();
        if (!empty($categoryIds)) {
            $websiteId = $this->_getWebsiteId($item);
            $commRates = $this->_getVendorCategoryCommissionRateByCatId($websiteId, $categoryIds[0], $vendorId);
            if (!empty($commRates)) {
                $total = $subject->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                    : $item->getQtyOrdered();

                $subject->setFeesCalcDataHistory(
                    $item,
                    self::FEE_TYPE_COMMISSION,
                    [
                        'precedence' => self::COMMISSION_LEVEL_VENDOR_CATEGORY,
                        'calculationType' => $commRates['calculation_type'],
                        'commissionRate' => $commRates['commission_value']
                    ]
                );
                return $subject->calculateRate(
                    $total,
                    $commRates['calculation_type'],
                    $commRates['commission_value'],
                    $qty,
                    $item->getOrder()->getCurrencyCode()
                );
            }
        }
    }

    protected function _getMarketPlaceBasedOnVendorCategoryRate(\Magento\Sales\Model\Order\Item $item, $subject)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $categoryIds = $product->getCategoryIds();
        $vendorId = $item->getVendorId();
        if (!empty($categoryIds)) {
            $websiteId = $this->_getWebsiteId($item);
            $catmarketRates = $this->_getVendorCategoryCommissionRateByCatId($websiteId, $categoryIds[0], $vendorId);
            if (!empty($catmarketRates)) {
                $total = $subject->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                    : $item->getQtyOrdered();

                $subject->setFeesCalcDataHistory(
                    $item,
                    self::FEE_TYPE_MARKETPLACE_FEE,
                    [
                        'precedence' => self::COMMISSION_LEVEL_VENDOR_CATEGORY,
                        'calculationType' => $catmarketRates['calculation_type'],
                        'commissionRate' => $catmarketRates['commission_value']
                    ]
                );
                return $subject->calculateRate(
                    $total,
                    $catmarketRates['marketplace_fee_type'],
                    $catmarketRates['marketplace_fee'],
                    $qty,
                    $item->getOrder()->getCurrencyCode()
                );
            }
        }
    }

    protected function _getCancellationBasedOnVendorCategoryRate(\Magento\Sales\Model\Order\Item $item, $subject)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $item->getProduct();
        if (!$product) {
            return null;
        }
        $categoryIds = $product->getCategoryIds();
        $vendorId = $item->getVendorId();
        if (!empty($categoryIds)) {
            $websiteId = $this->_getWebsiteId($item);
            $catmarketRates = $this->_getVendorCategoryCommissionRateByCatId($websiteId, $categoryIds[0], $vendorId);
            if (!empty($catmarketRates)) {
                $total = $subject->getItemRowTotalWithAdjustedDiscount($item);
                $qty   = $item->getQtyInvoiced() > 0 ? ($item->getQtyInvoiced() - $item->getQtyRefunded())
                    : $item->getQtyOrdered();

                $subject->setFeesCalcDataHistory(
                    $item,
                    self::FEE_TYPE_CANCELLATION_FEE,
                    [
                        'precedence' => self::COMMISSION_LEVEL_VENDOR_CATEGORY,
                        'calculationType' => $catmarketRates['cancellation_fee_calculation_type'],
                        'commissionRate' => $catmarketRates['cancellation_fee_commission_value']
                    ]
                );
                return $subject->calculateRate(
                    $total,
                    $catmarketRates['cancellation_fee_calculation_type'],
                    $catmarketRates['cancellation_fee_commission_value'],
                    $qty,
                    $item->getOrder()->getCurrencyCode()
                );
            }
        }
    }
}
