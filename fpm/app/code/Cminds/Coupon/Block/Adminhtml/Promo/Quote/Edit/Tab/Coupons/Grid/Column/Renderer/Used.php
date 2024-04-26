<?php
namespace Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer;

class Used extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        return (string)(($value == NULL) ? 0 : $value);
    }
}
