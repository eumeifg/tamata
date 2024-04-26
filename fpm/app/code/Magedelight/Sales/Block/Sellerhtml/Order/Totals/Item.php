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
namespace Magedelight\Sales\Block\Sellerhtml\Order\Totals;

/**
 * Totals item block
 */
class Item extends \Magedelight\Sales\Block\Sellerhtml\Order\Totals
{
    /**
     * Determine display parameters before rendering HTML
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $this->setCanDisplayTotalPaid($this->getParentBlock()->getCanDisplayTotalPaid());
        $this->setCanDisplayTotalRefunded($this->getParentBlock()->getCanDisplayTotalRefunded());
        $this->setCanDisplayTotalDue($this->getParentBlock()->getCanDisplayTotalDue());

        return $this;
    }

    /**
     * Initialize totals object
     *
     * @return $this
     */
    public function initTotals()
    {
        $total = new \Magento\Framework\DataObject(
            [
                'code' => $this->getNameInLayout(),
                'block_name' => $this->getNameInLayout(),
                'area' => $this->getDisplayArea(),
                'strong' => $this->getStrong(),
            ]
        );
        if ($this->getBeforeCondition()) {
            $this->getParentBlock()->addTotalBefore($total, $this->getBeforeCondition());
        } else {
            $this->getParentBlock()->addTotal($total, $this->getAfterCondition());
        }
        return $this;
    }

    /**
     * Price HTML getter
     *
     * @param float $baseAmount
     * @param float $amount
     * @return string
     */
    public function displayPrices($baseAmount, $amount)
    {
        return $this->_frontendHelper->displayPrices($this->getOrder(), $baseAmount, $amount);
    }

    /**
     * Price attribute HTML getter
     *
     * @param string $code
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPriceAttribute($code, $strong = false, $separator = '<br/>')
    {
        return $this->_frontendHelper->displayPriceAttribute($this->getSource(), $code, $strong, $separator);
    }

    /**
     * Source order getter
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }
}
