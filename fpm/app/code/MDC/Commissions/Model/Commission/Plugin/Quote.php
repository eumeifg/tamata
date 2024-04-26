<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_Commissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\Commissions\Model\Commission\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Quote extends \Magedelight\Commissions\Model\Commission\Quote
{

    const VENDOR_GROUP_COMMISSION_TABLE = "md_vendor_group_commission";
    const COMMISSION_LEVEL_VENDOR_GROUP = 4;

    /**
     * @var array
     */
    protected $groupCommissionRates = [];
    
    /**
     *
     * @param int $groupId
     * @return array
     */
    protected function _getVendorGroupCommissionRateByGroupId($groupId, $subject, $websiteId)
    {
        if (!$groupId) {
            return [];
        }
        if (!array_key_exists($groupId, $this->groupCommissionRates)) {
            $connection = $this->_connection;
            $select = $connection->select()
                ->from(self::VENDOR_GROUP_COMMISSION_TABLE, [
                    'calculation_type',
                    'commission_value',
                    'marketplace_fee_type',
                    'marketplace_fee'
                ])
                ->where('status = ?', 1)
                ->where('vendor_group_id = ?', $groupId)
                ->where('website_id = ?', $websiteId);
            $this->groupCommissionRates[$groupId] = $connection->fetchRow($select);
        }
        return $this->groupCommissionRates[$groupId];
    }
    
    /**
     * Commission Calculations based on vendor group rate
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null Commission
     */
    protected function _getCommissionBasedOnVendorGroupRate(
        \Magento\Quote\Api\Data\CartItemInterface $item,
        $subject,
        $websiteId
    ) {
        $vendor = $this->getVendor($item->getVendorId());
        if ($vendor && $vendor->getVendorId()) {
            $commRates = $this->_getVendorGroupCommissionRateByGroupId($vendor->getVendorGroup(), $subject, $websiteId);
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
        if ($precedence == self::COMMISSION_LEVEL_VENDOR_GROUP) {
            return $this->_getCommissionBasedOnVendorGroupRate($item, $subject, $websiteId);
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
        if ($precedence == self::COMMISSION_LEVEL_VENDOR_GROUP) {
            return $this->_getMarketPlaceBasedOnVendorGroupRate($item, $subject, $websiteId);
        }
        return $proceed($precedence, $item, $websiteId);
    }
    
    /**
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null
     */
    protected function _getMarketPlaceBasedOnVendorGroupRate(
        \Magento\Quote\Api\Data\CartItemInterface $item,
        $subject,
        $websiteId
    ) {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $vendor = $this->getVendor($item->getVendorId());
        if ($vendor && $vendor->getVendorId()) {
            $commRates = $this->_getVendorGroupCommissionRateByGroupId($vendor->getVendorGroup(), $subject, $websiteId);
            $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
            if (!empty($commRates)) {
                $total = $subject->getItemRowTotalWithAdjustedDiscount($item);
                return $subject->calculateRate(
                    $total,
                    $commRates['marketplace_fee_type'],
                    $commRates['marketplace_fee'],
                    $item->getQty()
                );
            }
        }
    }
}
