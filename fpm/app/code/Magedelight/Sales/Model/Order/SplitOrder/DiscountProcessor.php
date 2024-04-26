<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\Order\SplitOrder;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\SalesRule\Model\RuleFactory;

/**
 * Process discount for sub order data.
 * @author Rocket Bazaar Core Team
 * Created at 31 Dec , 2019 11:30:00 AM
 */
class DiscountProcessor extends \Magento\Framework\DataObject
{
    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * DiscountProcessor constructor.
     * @param Json $serializer
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Json $serializer,
        RuleFactory $ruleFactory
    ) {
        $this->serializer = $serializer;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param $order
     * @param $subOrderData
     * @return mixed
     */
    public function process($order, $subOrderData)
    {
        $vendorShippingData = ($order->getVendorShippingData()) ?
            $this->serializer->unserialize($order->getVendorShippingData()) : [];

        $subOrderData->setDiscountAmount($subOrderData->getDiscountAmount() * -1);
        $subOrderData->setBaseDiscountAmount($subOrderData->getBaseDiscountAmount() * -1);

        if (empty($vendorShippingData)) {
            $subOrderData->setDiscountAmount($subOrderData->getDiscountAmount() - $subOrderData->getDiscountAmount());
            $subOrderData->setBaseDiscountAmount(
                $subOrderData->getBaseDiscountAmount() - $subOrderData->getBaseDiscountAmount()
            );
        }

        $subOrderData->setGrandTotal($subOrderData->getGrandTotal() + $subOrderData->getDiscountAmount());
        $subOrderData->setBaseGrandTotal(
            $subOrderData->getBaseGrandTotal() + abs($subOrderData->getBaseDiscountAmount())
        );

        return $subOrderData;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $vendorId
     * @param bool $baseDiscount
     * @return float|null
     */
    public function calculateVendorDiscountAmount(
        \Magento\Sales\Model\Order\Item $item,
        $vendorId,
        $baseDiscount = false
    ) {
        if (!$item || !$item->getAppliedRuleIds()) {
            return 0;
        }
        // lookup rule ids
        $ruleIds = explode(',', $item->getAppliedRuleIds());
        $ruleIds = array_unique($ruleIds);
        $canDiscount = false;
        foreach ($ruleIds as $ruleId) {
            if (!$ruleId) {
                continue;
            }

            $rule = $this->ruleFactory->create()->load($ruleId);
            $vendorIds = explode(',', $rule->getData('vendor_id'));
            if (empty($vendorIds)) {
                continue;
            }
            if (in_array($vendorId, $vendorIds)) {
                $canDiscount = true;
            }
        }
        return ($canDiscount) ? ($baseDiscount) ? $item->getBaseDiscountAmount() : $item->getDiscountAmount() : 0;
    }

    /**
     * @param \Magedelight\Sales\Model\Core\Order\Item $item
     * @param $vendorId
     * @return string|null
     */
    public function getDiscountDescription(\Magedelight\Sales\Model\Core\Order\Item $item, $vendorId)
    {
        if (!$item || !$item->getAppliedRuleIds()) {
            return null;
        }
        // lookup rule ids
        $ruleIds = explode(',', $item->getAppliedRuleIds());
        $ruleIds = array_unique($ruleIds);
        $discountDescription = [];
        foreach ($ruleIds as $ruleId) {
            if (!$ruleId) {
                continue;
            }

            $rule = $this->ruleFactory->create()->load($ruleId);
            $vendorIds = explode(',', $rule->getVendorId());
            if (empty($vendorIds)) {
                continue;
            }
            if (in_array($vendorId, $vendorIds)) {
                $discountDescription[] = $rule->getStoreLabel($item->getStoreId());
            }
        }

        return (!empty($discountDescription)) ? implode(",", $discountDescription) : null;
    }
}
