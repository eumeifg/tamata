<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CAT\VIP\Model\Rule\Action\Discount;

class ByFixed extends \Magento\SalesRule\Model\Rule\Action\Discount\ByFixed
{
    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $viphelper = $object_manager->get('\CAT\VIP\Helper\Data');
        $quote = $item->getQuote();
        if($viphelper->isVipCustomer($quote->getCustomer()->getGroupId())){
            $quoteAmount = $this->priceCurrency->convert($rule->getVipDiscountAmount(), $item->getQuote()->getStore());
            $discountData->setAmount($qty * $quoteAmount);
            $discountData->setBaseAmount($qty * $rule->getVipDiscountAmount());
        }else{
            $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $item->getQuote()->getStore());
            $discountData->setAmount($qty * $quoteAmount);
            $discountData->setBaseAmount($qty * $rule->getDiscountAmount());
        }

        return $discountData;
    }

    /**
     * @param float $qty
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return float
     */
    public function fixQuantity($qty, $rule)
    {
        $step = $rule->getDiscountStep();
        if ($step) {
            $qty = floor($qty / $step) * $step;
        }

        return $qty;
    }
}
