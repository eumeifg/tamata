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
use Magento\Framework\Serialize\Serializer\Json;

class Payment extends \Magedelight\Commissions\Model\Commission\Payment
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
    protected function _getVendorGroupCommissionRateByGroupId($websiteId, $groupId, $subject)
    {
        if (!array_key_exists($groupId, $this->groupCommissionRates)) {
            $connection = $this->_getConnection();
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
        if ($precedence == self::COMMISSION_LEVEL_VENDOR_GROUP) {
            $rate = $this->_getCommissionBasedOnVendorGroupRate($item, $subject);
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
        if ($precedence == self::COMMISSION_LEVEL_VENDOR_GROUP) {
            return $this->_getMarketPlaceBasedOnVendorGroupRate($item, $subject);
        }
        return $proceed($precedence, $item);
    }
    
    /**
     * Commission Calculations based on vendor group rate
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|null Commission
     */
    protected function _getMarketPlaceBasedOnVendorGroupRate(\Magento\Sales\Model\Order\Item $item, $subject)
    {
        $this->setVendorId($item->getVendorId());
        $vendor = $this->getVendor();
        if ($vendor && $vendor->getVendorId()) {
            $websiteId = '';
            $websiteId = $this->_getWebsiteId($item);
            $commRates = $this->_getVendorGroupCommissionRateByGroupId($websiteId, $vendor->getVendorGroup(), $subject);
            if (!empty($commRates)) {
                $total = $subject->getItemRowTotalWithAdjustedDiscount($item);
                $qty = $item->getQtyInvoiced() > 0 ?
                    ($item->getQtyInvoiced()-$item->getQtyRefunded()):$item->getQtyOrdered();
                $subject->setFeesCalcDataHistory(
                    $item,
                    self::FEE_TYPE_COMMISSION,
                    [
                        'precedence' => self::COMMISSION_LEVEL_VENDOR_GROUP,
                        'calculationType' => $commRates['calculation_type'],
                        'commissionRate' => $commRates['commission_value']
                    ]
                );
                return $subject->calculateRate(
                    $total,
                    $commRates['marketplace_fee_type'],
                    $commRates['marketplace_fee'],
                    $qty,
                    $item->getOrder()->getCurrencyCode()
                );
            }
        }
    }
    
     /**
      * Commission Calculations based on vendor group rate
      * @param \Magento\Quote\Api\Data\CartItemInterface $item
      * @return float|null Commission
      */
    protected function _getCommissionBasedOnVendorGroupRate(\Magento\Sales\Model\Order\Item $item, $subject)
    {
        $this->setVendorId($item->getVendorId());
        $vendor = $this->getVendor();
        
        if ($vendor && $vendor->getVendorId()) {
            $websiteId = '';
            $websiteId = $this->_getWebsiteId($item);
            $commRates = $this->_getVendorGroupCommissionRateByGroupId($websiteId, $vendor->getVendorGroup(), $subject);
            if (!empty($commRates)) {
                $total = $subject->getItemRowTotalWithAdjustedDiscount($item);
                $qty = $item->getQtyInvoiced() > 0 ?
                    ($item->getQtyInvoiced()-$item->getQtyRefunded()):$item->getQtyOrdered();
                $subject->setFeesCalcDataHistory(
                    $item,
                    self::FEE_TYPE_COMMISSION,
                    [
                        'precedence' => self::COMMISSION_LEVEL_VENDOR_GROUP,
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
}
